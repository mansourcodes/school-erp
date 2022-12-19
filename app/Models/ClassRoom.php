<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\HtmlHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassRoom extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'class_room_name',
        'class_room_number',
        'course_id',
        'teachers',
        'attend_table',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'teachers' => 'array',
        'attend_table' => 'array',
    ];


    protected $appends = [
        'long_name',
        'code',
        'curriculums',
    ];

    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class);
        // return $this->hasOne(\App\Models\Course::class);
    }

    public function students()
    {
        return $this->belongsToMany(\App\Models\Student::class);
        // return $this->hasMany(\App\Models\Student::class);
    }

    public function studentsCount()
    {
        return $this->belongsToMany(\App\Models\Student::class);
        // return $this->hasMany(\App\Models\Student::class);
    }

    public function getLongNameAttribute()
    {
        return $this->class_room_name . " [" . $this->class_room_number . "]";
    }

    public function getCurriculumsAttribute()
    {
        $curriculums = [];
        foreach ($this->teachers as $teachers) {
            $id = $teachers['curriculumـid'];
            $curriculums[$id]['teacher_name'] = $teachers['teacher_name'];
        }
        foreach ($this->attend_table as $attend_table) {
            $id = $attend_table['curriculumـid'];
            $curriculums[$id]['days'][] = $attend_table['day'];
            $curriculums[$id]['attend_table'][$attend_table['day']] = $attend_table['start_time'];
        }
        foreach ($curriculums as $curriculumId => $value) {
            $curriculums[$curriculumId]['id'] = $curriculumId;
            $curriculums[$curriculumId]['curriculumـname'] = Curriculum::find($curriculumId)->curriculumـname;
        }

        return $curriculums;
    }

    public function getPrintDropdown()
    {

        $list = [
            [
                'label' => trans('reports.student_attend_list'),
                'url' => backpack_url('reports?view=student_attend_list&classroom=' . $this->id),
            ],
            [
                'label' => trans('reports.student_attend_report'),
                'url' => backpack_url('reports?view=student_attend_report&classroom=' . $this->id),
            ],
            [
                'label' => trans('reports.student_attend'),
                'url' => backpack_url('reports?view=student_attend&classroom=' . $this->id),
            ],
            [
                'label' => trans('reports.scoring_sheet'),
                'url' => backpack_url('reports?view=scoring_sheet&classroom=' . $this->id),
            ],
            [
                'label' => trans('reports.transcript'),
                'url' => backpack_url('reports?view=transcript&classroom=' . $this->id),
            ],
            [
                'label' => trans('reports.student_edu_statement'),
                'url' => backpack_url('reports?view=student_edu_statement&classroom=' . $this->id),
            ],
        ];

        $html = HtmlHelper::dropdownMenuButton($list);

        return $html;
    }




    public function getCodeAttribute()
    {
        return  $this->course_id . "#" . $this->id;
    }

    public static function getByDate($date, $activeCourse = true)
    {
        $day_number = $date->format('N');
        $classrooms =  ClassRoom::where('attend_table', 'like', '%"day":"' . $day_number . '"%')->get();
        $attends =  Attends::where('date', $date->format('Y-m-d'))->get()->pluck('code')->toArray();



        foreach ($classrooms as $key => $classroom) {
            $classroom->attributes['active_attend_table'] = [];


            if ($classroom->attend_table) {
                foreach ($classroom->attend_table as $attend_table) {

                    if ($attend_table['day'] == $day_number) {

                        $attend_table['curriculum'] = Curriculum::find($attend_table['curriculumـid']);
                        $chosen_date = Carbon::parse($attend_table['start_time']);
                        $attend_table['start_time'] = $chosen_date;


                        $date_string = (new Carbon($date))->format('Y-m-d');
                        $start_time = (new Carbon($attend_table['start_time']))->format('H:m');
                        $attend_table_code = $date_string . '#' . $start_time . '#' . $classroom->code . '#' . $attend_table['curriculumـid'];


                        if (in_array($attend_table_code, $attends)) {
                            $attend_table['is_recorded'] = true;
                        } else {
                            $attend_table['is_recorded'] = false;
                        }

                        $attend_table['code'] = $attend_table_code;
                        $classroom->attributes['active_attend_table'][] =  $attend_table;
                    }
                }
            }
        }

        return $classrooms;
    }
}
