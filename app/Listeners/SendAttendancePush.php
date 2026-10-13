<?php

namespace App\Listeners;

use App\Events\AttendanceFirstRecorded;
use App\Services\Portal\PushOutbox;
use Throwable;

/**
 * Each student of the first save gets their own status (Q17). A teacher save only records the
 * pushes in the outbox (the teacher's app sends them one by one); an admin save sends them now.
 * A push problem must never fail the save.
 */
class SendAttendancePush
{
    public function handle(AttendanceFirstRecorded $event)
    {
        try {
            $outbox = app(PushOutbox::class);
            $ids = $outbox->queueAttendance($event->attendId, $event->senderUserId);
            if ($event->senderUserId === null) {
                $outbox->sendMany($ids);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
