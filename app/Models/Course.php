<?php

namespace App\Models;

use App\Helpers\HtmlHelper;
use App\Http\Controllers\Report\StudentController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
        'start_date',
        'end_date',
        'is_active',
        'academic_path_id',
        'course_root_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];


    protected $appends = [
        'long_name'
    ];

    public function academicPath()
    {
        return $this->belongsTo(\App\Models\AcademicPath::class);
    }


    public function classRooms()
    {
        return $this->hasMany(\App\Models\ClassRoom::class);
    }


    public function courseRootId()
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_root_id');
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
        $html = '';
        $list = [
            [
                'label' => trans('reports.transcript'),
                'url' => backpack_url('reports?view=transcript&course=' . $this->id),
            ],
        ];

        $html .= HtmlHelper::dropdownMenuButton($list);

        $html .= HtmlHelper::dropdownMenuButton($this->getStudentReportsList(), trans('reports.student'));

        return $html;
    }



    public function getStudentReportsList()
    {

        $list = [];
        $class_methods = get_class_methods(new StudentController());
        foreach ($class_methods as $method_name) {
            $functionOriginalName = substr($method_name, 0, -1);
            if (substr($method_name, -1) === '_') {

                array_push($list, [
                    'label' => trans('reports.' . Str::of($functionOriginalName)->snake()),
                    'url' => backpack_url('studentReports?view=' . $functionOriginalName . '&course='),
                ]);
            }
        }
        return $list;
    }
}
