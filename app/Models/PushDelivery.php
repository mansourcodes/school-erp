<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One student's pending push after a teacher save or message (push outbox, see PushOutbox). */
class PushDelivery extends Model
{
    const PENDING = 'pending';
    const SENDING = 'sending';
    const SENT = 'sent';
    const FAILED = 'failed';
    const SKIPPED = 'skipped';

    protected $fillable = [
        'user_id',
        'student_id',
        'type',
        'attend_id',
        'message_id',
        'state',
        'status',
        'attempts',
        'sent_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'student_id' => 'integer',
        'attend_id' => 'integer',
        'message_id' => 'integer',
        'attempts' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
