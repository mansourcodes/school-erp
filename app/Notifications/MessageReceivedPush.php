<?php

namespace App\Notifications;

use App\Support\PortalBranding;
use Illuminate\Support\Str;

/**
 * New teacher message (PLAN.md §4, Q16): titled with the centre name, the first ~80 characters
 * as body; opens the inbox at that message.
 */
class MessageReceivedPush extends PortalPush
{
    public $messageId;

    public $text;

    public function __construct($messageId, $text)
    {
        $this->messageId = $messageId;
        $this->text = $text;
    }

    protected function type()
    {
        return 'messages';
    }

    protected function title()
    {
        return PortalBranding::get('centre');
    }

    protected function body()
    {
        return Str::limit(preg_replace('/\s+/u', ' ', $this->text), 80);
    }

    protected function tag()
    {
        return 'message-' . $this->messageId;
    }

    protected function path()
    {
        return '/messages#' . $this->messageId;
    }
}
