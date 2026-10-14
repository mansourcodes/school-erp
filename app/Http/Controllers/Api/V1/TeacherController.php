<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Curriculum;
use App\Models\PushDelivery;
use App\Services\Attendance\AttendanceRecorder;
use App\Services\Attendance\ClassroomAttendance;
use App\Services\Attendance\StudentSessions;
use App\Services\Portal\StudentPhotos;
use App\Services\Portal\TeacherPhotos;
use App\Services\Teacher\ClassroomStats;
use RuntimeException;
use App\Services\Teacher\TeacherScope;
use App\Services\Teacher\TeacherSessions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Teacher API (PLAN.md §8). Every endpoint is limited to the teacher's classrooms of the
 * current course and to their subjects there (TeacherScope); anything else is 404.
 */
class TeacherController extends Controller
{
    protected function scope(Request $request)
    {
        return new TeacherScope($request->user());
    }

    protected function now()
    {
        return Carbon::now(config('portal.timezone'));
    }

    /** GET /teacher/today: today's sessions, past ones never recorded, the next ones, and tiles. */
    public function today(Request $request)
    {
        $scope = $this->scope($request);
        $now = $this->now();
        $today = $now->toDateString();

        $sessions = collect();
        $students = collect();
        $overall = [];
        foreach ($scope->classrooms() as $classroom) {
            $mine = $scope->curriculumIds($classroom);
            $sessions = $sessions->merge(array_map(function ($s) use ($classroom) {
                return $s + ['classroom' => $this->classroomLabel($classroom)];
            }, TeacherSessions::forClassroom($classroom, $mine, $now)));
            $students = $students->merge($classroom->students->pluck('id'));
            $overall[] = ClassroomStats::compute($classroom, $mine, $now)['overall'];
        }
        $sessions = $sessions->sortBy(function ($s) {
            return $s['date'] . ' ' . $s['start'];
        })->values();

        $pending = $sessions->where('status', 'pending')->values();
        $first = $scope->classrooms()->first();

        return response()->json(['data' => [
            'date' => $today,
            'course' => $first ? $first->course->long_name : null,
            'today' => $sessions->where('date', $today)->values(),
            'pending' => $pending,
            'upcoming' => $sessions->filter(function ($s) use ($today) {
                return $s['date'] > $today;
            })->take(3)->values(),
            'tiles' => [
                'classrooms' => $scope->classrooms()->count(),
                'students' => $students->unique()->count(),
                'rate' => $this->combinedRate($overall),
                'pending' => $pending->count(),
            ],
        ]]);
    }

    /** GET /teacher/classrooms */
    public function classrooms(Request $request)
    {
        $scope = $this->scope($request);
        $now = $this->now();

        return response()->json(['data' => $scope->classrooms()->map(function (ClassRoom $classroom) use ($scope, $now) {
            $mine = $scope->curriculumIds($classroom);
            $sessions = collect(TeacherSessions::forClassroom($classroom, $mine, $now));

            return $this->classroomSummary($classroom, $mine) + [
                'students_count' => $classroom->students->count(),
                'rate' => ClassroomStats::compute($classroom, $mine, $now)['overall']['rate'],
                'pending_count' => $sessions->where('status', 'pending')->count(),
            ];
        })->values()]);
    }

    /** GET /teacher/classrooms/{id}: classroom, its course and the students with their summary. */
    public function classroom(Request $request, $id)
    {
        $scope = $this->scope($request);
        $classroom = $scope->find($id);
        abort_unless($classroom, 404);

        $mine = $scope->curriculumIds($classroom);
        $stats = ClassroomStats::compute($classroom, $mine, $this->now());

        // array_merge, not `+`: the summary's string `course` must be replaced by the detail object.
        return response()->json(['data' => array_merge($this->classroomSummary($classroom, $mine), [
            'course' => [
                'id' => $classroom->course->id,
                'long_name' => $classroom->course->long_name,
                'start_date' => optional($classroom->course->start_date)->toDateString(),
                'end_date' => optional($classroom->course->end_date)->toDateString(),
            ],
            'rate' => $stats['overall']['rate'],
            'students' => array_map([$this, 'studentRow'], $stats['students']),
            'readonly' => false,
        ])]);
    }

    /** GET /teacher/classrooms/{id}/sessions */
    public function classroomSessions(Request $request, $id)
    {
        $scope = $this->scope($request);
        $classroom = $scope->find($id);
        abort_unless($classroom, 404);

        return response()->json(['data' => TeacherSessions::forClassroom($classroom, $scope->curriculumIds($classroom), $this->now())]);
    }

    /** GET /teacher/sessions/{code}: roster with this classroom's recorded states. */
    public function session(Request $request, $code)
    {
        list($classroom, $session, $sessions) = $this->resolveSession($request, $code);
        $recorder = app(AttendanceRecorder::class);
        $record = $recorder->find($classroom, $session['curriculum_id'], $session['date'], $session['start']);

        $states = [];
        if ($record) {
            foreach (ClassroomAttendance::STATE_RELATIONS as $state => $relation) {
                foreach ($record->{$relation}()->pluck('students.id') as $studentId) {
                    $states[$studentId] = StudentSessions::STATE_NAMES[$state];
                }
            }
        }

        $index = array_search($code, array_column($sessions, 'code'), true);

        return response()->json(['data' => [
            'session' => $session + ['classroom' => $this->classroomLabel($classroom)],
            'students' => $classroom->students->map(function ($student) use ($states) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'student_id' => $student->student_id,
                    'photo_url' => StudentPhotos::url($student),
                    'state' => $states[$student->id] ?? null,
                ];
            })->values(),
            'previous_code' => $index > 0 ? $sessions[$index - 1]['code'] : null,
            'next_code' => $index < count($sessions) - 1 ? $sessions[$index + 1]['code'] : null,
        ]]);
    }

    /**
     * PUT /teacher/sessions/{code}: { states: { student_id: state } } for every student of the
     * classroom. Idempotent (offline retries are safe). 403 for a session on a later day (Q9).
     */
    public function saveSession(Request $request, $code)
    {
        list($classroom, $session) = $this->resolveSession($request, $code);
        abort_unless($session['editable'], 403);

        $studentIds = $classroom->students->pluck('id')->map(function ($id) {
            return (string) $id;
        })->all();
        $apiStates = array_values(StudentSessions::STATE_NAMES);

        $data = $request->validate([
            'states' => ['required', 'array', 'size:' . count($studentIds)],
            'states.*' => ['required', Rule::in($apiStates)],
        ]);
        $given = array_map('strval', array_keys($data['states']));
        sort($given);
        sort($studentIds);
        if ($given !== $studentIds) {
            // Every student of the classroom, and only them (the roster may have changed meanwhile).
            throw ValidationException::withMessages(['states' => [__('portal.attendance.students_mismatch')]]);
        }

        $stored = array_flip(StudentSessions::STATE_NAMES);
        $states = [];
        foreach ($data['states'] as $studentId => $state) {
            $states[(int) $studentId] = $stored[$state];
        }

        list($attend, $created) = app(AttendanceRecorder::class)->save(
            $classroom,
            $session['curriculum_id'],
            $session['date'],
            $session['start'],
            $states,
            $this->now(),
            $request->user()->id
        );

        return response()->json(['data' => [
            'updated_at' => $attend->updated_at->toIso8601String(),
            'created' => $created,
            // Pushes the app now sends one by one (push outbox); only a first save has any.
            'push_pending' => $created ? PushDelivery::where('user_id', $request->user()->id)->where('attend_id', $attend->id)->where('status', PushDelivery::PENDING)->count() : 0,
        ]]);
    }

    /** GET /teacher/stats?classroom_id=: metrics, per-student rows, follow-up list, rate per classroom. */
    public function stats(Request $request)
    {
        $request->validate(['classroom_id' => ['nullable', 'integer']]);
        $scope = $this->scope($request);
        $now = $this->now();

        $classrooms = $request->filled('classroom_id')
            ? collect([$scope->find($request->input('classroom_id'))])->filter()
            : $scope->classrooms();
        abort_if($request->filled('classroom_id') && $classrooms->isEmpty(), 404);

        $rows = [];
        $overall = [];
        $perClassroom = [];
        foreach ($classrooms as $classroom) {
            $result = ClassroomStats::compute($classroom, $scope->curriculumIds($classroom), $now);
            foreach ($result['students'] as $row) {
                $rows[] = $this->studentRow($row) + ['classroom_id' => $classroom->id, 'classroom' => $this->classroomLabel($classroom)];
            }
            $overall[] = $result['overall'];
            $perClassroom[] = ['id' => $classroom->id, 'label' => $this->classroomLabel($classroom), 'rate' => $result['overall']['rate']];
        }

        $followUp = array_values(array_filter($rows, function ($row) {
            return ($row['rate'] !== null && $row['rate'] < ClassroomStats::FOLLOW_UP_RATE)
                || $row['absences'] >= ClassroomStats::FOLLOW_UP_ABSENCES;
        }));
        usort($followUp, function ($a, $b) {
            return [$a['rate'] ?? 101, -$a['absences']] <=> [$b['rate'] ?? 101, -$b['absences']];
        });

        return response()->json(['data' => $this->mergeStats($overall) + [
            'students' => $rows,
            'follow_up' => $followUp,
            'classrooms' => $perClassroom,
        ]]);
    }

    /**
     * POST /teacher/classrooms/{id}/students/{sid}/photo: multipart `photo`. Applies at once (Q11),
     * only for a student of one of my current classrooms.
     */
    public function uploadPhoto(Request $request, $id, $sid, StudentPhotos $photos)
    {
        $student = $this->classroomStudent($request, $id, $sid);
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048', 'dimensions:min_width=300,min_height=300'],
        ]);

        try {
            $photos->store($student, $request->file('photo'));
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['photo' => [__('portal.photo.unreadable')]]);
        }

        return response()->json(['data' => ['photo_url' => StudentPhotos::url($student)]]);
    }

    /** DELETE /teacher/classrooms/{id}/students/{sid}/photo */
    public function deletePhoto(Request $request, $id, $sid, StudentPhotos $photos)
    {
        $photos->delete($this->classroomStudent($request, $id, $sid));

        return response()->noContent();
    }

    /**
     * POST /teacher/photo: multipart `photo`, my own photo; my students see it next to my name.
     */
    public function uploadOwnPhoto(Request $request, TeacherPhotos $photos)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048', 'dimensions:min_width=300,min_height=300'],
        ]);

        try {
            $photos->store($request->user(), $request->file('photo'));
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['photo' => [__('portal.photo.unreadable')]]);
        }

        return response()->json(['data' => ['photo_url' => TeacherPhotos::url($request->user())]]);
    }

    /** DELETE /teacher/photo */
    public function deleteOwnPhoto(Request $request, TeacherPhotos $photos)
    {
        $photos->delete($request->user());

        return response()->noContent();
    }

    protected function classroomStudent(Request $request, $classroomId, $studentId)
    {
        $classroom = $this->scope($request)->find($classroomId);
        abort_unless($classroom, 404);
        $student = $classroom->students->firstWhere('id', (int) $studentId);
        abort_unless($student, 404);

        return $student;
    }

    /** @return array [ClassRoom, session, all sessions of the classroom] or 404 */
    protected function resolveSession(Request $request, $code)
    {
        $parsed = TeacherSessions::parse($code);
        abort_unless($parsed, 404);

        $scope = $this->scope($request);
        $classroom = $scope->find($parsed[2]);
        abort_unless($classroom && in_array($parsed[3], $scope->curriculumIds($classroom), true), 404);

        $sessions = TeacherSessions::forClassroom($classroom, $scope->curriculumIds($classroom), $this->now());
        $session = collect($sessions)->firstWhere('code', $code);
        abort_unless($session, 404);

        return [$classroom, $session, $sessions];
    }

    protected function classroomLabel(ClassRoom $classroom)
    {
        return ['id' => $classroom->id, 'number' => $classroom->class_room_number, 'name' => $classroom->class_room_name];
    }

    protected function classroomSummary(ClassRoom $classroom, array $mine)
    {
        $names = Curriculum::withTrashed()->whereIn('id', $mine)->pluck('curriculum_name', 'id');
        $slots = [];
        foreach ($classroom->attend_table ?: [] as $slot) {
            if (isset($slot['curriculum_id'], $slot['day']) && in_array((int) $slot['curriculum_id'], $mine, true)) {
                $start = empty($slot['start_time']) ? null : Carbon::parse($slot['start_time'])->format('H:i');
                $slots[] = [
                    'curriculum_id' => (int) $slot['curriculum_id'],
                    'day' => ((int) $slot['day']) % 7,
                    'start' => $start,
                    'end' => empty($slot['end_time']) ? ($start ? Carbon::createFromFormat('H:i', $start)->addHour()->format('H:i') : null)
                        : Carbon::parse($slot['end_time'])->format('H:i'),
                ];
            }
        }

        return [
            'id' => $classroom->id,
            'number' => $classroom->class_room_number,
            'name' => $classroom->class_room_name,
            'course' => $classroom->course->long_name,
            'subjects' => collect($mine)->map(function ($id) use ($names) {
                return ['id' => $id, 'name' => $names->get($id, '')];
            })->values(),
            'slots' => $slots,
        ];
    }

    public function studentRow(array $row)
    {
        return [
            'id' => $row['student']->id,
            'name' => $row['student']->name,
            'student_id' => $row['student']->student_id,
            'photo_url' => StudentPhotos::url($row['student']),
            'rate' => $row['stats']['rate'],
            'recorded' => $row['stats']['recorded'],
            'absences' => $row['absences'],
            'late' => $row['late'],
        ];
    }

    /** Sum the counts of several classrooms and recompute the rates (PLAN.md §7.4). */
    protected function mergeStats(array $list)
    {
        $counts = array_fill_keys(array_merge(array_values(StudentSessions::STATE_NAMES), ['unrecorded']), 0);
        foreach ($list as $stats) {
            foreach ($stats['counts'] as $state => $n) {
                $counts[$state] += $n;
            }
        }

        return StudentSessions::fromCounts($counts);
    }

    protected function combinedRate(array $list)
    {
        return $this->mergeStats($list)['rate'];
    }
}
