<?php

namespace App\Listeners;

use App\Events\AttendanceFirstRecorded;
use App\Models\Attends;
use App\Notifications\AttendanceRecordedPush;
use App\Services\Attendance\ClassroomAttendance;
use App\Services\Attendance\StudentSessions;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Each student of the first save gets their own status (Q17); the sends are queued.
 * A push problem must never fail the teacher's save (with a `sync` queue it would run inline).
 */
class SendAttendancePush
{
    public function handle(AttendanceFirstRecorded $event)
    {
        $attend = Attends::with(array_merge(['curriculum'], array_values(ClassroomAttendance::STATE_RELATIONS)))->find($event->attendId);
        if (! $attend) {
            return;
        }

        foreach (ClassroomAttendance::STATE_RELATIONS as $state => $relation) {
            if ($attend->{$relation}->isEmpty()) {
                continue;
            }
            try {
                Notification::send($attend->{$relation}, new AttendanceRecordedPush(
                    StudentSessions::STATE_NAMES[$state],
                    optional($attend->curriculum)->curriculum_name,
                    $attend->date->toDateString(),
                    $attend->start_time,
                    $attend->id
                ));
            } catch (Throwable $e) {
                report($e);
            }
        }
    }
}
