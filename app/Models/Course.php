<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_year',
        'hijri_year',
        'semester',
        'duration',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];


    public function academicPath()
    {
        return $this->belongsTo(\App\Models\AcademicPath::class);
        // return $this->hasOne(\App\Models\AcademicPath::class);
    }

    public function classRooms()
    {
        return $this->hasMany(\App\Models\ClassRoom::class);
        // return $this->belongsToMany(\App\Models\ClassRoom::class);
    }
}
