<?php

namespace App\Services\Attendance;

use App\Models\ClassRoom;
use App\Models\Curriculum;
use Carbon\Carbon;

/**
 * One student's sessions in one classroom: the timetable's expected sessions up to now,
 * each resolved with ClassroomAttendance (the admin report's cell rule). PLAN.md §5.5, §7.3.
 */
class StudentSessions
{
    /** Stored state → API state (PLAN.md §2 status encoding). */
    const STATE_NAMES = [
        'attend' => 'present',
        'late' => 'late',
        'late_w_excuse' => 'late_excused',
        'absent' => 'absent',
        'absent_w_excuse' => 'absent_excused',
    ];

    /**
     * @return array[] sessions sorted by date then time:
     *   [date, start, end, curriculum_id, curriculum, teacher, state, other_classroom]
     */
    public static function forStudent(ClassRoom $classroom, $studentId, Carbon $now)
    {
        $attendance = new ClassroomAttendance($classroom, [$studentId]);
        $slots = $attendance->slots();

        $teachers = collect($classroom->teachers ?: [])
            ->filter(function ($t) {
                return isset($t['curriculum_id']);
            })
            ->mapWithKeys(function ($t) {
                return [(int) $t['curriculum_id'] => $t['teacher_name'] ?? null];
            });
        $names = Curriculum::withTrashed()
            ->whereIn('id', array_column($slots, 'curriculum_id'))
            ->pluck('curriculum_name', 'id');

        $today = $now->toDateString();
        $nowTime = $now->format('H:i');
        $sessions = [];

        foreach ($slots as $slot) {
            $start = $slot['start'] ? $slot['start']->format('H:i') : null;
            $end = $slot['end_time'] ? Carbon::parse($slot['end_time'])->format('H:i')
                : ($slot['start'] ? $slot['start']->copy()->addHour()->format('H:i') : null);
            $hour = $slot['start'] ? $slot['start']->format('H') : null;

            foreach ($slot['dates'] as $date) {
                if ($date > $today) {
                    continue;
                }
                list($state, $fromOther) = $attendance->stateFor($slot['curriculum_id'], $date, $hour, $studentId);

                // Today's session that has not started (and is not recorded yet) is not due.
                if (! $state && $date === $today && ($start === null || $start > $nowTime)) {
                    continue;
                }

                $sessions[] = [
                    'date' => $date,
                    'start' => $start,
                    'end' => $end,
                    'curriculum_id' => $slot['curriculum_id'],
                    'curriculum' => $names->get($slot['curriculum_id'], ''),
                    'teacher' => $teachers->get($slot['curriculum_id']),
                    'state' => $state ? self::STATE_NAMES[$state] : 'unrecorded',
                    'other_classroom' => $fromOther,
                ];
            }
        }

        usort($sessions, function ($a, $b) {
            return [$a['date'], (string) $a['start']] <=> [$b['date'], (string) $b['start']];
        });

        return $sessions;
    }

    /**
     * Metric definitions (PLAN.md §7.4, Q2): unrecorded sessions are excluded from every rate;
     * attendance rate = (present + late + late_excused) ÷ recorded; punctual = present ÷ recorded.
     */
    public static function stats(array $sessions)
    {
        $counts = array_fill_keys(array_merge(array_values(self::STATE_NAMES), ['unrecorded']), 0);
        foreach ($sessions as $session) {
            $counts[$session['state']]++;
        }

        $recorded = count($sessions) - $counts['unrecorded'];
        $attended = $counts['present'] + $counts['late'] + $counts['late_excused'];

        return [
            'counts' => $counts,
            'recorded' => $recorded,
            'attended' => $attended,
            'rate' => $recorded ? round($attended / $recorded * 100, 1) : null,
            'punctual' => $recorded ? round($counts['present'] / $recorded * 100, 1) : null,
        ];
    }
}
