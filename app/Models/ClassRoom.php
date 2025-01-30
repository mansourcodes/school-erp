<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\HtmlHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
        'first_long_name',
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
        // sort alphabetically
        return $this->belongsToMany(\App\Models\Student::class)->orderBy('name');
    }

    public function studentsCount()
    {
        return $this->belongsToMany(\App\Models\Student::class);
        // return $this->hasMany(\App\Models\Student::class);
    }

    public function getLongNameAttribute()
    {
        $curriculums = new Collection($this->curriculums);

        foreach ($curriculums as $key => $single_curriculum) {
            $time = '';
            if (isset($single_curriculum['attend_table'])) {
                $time = current($single_curriculum['attend_table']);
            }
            $return[$key] =
                @$single_curriculum['teacher_name'] . '-'
                . $single_curriculum['short_name'] . "-"
                . $this->class_room_number
                . "-(" . $time . ')';
        }

        if (empty($return)) {
            return ['Class:' . $this->id];
        }
        return $return;
    }

    public function getFirstLongNameAttribute()
    {

        return current($this->long_name);
    }

    public function getCurriculumsAttribute()
    {
        $curriculums = [];
        foreach ($this->teachers as $teachers) {

            if (!isset($teachers['curriculum_id'])) {
                continue;
            }

            $id = $teachers['curriculum_id'];
            $curriculums[$id]['teacher_name'] = $teachers['teacher_name'];
        }
        foreach ($this->attend_table as $attend_table) {

            if (!isset($attend_table['curriculum_id'])) {
                continue;
            }

            $id = $attend_table['curriculum_id'];
            $curriculums[$id]['days'][] = $attend_table['day'];
            $curriculums[$id]['attend_table'][$attend_table['day']] = $this->formatToHumanTime($attend_table['start_time']);

            if (isset($attend_table['end_time'])) {
                $curriculums[$id]['end_table'][$attend_table['day']] = $this->formatToHumanTime($attend_table['end_time']);
            } else {
                $curriculums[$id]['end_table'][$attend_table['day']] = $this->findEndTime($attend_table['start_time']);
            }
        }
        foreach ($curriculums as $curriculumId => $value) {
            $curriculums[$curriculumId]['id'] = $curriculumId;
            $curriculums[$curriculumId]['curriculum'] = Curriculum::find($curriculumId);
            $curriculums[$curriculumId]['curriculum_name'] = Curriculum::find($curriculumId)->curriculum_name;
            $curriculums[$curriculumId]['short_name'] = Curriculum::find($curriculumId)->short_name;
        }

        return $curriculums;
    }

    public function addMarksByClass()
    {
        return "        
            <a class='btn btn-primary' href='" . backpack_url('addMarksByClass/' . $this->id) . "'>
                " . __('studentmark.add_marks_by_class') . "
            </a>
        ";
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


        $data['list'] = $list;
        return view('vendor.backpack.crud.buttons.multilevel_dropdown', $data);
    }




    public function getCodeAttribute()
    {
        return  $this->course_id . "#" . $this->id;
    }

    public static function getByDate($date, $activeCourse = true)
    {
        $day_number = $date->format('N');
        $classrooms =  ClassRoom::where('attend_table', 'like', '%"day":"' . $day_number . '",%')
            ->orWhere('attend_table', 'like', '%"day":' . $day_number . ',%')
            ->get();
        $attends =  Attends::where('date', $date->format('Y-m-d'))->get()->pluck('code')->toArray();



        foreach ($classrooms as $key => $classroom) {
            $classroom->attributes['active_attend_table'] = [];


            if ($classroom->attend_table) {
                foreach ($classroom->attend_table as $attend_table) {

                    if ($attend_table['day'] == $day_number) {

                        $attend_table['curriculum'] = Curriculum::find($attend_table['curriculum_id']);
                        $chosen_date = Carbon::parse($attend_table['start_time']);
                        $attend_table['start_time'] = $chosen_date;


                        $date_string = (new Carbon($date))->format('Y-m-d');
                        $start_time = (new Carbon($attend_table['start_time']))->format('H:m');
                        $attend_table_code = $date_string . '#' . $start_time . '#' . $classroom->code . '#' . $attend_table['curriculum_id'];


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



    private function findEndTime($start_time)
    {

        $date = Carbon::parse($start_time)->addHour();
        $time = $date->format('h:i a');

        $time = str_replace('am', 'ص', $time);
        $time = str_replace('pm', 'م', $time);

        return $time;
    }

    private function formatToHumanTime($time)
    {

        $date = Carbon::parse($time);
        $time = $date->format('h:i a');

        $time = str_replace('am', 'ص', $time);
        $time = str_replace('pm', 'م', $time);

        return $time;
    }




    function cloneClassRoomWithStudents()
    {
        // Start a database transaction
        DB::beginTransaction();

        try {
            // Retrieve the original ClassRoom
            $originalClassRoom = ClassRoom::with('students')->findOrFail($this->id);

            // Create a copy of the ClassRoom
            $clonedClassRoom = $originalClassRoom->replicate();
            $clonedClassRoom->save();

            // Sync the students to the cloned ClassRoom
            $studentIds = $originalClassRoom->students->pluck('id');
            $clonedClassRoom->students()->sync($studentIds);

            // Commit the transaction
            DB::commit();

            return $clonedClassRoom;
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Rethrow the exception or handle it as needed
            throw $e;
        }
    }
}
