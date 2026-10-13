<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * A session's Attends record was created (its first save) with its states synced.
 * Edits of an existing record never fire this, so students are notified once (PLAN.md §5.12).
 */
class AttendanceFirstRecorded
{
    use Dispatchable;

    public $attendId;

    /** The teacher whose app sends the pushes (push outbox); null = the server sends them now (admin). */
    public $senderUserId;

    public function __construct($attendId, $senderUserId = null)
    {
        $this->attendId = $attendId;
        $this->senderUserId = $senderUserId;
    }
}
