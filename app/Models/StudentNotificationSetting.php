<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Push preferences of one student (PLAN.md §5.12); both default to on. */
class StudentNotificationSetting extends Model
{
    const TYPES = ['attendance', 'messages'];

    protected $fillable = [
        'student_id',
        'attendance',
        'messages',
    ];

    protected $casts = [
        'student_id' => 'integer',
        'attendance' => 'boolean',
        'messages' => 'boolean',
    ];

    protected $attributes = [
        'attendance' => true,
        'messages' => true,
    ];

    public static function for(Student $student)
    {
        return static::firstOrNew(['student_id' => $student->id]);
    }
}
