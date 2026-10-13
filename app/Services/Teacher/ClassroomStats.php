<?php

namespace App\Services\Teacher;

use App\Models\ClassRoom;
use App\Services\Attendance\ClassroomAttendance;
use App\Services\Attendance\StudentSessions;
use Carbon\Carbon;

/**
 * Attendance metrics of a classroom over the teacher's subjects, per student and overall.
 * Same cell rule (ClassroomAttendance) and definitions (StudentSessions::stats, PLAN.md §7.4)
 * as the student portal, so a student's rate is the same on both sides for those subjects.
 */
class ClassroomStats
{
    /** Follow-up rule (PLAN.md §8, screen 6): rate below 80% or 3+ absences. */
    const FOLLOW_UP_RATE = 80;
    const FOLLOW_UP_ABSENCES = 3;

    /**
     * @return array [students => [[student, stats, absences, late]], overall => stats]
     */
    public static function compute(ClassRoom $classroom, array $curriculumIds, Carbon $now)
    {
        $students = $classroom->students;
        $attendance = new ClassroomAttendance($classroom, $students->pluck('id')->all());
        $today = $now->toDateString();
        $nowTime = $now->format('H:i');

        // Sessions due so far (today's only once started), as [curriculum_id, date, hour].
        $due = [];
        foreach ($attendance->slots() as $slot) {
            if (! in_array($slot['curriculum_id'], $curriculumIds, true)) {
                continue;
            }
            $start = $slot['start'] ? $slot['start']->format('H:i') : null;
            foreach ($slot['dates'] as $date) {
                if ($date < $today || ($date === $today && $start !== null && $start <= $nowTime)) {
                    $due[] = [$slot['curriculum_id'], $date, $slot['start'] ? $slot['start']->format('H') : null];
                }
            }
        }

        $all = [];
        $rows = [];
        foreach ($students as $student) {
            $sessions = [];
            foreach ($due as list($curriculumId, $date, $hour)) {
                list($state) = $attendance->stateFor($curriculumId, $date, $hour, $student->id);
                $sessions[] = ['state' => $state ? StudentSessions::STATE_NAMES[$state] : 'unrecorded'];
            }
            $stats = StudentSessions::stats($sessions);
            $all = array_merge($all, $sessions);
            $rows[] = [
                'student' => $student,
                'stats' => $stats,
                'absences' => $stats['counts']['absent'] + $stats['counts']['absent_excused'],
                'late' => $stats['counts']['late'] + $stats['counts']['late_excused'],
            ];
        }

        return ['students' => $rows, 'overall' => StudentSessions::stats($all)];
    }

    public static function needsFollowUp(array $row)
    {
        return ($row['stats']['rate'] !== null && $row['stats']['rate'] < self::FOLLOW_UP_RATE)
            || $row['absences'] >= self::FOLLOW_UP_ABSENCES;
    }
}
