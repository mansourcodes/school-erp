<?php

namespace App\Models;

use App\Helpers\HtmlHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentMarks extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id',
        'course_id',
        'total_mark',
        'final_grade',
        'marks',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'marks' => 'array',
        'total_mark' => 'float',
    ];


    public function Student()
    {
        return $this->belongsTo(\App\Models\Student::class);
        // return $this->hasOne(\App\Models\Student::class);
    }

    public function Course()
    {
        return $this->belongsTo(\App\Models\Course::class);
        // return $this->hasOne(\App\Models\Course::class);
    }



    public function getPrintDropdown()
    {

        $list = [
            [
                'label' => trans('reports.transcript'),
                'url' => backpack_url('reports?view=transcript&studentmarks=' . $this->id),
            ],
            [
                'label' => trans('reports.student_edu_statement'),
                'url' => backpack_url('reports?view=student_edu_statement&studentmarks=' . $this->id),
            ],
            [
                'label' => trans('reports.student_courses_transcript'),
                'url' => backpack_url('reports?view=student_courses_transcript&studentmarks=' . $this->id),
            ],
        ];


        $html = HtmlHelper::dropdownMenuButton($list);

        return $html;
    }
}
