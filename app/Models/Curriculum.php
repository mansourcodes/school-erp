<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Curriculum extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'curriculumـname',
        'bookـname',
        'weightـinـhours',
        'curriculum_category_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    protected $appends = [
        'long_name'
    ];


    public function curriculumCategory()
    {
        // return $this->hasOne(\App\Models\CurriculumCategory::class);
        return $this->belongsTo(\App\Models\CurriculumCategory::class);
    }

    public function academicPaths()
    {
        return $this->hasMany(\App\Models\AcademicPath::class);
        // return $this->belongsToMany(\App\Models\AcademicPath::class);
    }


    public function getLongNameAttribute()
    {
        return "[" . $this->curriculumـname . "] " . $this->bookـname;
    }
}
