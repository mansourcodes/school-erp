<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One student's copy of a message, with when they read it. */
class MessageRecipient extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'message_id',
        'student_id',
        'read_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'message_id' => 'integer',
        'student_id' => 'integer',
        'read_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }
}
