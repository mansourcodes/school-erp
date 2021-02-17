<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicPath extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'academic_path_name',
        'academic_path_type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];


    public function curricula()
    {
        return $this->belongsToMany(\App\Models\Curriculum::class);
        // return $this->hasMany(\App\Models\Curriculum::class);
    }

    public function courses()
    {
        return $this->hasMany(\App\Models\Course::class);
        // return $this->belongsToMany(\App\Models\Course::class);
    }
}
