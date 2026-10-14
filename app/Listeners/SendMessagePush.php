<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Services\Portal\PushOutbox;
use Throwable;

/**
 * Records a push per recipient in the outbox (PLAN.md §5.12); the sending teacher's app sends
 * them one by one, while an admin send goes out now. A push problem must never fail the send itself.
 */
class SendMessagePush
{
    public function handle(MessageSent $event)
    {
        try {
            $outbox = app(PushOutbox::class);
            $ids = $outbox->queueMessage($event->message, $event->message->sender_user_id);
            if ($event->sendNow) {
                $outbox->sendMany($ids);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
