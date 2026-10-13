<?php

namespace App\Services\Teacher;

use App\Models\Attends;
use App\Models\ClassRoom;
use App\Models\Curriculum;
use App\Services\Attendance\ClassroomAttendance;
use App\Services\Attendance\StudentSessions;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * The expected sessions of a teacher's subjects in one classroom (timetable × course dates,
 * same dates as the admin report), each with this classroom's own record, if any.
 */
class TeacherSessions
{
    /** Code in URLs: date#HH:MM#classroom#curriculum (PLAN.md §8); "--:--" for a slot without a time. */
    const NO_TIME = '--:--';

    public static function code($date, $start, $classroomId, $curriculumId)
    {
        return $date . '#' . ($start ?: self::NO_TIME) . '#' . $classroomId . '#' . $curriculumId;
    }

    /** @return array|null [date, start|null, classroom_id, curriculum_id] */
    public static function parse($code)
    {
        if (! preg_match('/^(\d{4}-\d{2}-\d{2})#(\d{2}:\d{2}|--:--)#(\d+)#(\d+)$/', (string) $code, $m)) {
            return null;
        }

        return [$m[1], $m[2] === self::NO_TIME ? null : $m[2], (int) $m[3], (int) $m[4]];
    }

    /**
     * @param  int[]  $curriculumIds  the teacher's subjects in this classroom
     * @return array[] sorted by date, time:
     *   [code, classroom_id, date, start, end, curriculum_id, curriculum, status, recorded, editable, counts, updated_at]
     *   status: recorded | pending (past, never recorded) | open (today, not recorded) | upcoming
     */
    public static function forClassroom(ClassRoom $classroom, array $curriculumIds, Carbon $now)
    {
        $slots = array_filter((new ClassroomAttendance($classroom, []))->slots(), function ($slot) use ($curriculumIds) {
            return in_array($slot['curriculum_id'], $curriculumIds, true);
        });
        $names = Curriculum::withTrashed()->whereIn('id', $curriculumIds)->pluck('curriculum_name', 'id');
        $studentIds = $classroom->students->pluck('id')->all();
        $records = self::records($classroom, $curriculumIds, $studentIds);
        $today = $now->toDateString();

        $sessions = [];
        foreach ($slots as $slot) {
            $start = $slot['start'] ? $slot['start']->format('H:i') : null;
            $end = $slot['end_time'] ? Carbon::parse($slot['end_time'])->format('H:i')
                : ($slot['start'] ? $slot['start']->copy()->addHour()->format('H:i') : null);

            foreach ($slot['dates'] as $date) {
                $record = self::match($records, $slot['curriculum_id'], $date, $start);
                $status = $record ? 'recorded' : ($date < $today ? 'pending' : ($date === $today ? 'open' : 'upcoming'));

                $sessions[] = [
                    'code' => self::code($date, $start, $classroom->id, $slot['curriculum_id']),
                    'classroom_id' => $classroom->id,
                    'date' => $date,
                    'start' => $start,
                    'end' => $end,
                    'curriculum_id' => $slot['curriculum_id'],
                    'curriculum' => $names->get($slot['curriculum_id'], ''),
                    'status' => $status,
                    'recorded' => (bool) $record,
                    // Same-day recording at any time; later days are read-only (Q9). Any past day is editable (Q10).
                    'editable' => $date <= $today,
                    'counts' => $record ? self::counts($record) : null,
                    'updated_at' => $record ? $record->updated_at->toIso8601String() : null,
                ];
            }
        }

        usort($sessions, function ($a, $b) {
            return [$a['date'], (string) $a['start']] <=> [$b['date'], (string) $b['start']];
        });

        return $sessions;
    }

    /** One session by code, or null when it is not on this classroom's timetable for the teacher. */
    public static function find(ClassRoom $classroom, array $curriculumIds, $code, Carbon $now)
    {
        foreach (self::forClassroom($classroom, $curriculumIds, $now) as $session) {
            if ($session['code'] === $code) {
                return $session;
            }
        }

        return null;
    }

    /** This classroom's Attends for the subjects, with state counts of current students only. */
    protected static function records(ClassRoom $classroom, array $curriculumIds, array $studentIds)
    {
        $onlyCurrent = function ($q) use ($studentIds) {
            $q->whereIn('students.id', $studentIds);
        };
        $counts = [];
        foreach (ClassroomAttendance::STATE_RELATIONS as $relation) {
            $counts[$relation] = $onlyCurrent;
        }

        return Attends::where('class_room_id', $classroom->id)
            ->whereIn('curriculum_id', $curriculumIds)
            ->withCount($counts)
            ->orderBy('id')
            ->get();
    }

    /** Same rule as the report: same hour, else any record of the subject that day (slot without time). */
    protected static function match($records, $curriculumId, $date, $start)
    {
        $sameDay = $records->filter(function (Attends $a) use ($curriculumId, $date) {
            return (int) $a->curriculum_id === $curriculumId && $a->date->format('Y-m-d') === $date;
        });
        if ($start === null) {
            return $sameDay->last();
        }

        return $sameDay->last(function (Attends $a) use ($start) {
            return Carbon::parse($a->start_time)->format('H') === substr($start, 0, 2);
        }) ?: null;
    }

    protected static function counts(Attends $record)
    {
        $counts = [];
        foreach (ClassroomAttendance::STATE_RELATIONS as $state => $relation) {
            $counts[StudentSessions::STATE_NAMES[$state]] = (int) $record->{Str::snake($relation) . '_count'};
        }

        return $counts;
    }
}
