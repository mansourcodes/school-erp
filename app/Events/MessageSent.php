<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Foundation\Events\Dispatchable;

/** A teacher message was stored with its recipients (push notifications listen to this, P3.4). */
class MessageSent
{
    use Dispatchable;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }
}
