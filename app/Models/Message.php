<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A message to selected students of one classroom (PLAN.md §5.11), from a teacher or from the
 * Superadmin (/admin/message). One-way: students read only; there is no reply.
 */
class Message extends Model
{
    use CrudTrait, SoftDeletes;

    /** Kept for this many days, then pruned (Q15). */
    const RETENTION_DAYS = 90;

    const MAX_LENGTH = 500;

    protected $fillable = [
        'sender_user_id',
        'class_room_id',
        'body',
    ];

    protected $casts = [
        'id' => 'integer',
        'sender_user_id' => 'integer',
        'class_room_id' => 'integer',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id')->withTrashed();
    }

    public function recipients()
    {
        return $this->hasMany(MessageRecipient::class);
    }
}
