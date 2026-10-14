<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A message was stored with its recipients (push notifications listen to this, P3.4). A teacher's
 * app sends the pushes one by one; an admin send ($sendNow) has no app to do it, so the server does.
 */
class MessageSent
{
    use Dispatchable;

    public $message;

    public $sendNow;

    public function __construct(Message $message, $sendNow = false)
    {
        $this->message = $message;
        $this->sendNow = $sendNow;
    }
}
