<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attends extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date',
        'start_time',
        'class_room_id',
        'course_id',
        'curriculum_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'date' => 'date',
    ];


    public function attendStudents()
    {
        return $this->belongsToMany(\App\Models\Student::class, 'attends_student_attend');
    }

    public function absentStudents()
    {
        return $this->belongsToMany(\App\Models\Student::class, 'attends_student_absent');
    }

    public function absentWithExcuseStudents()
    {
        return $this->belongsToMany(\App\Models\Student::class, 'attends_student_absent_w_excuse');
    }

    public function lateStudents()
    {
        return $this->belongsToMany(\App\Models\Student::class, 'attends_student_late');
    }

    public function lateWithExcuseStudents()
    {
        return $this->belongsToMany(\App\Models\Student::class, 'attends_student_late_w_excuse');
    }

    public function classRoom()
    {
        return $this->belongsTo(\App\Models\ClassRoom::class);
    }

    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class);
    }

    public function curriculum()
    {
        return $this->belongsTo(\App\Models\Curriculum::class);
    }
}
