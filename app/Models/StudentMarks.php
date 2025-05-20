<?php

namespace App\Models;

use App\Helpers\HtmlHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\MarksDetailsCast;


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

        $standard_marks_composer = [
            'finalexam_mark_details'         => 'Single',
            'project_marks_details'         => 'None',
            'midexam_marks_details'         => 'SumMerge',
            'practice_mark_details'         => 'Single',
            'memorize_mark_details'         => 'AvgMerge',
            'class_mark_details'         => 'None',
            'attend_mark_details'         => 'None',
            'marks_details'         => 'None',
        ];





        function getBriefMark($detailed_mark)
        {
            $brief_marks = [];
            foreach ($detailed_mark as $key => $mark) {
                if ($mark === null || $mark == '') {
                    continue;
                } elseif (is_numeric($mark)) {
                    continue;
                } elseif (
                    $key == 'finalexam_mark_details' || $key == 'practice_mark_details'
                    // || $key == 'class_mark_details' || $key == 'marks_details' || $key == 'project_marks_details' || $key == 'practice_mark_details' || $key == 'memorize_mark_details'
                ) {
                    $arrayOfArrays = array_map(function ($item) {
                        return (array) $item;
                    }, $mark);
                    $brief_marks = [...array_values($brief_marks), ...$arrayOfArrays];
                } else {
                    $data = collect($mark);
                    $total = $data->sum('mark');
                    $brief_marks[$key] = [
                        'label' => $key,
                        'mark' => $total
                    ];
                }
            }
            return $brief_marks;
        }


        // add max marks

        $marks_labels = getBriefMark($this->curriculum->marks_labels);
        $brief_marks = getBriefMark($this->marks[0]);

        dd($marks_labels, $brief_marks);

        foreach ($brief_marks as $key => $mark) {
            dd($key, $mark);
            if (isset($marks_labels[$key])) {
                dd($key);
                if (
                    $key == 'memorize_mark_details'
                    // $key == 'finalexam_mark_details' || $key == 'practice_mark_details'
                    // || $key == 'class_mark_details' || $key == 'marks_details' || $key == 'project_marks_details' || $key == 'practice_mark_details' || $key == 'memorize_mark_details'
                ) {
                    dd($marks_labels[$key]);
                    $brief_marks[$key]['max'] = 10;
                    // $brief_marks[$key]['max'] = $marks_labels[$key]['mark'];
                } else {
                    $brief_marks[$key]['max'] = $marks_labels[$key]['mark'];
                }
            }
        }

        // sort by max marks
        // usort($brief_marks, function ($a, $b) {
        //     return $b['max'] <=> $a['max']; // Descending order
        // });

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
