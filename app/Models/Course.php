<?php

namespace App\Models;

use App\Helpers\HtmlHelper;
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
        'academic_path_id',
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

    public function getLongNameAttribute()
    {

        if (is_null($this->academicPath)) {
            return  "... [" . $this->course_year . "] " . $this->hijri_year . " [" . $this->semester  . "]";
        } else {
            return $this->academicPath->academic_path_name . " [" . $this->course_year . "] " . $this->hijri_year . " [" . $this->semester  . "]";
        }
    }


    public function getPrintDropdown()
    {

        $list = [
            [
                'label' => trans('reports.transcript'),
                'url' => backpack_url('reports?view=transcript&course=' . $this->id),
            ],
        ];


        $html = HtmlHelper::dropdownMenuButton($list);


        return $html;
    }
}
