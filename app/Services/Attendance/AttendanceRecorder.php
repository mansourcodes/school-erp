<?php

namespace App\Services\Attendance;

use App\Events\AttendanceFirstRecorded;
use App\Models\Attends;
use App\Models\ClassRoom;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Writes attendance: one Attends row per session + its five state pivots.
 * Used by the admin easy form (pivots only) and the teacher portal (whole save).
 */
class AttendanceRecorder
{
    /**
     * Teacher save (PUT /teacher/sessions/{code}): idempotent, so an offline retry is safe.
     * Finds this classroom's record of the session with the reports' rule (same hour, or any
     * record of that subject and day for a slot without a time), else creates it.
     *
     * @param  array  $states  student_id => stored state (attend, late, late_w_excuse, absent, absent_w_excuse)
     * @param  int|null  $teacherId  the teacher whose app sends the pushes (push outbox)
     * @return array [Attends, bool created]
     */
    public function save(ClassRoom $classroom, $curriculumId, $date, $start, array $states, Carbon $now, $teacherId = null)
    {
        $result = DB::transaction(function () use ($classroom, $curriculumId, $date, $start, $states, $now) {
            // One save per classroom at a time: a Background Sync replay and the page's resend can arrive
            // together, and both would miss the record and create one each. The match is by hour, so no
            // unique key can catch it.
            ClassRoom::whereKey($classroom->id)->lockForUpdate()->first();
            $attend = $this->find($classroom, $curriculumId, $date, $start);
            $created = false;

            if (! $attend) {
                $attend = Attends::create([
                    'date' => $date,
                    'start_time' => $start ?: $now->format('H:i'),
                    'class_room_id' => $classroom->id,
                    'course_id' => $classroom->course_id,
                    'curriculum_id' => $curriculumId,
                ]);
                $created = true;
            }

            $this->syncStates($attend, $states);
            $attend->touch();

            return [$attend->fresh(), $created];
        });

        // First save only (after commit): students get their status once; edits never notify.
        if ($result[1]) {
            event(new AttendanceFirstRecorded($result[0]->id, $teacherId));
        }

        return $result;
    }

    /** This classroom's record of one session, matched like ClassroomAttendance (by hour). */
    public function find(ClassRoom $classroom, $curriculumId, $date, $start)
    {
        $records = Attends::where('class_room_id', $classroom->id)
            ->where('curriculum_id', $curriculumId)
            ->whereDate('date', $date)
            ->orderByDesc('id')
            ->get();

        if ($start === null) {
            return $records->first();
        }

        $hour = substr($start, 0, 2);

        return $records->first(function (Attends $attend) use ($hour) {
            return Carbon::parse($attend->start_time)->format('H') === $hour;
        });
    }

    /**
     * Replaces the five state pivots of a record.
     *
     * @param  array  $states  student_id => stored state
     */
    public function syncStates(Attends $attend, array $states)
    {
        $byState = array_fill_keys(array_keys(ClassroomAttendance::STATE_RELATIONS), []);
        foreach ($states as $studentId => $state) {
            $byState[$state][] = $studentId;
        }

        foreach (ClassroomAttendance::STATE_RELATIONS as $state => $relation) {
            $attend->{$relation}()->sync($byState[$state]);
        }
    }
}
