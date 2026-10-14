<?php

namespace App\Http\Resources\V1;

use App\Models\Curriculum;
use App\Models\StudentMarks;
use App\Models\User;
use App\Services\Portal\TeacherPhotos;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

/**
 * One enrollment as the student sees it (PLAN.md §7.3 CourseDetail).
 * Wraps a ClassRoom (with course.academicPath loaded); payments and marks
 * belong to the signed-in student and are passed in by the controller.
 */
class CourseDetailResource extends JsonResource
{
    protected $payments;

    protected $studentMarks;

    protected $status;

    public function __construct($classroom, Collection $payments, ?StudentMarks $studentMarks, $status)
    {
        parent::__construct($classroom);
        $this->payments = $payments;
        $this->studentMarks = $studentMarks;
        $this->status = $status;
    }

    public function toArray($request)
    {
        $course = $this->course;
        $marks = $this->marks();
        $finalGrade = $this->studentMarks ? $this->studentMarks->final_grade : null;
        $published = $finalGrade !== null && $finalGrade !== '';

        return [
            'id' => $course->id,
            'classroom_id' => $this->id,
            'status' => $this->status,
            'long_name' => $course->long_name,
            'academic_path' => optional($course->academicPath)->academic_path_name,
            'year' => $course->course_year,
            'hijri_year' => $course->hijri_year,
            'semester' => $course->semester,
            'start_date' => optional($course->start_date)->toDateString(),
            'end_date' => optional($course->end_date)->toDateString(),
            'duration' => $course->duration,
            'class_room_number' => $this->class_room_number,
            'class_room_name' => $this->class_room_name,
            'curricula' => $this->curricula(),
            'payments' => $this->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'date' => $payment->created_at->toDateString(),
                    'amount' => (float) $payment->amount,
                    'type' => $this->paymentLabel('payment_types', $payment->type),
                    'source' => $this->paymentLabel('payment_sources', $payment->source),
                ];
            })->values(),
            // Certificate available once the final grade is set (Q3).
            'marks_published' => $published,
            'marks' => $marks,
            'total_mark' => $published ? $this->studentMarks->total_mark : null,
            'final_grade' => $published ? $finalGrade : null,
        ];
    }

    /**
     * Subjects with teacher and weekly slots, from the `teachers` and `attend_table` JSON columns.
     * Days go from ISO (1 = Mon … 7 = Sun, as stored) to 0 = Sun … 6 = Sat (API contract).
     */
    protected function curricula()
    {
        $teachers = collect($this->teachers ?: [])->filter(function ($t) {
            return isset($t['curriculum_id']);
        });
        $slots = collect($this->attend_table ?: [])->filter(function ($s) {
            return isset($s['curriculum_id'], $s['day']);
        });

        $ids = $teachers->pluck('curriculum_id')->merge($slots->pluck('curriculum_id'))
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values();
        $names = Curriculum::withTrashed()->whereIn('id', $ids)->pluck('curriculum_name', 'id');
        // Photos of the teacher users linked in the row (`user_id` is optional in the admin form).
        $users = User::whereIn('id', $teachers->pluck('user_id')->filter())->get(['id', 'photo_path'])->keyBy('id');

        return $ids->map(function ($id) use ($teachers, $slots, $names, $users) {
            $teacher = $teachers->first(function ($t) use ($id) {
                return (int) $t['curriculum_id'] === $id;
            });
            $user = empty($teacher['user_id']) ? null : $users->get((int) $teacher['user_id']);

            return [
                'id' => $id,
                'name' => $names->get($id, ''),
                'teacher' => $teacher['teacher_name'] ?? null,
                'teacher_photo_url' => $user ? TeacherPhotos::url($user) : null,
                'slots' => $slots
                    ->filter(function ($s) use ($id) {
                        return (int) $s['curriculum_id'] === $id;
                    })
                    ->map(function ($s) {
                        $start = $this->time($s['start_time'] ?? null);
                        $end = $this->time($s['end_time'] ?? null);
                        if ($end === null && $start !== null) {
                            // Same default as the admin timetable: one hour.
                            $end = Carbon::createFromFormat('H:i', $start)->addHour()->format('H:i');
                        }

                        return ['day' => ((int) $s['day']) % 7, 'start' => $start, 'end' => $end];
                    })
                    ->sortBy(function ($s) {
                        return sprintf('%d-%s', $s['day'], $s['start']);
                    })
                    ->values(),
            ];
        })->values();
    }

    /**
     * `brief_marks` rows, the same ones the admin certificate table prints.
     * `mark` is null while it is not recorded yet.
     */
    protected function marks()
    {
        $record = $this->studentMarks;
        if (! $record || empty($record->getRawOriginal('marks'))) {
            return [];
        }

        try {
            $brief = $record->brief_marks;
        } catch (Throwable $e) {
            // Malformed legacy marks JSON must not break the whole screen.
            report($e);

            return [];
        }

        return collect($brief)->values()->map(function ($mark, $index) {
            $label = (string) $mark->label;

            return [
                'key' => 'm' . $index,
                'label' => isArabic($label) ? $label : __('studentmark.' . $label),
                'max' => is_numeric($mark->max) ? (float) $mark->max : null,
                'mark' => is_numeric($mark->mark) ? (float) $mark->mark : null,
            ];
        });
    }

    /** Stored times look like "16:00" or a full datetime; empty means not set. */
    protected function time($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (Throwable $e) {
            return null;
        }
    }

    /** Payment type/source keys (FULL, cash, BENEFIT_PAY…) → Arabic labels in portal.php. */
    protected function paymentLabel($group, $value)
    {
        $key = Str::upper(Str::snake(Str::lower((string) $value)));
        $translationKey = 'portal.' . $group . '.' . $key;
        $label = __($translationKey);

        return $label === $translationKey ? (string) $value : $label;
    }
}
