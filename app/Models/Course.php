<?php

namespace App\Models;

use App\Helpers\HtmlHelper;
use App\Http\Controllers\Report\AccountController;
use App\Http\Controllers\Report\CourseController;
use App\Http\Controllers\Report\StatisticController;
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


    protected static function boot()
    {
        parent::boot();

        // Listen for the deleting event
        static::deleting(function ($course) {
            // Delete all related classrooms
            $course->classrooms->each(function ($classroom) {
                // Delete the classroom
                $classroom->delete();
            });
        });
    }


    public function academicPath()
    {
        return $this->belongsTo(\App\Models\AcademicPath::class);
    }


    public function classRooms()
    {
        return $this->hasMany(\App\Models\ClassRoom::class);
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Account\Payment::class);
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

        $list = [
            [
                'label' => trans('reports.student'),
                'url' => getReportClassFunctions(StudentController::class, 'studentReports', $this->id)
            ],
            [
                'label' => trans('reports.course'),
                'url' => getReportClassFunctions(CourseController::class, 'courseReports', $this->id)
            ],
            [
                'label' => trans('reports.account'),
                'url' => getReportClassFunctions(AccountController::class, 'accountReports', $this->id)
            ],
            [
                'label' => trans('reports.statistic'),
                'url' => getReportClassFunctions(StatisticController::class, 'statisticReports', $this->id)
            ],
        ];

        $data['list'] = $list;
        return view('vendor.backpack.crud.buttons.multilevel_dropdown', $data);
    }
}
