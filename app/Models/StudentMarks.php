<?php

namespace App\Models;

use App\Helpers\BriefMarkHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\MarksDetailsCast;
use Mpdf\Tag\Br;

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
        'marks' => MarksDetailsCast::class,
        'total_mark' => 'float',
    ];

    protected $appends = [
        'brief_marks',
        'curriculum',

    ];


    static $standard_marks_composer = [
        'finalexam_mark_details'         => 'Single',
        'project_marks_details'         => 'None',
        'midexam_marks_details'         => 'None',
        'practice_mark_details'         => 'None',
        'memorize_mark_details'         => 'None',
        'class_mark_details'         => 'None',
        'attend_mark_details'         => 'None',
        'marks_details'         => 'None',
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

    public function getBriefMarksAttribute()
    {



        $brief_marks = [];
        foreach (StudentMarks::$standard_marks_composer as $key => $compress_type) {
            $curriculum_mark_template = $this->curriculum->marks_labels[$key];
            $student_mark = $this->marks[0][$key];

            if ($compress_type == 'None') {
                //
            } elseif ($compress_type == 'Single') {
                $brief_marks = [...array_values($brief_marks), ...BriefMarkHelper::composeMark($curriculum_mark_template, $student_mark, $compress_type, $key)];
            } else {
                $brief_marks[$key] = BriefMarkHelper::composeMark($curriculum_mark_template, $student_mark, $compress_type, $key);
            }
        }
        // sort by max marks
        usort($brief_marks, function ($a, $b) {
            return $b->max <=> $a->max; // Descending order
        });

        // dd($brief_marks);
        return $brief_marks;
    }

    public function getCurriculumAttribute()
    {
        $curriculum_id = $this->marks[0]['curriculum_id'];
        return Curriculum::find($curriculum_id);
    }



    public function getPrintDropdown()
    {

        $list = [
            [
                'label' => trans('reports.transcript'),
                'url' => backpack_url('reports?view=transcript&studentmarks=' . $this->id),
            ],
            [
                'label' => trans('reports.certificate'),
                'url' => backpack_url('reports?view=certificate&studentmarks=' . $this->id),
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


        $data['list'] = $list;
        return view('vendor.backpack.crud.buttons.multilevel_dropdown', $data);
    }
}
