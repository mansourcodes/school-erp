<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Models\Student;
use App\Notifications\MessageReceivedPush;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Notifies the recipients of a teacher message (PLAN.md §5.12); sends are queued, in chunks.
 * A push problem must never fail the send itself.
 */
class SendMessagePush
{
    public function handle(MessageSent $event)
    {
        $message = $event->message;
        $ids = $message->recipients()->pluck('student_id');

        try {
            Student::whereIn('id', $ids)->chunkById(100, function ($students) use ($message) {
                Notification::send($students, new MessageReceivedPush($message->id, $message->body));
            });
        } catch (Throwable $e) {
            report($e);
        }
    }
}
