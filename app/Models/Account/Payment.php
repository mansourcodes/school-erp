<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_id',
        'student_id',
        'amount',
        'source',
        'meta',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'course_id' => 'integer',
        'student_id' => 'integer',
        'amount' => 'double',
    ];


    public function course()
    {
        return $this->belongsTo(\App\Models\Account\Course::class);
    }

    public function student()
    {
        return $this->belongsTo(\App\Models\Account\Student::class);
    }
}
