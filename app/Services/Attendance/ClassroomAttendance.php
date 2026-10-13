<?php

namespace App\Services\Attendance;

use App\Helpers\HtmlHelper;
use App\Models\Attends;
use App\Models\ClassRoom;
use Carbon\Carbon;

/**
 * Recorded attendance of one classroom, and the rule that resolves one cell
 * (curriculum, date, timetable slot, student) to a state.
 *
 * Shared by the admin `student_attend_list` report and the portal API
 * (PLAN.md §5.5), so both always show the same states.
 */
class ClassroomAttendance
{
    /** Stored state => Attends relation (one pivot table per state). */
    const STATE_RELATIONS = [
        'attend' => 'attendStudents',
        'absent' => 'absentStudents',
        'absent_w_excuse' => 'absentWithExcuseStudents',
        'late' => 'lateStudents',
        'late_w_excuse' => 'lateWithExcuseStudents',
    ];

    protected $classroom;

    /** [curriculum_id][date][start hour | '*' | 'other'][student_id] => state */
    protected $attendStates = [];

    /** [date][student_id] => state, from other classrooms of the same course */
    protected $courseStates = [];

    /**
     * @param  int[]|null  $studentIds  only load these students' records (default: the classroom's students)
     */
    public function __construct(ClassRoom $classroom, array $studentIds = null)
    {
        $this->classroom = $classroom;
        $this->load($studentIds === null ? $classroom->students->pluck('id')->all() : $studentIds);
    }

    /**
     * Recorded attends, bucketed for lookup.
     * - Matched by hour only: old records saved start_time as 'H:m' (m = month), and ones the
     *   repair migration could not match to a timetable slot keep wrong minutes.
     * - '*' is the fallback for slots without a start_time in the timetable (the form then
     *   records the time it was opened, so the hour can't match).
     * - 'other' holds records from other classrooms of the same course, for students moved into
     *   this classroom; used only when this classroom has no record for the student.
     * - courseStates[date][student] is the last fallback: the student's record in any classroom
     *   of the course on that date, whatever the curriculum.
     * Students not in $studentIds (e.g. removed from the classroom) are ignored.
     */
    protected function load(array $studentIds)
    {
        $onlyThese = function ($query) use ($studentIds) {
            $query->whereIn('students.id', $studentIds);
        };

        $attends = Attends::where('class_room_id', $this->classroom->id)
            ->orWhere('course_id', $this->classroom->course_id)
            ->with(array_fill_keys(array_values(self::STATE_RELATIONS), $onlyThese))
            ->get();

        foreach ($attends as $attend) {
            $date = $attend->date->format('Y-m-d');
            $hour = Carbon::parse($attend->start_time)->format('H');
            $isOwn = $attend->class_room_id == $this->classroom->id;
            $buckets = $isOwn ? [$hour, '*'] : ['other'];

            foreach (self::STATE_RELATIONS as $state => $relation) {
                foreach ($attend->{$relation} as $student) {
                    foreach ($buckets as $bucket) {
                        $this->attendStates[(int) $attend->curriculum_id][$date][$bucket][$student->id] = $state;
                    }
                    if (! $isOwn) {
                        $this->courseStates[$date][$student->id] = $state;
                    }
                }
            }
        }
    }

    /**
     * The state of one cell, and whether it came from another classroom of the course.
     *
     * @param  string|null  $slotHour  the timetable slot's start hour ('16'), null when it has no time
     * @return array [state|null, bool fromOtherClassroom]
     */
    public function stateFor($curriculumId, $date, $slotHour, $studentId)
    {
        $states = $this->attendStates[(int) $curriculumId][$date] ?? [];

        $state = ($slotHour !== null ? ($states[$slotHour][$studentId] ?? null) : null)
            ?? $states['*'][$studentId]
            ?? null;
        if ($state) {
            return [$state, false];
        }

        $state = $states['other'][$studentId] ?? $this->courseStates[$date][$studentId] ?? null;

        return [$state, (bool) $state];
    }

    /**
     * Expected sessions from the timetable (`attend_table`): one entry per slot with its dates.
     * Dates come from HtmlHelper::getDaysInRange, exactly as in the admin report.
     *
     * @return array[] [curriculum_id, day (ISO 1–7), start (Carbon|null), end_time (raw|null), dates (Y-m-d[])]
     */
    public function slots()
    {
        $course = $this->classroom->course;
        $slots = [];

        foreach ($this->classroom->attend_table ?: [] as $slot) {
            if (! isset($slot['curriculum_id'], $slot['day'])) {
                continue;
            }
            $slots[] = [
                'curriculum_id' => (int) $slot['curriculum_id'],
                'day' => (int) $slot['day'],
                'start' => empty($slot['start_time']) ? null : Carbon::parse($slot['start_time']),
                'end_time' => empty($slot['end_time']) ? null : $slot['end_time'],
                'dates' => $course && $course->start_date && $course->end_date
                    ? HtmlHelper::getDaysInRange($slot['day'], $course->start_date, $course->end_date)
                    : [],
            ];
        }

        return $slots;
    }
}
