<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\StudentMarks;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class 
 */
class StatisticController extends Controller
{

    /**
     * Title: 
     *
     * @return view
     */
    public function print(Request $request)
    {

        $view = $request->input('view');
        $function =  Str::camel($view) . '_';
        $data = $this->{$function}($request);

        $print = $request->input('print');
        if ($print == 'pdf') {
            $data['print'] = 'pdf';
            // $pdf = PDF::loadView('reports.' . $view, $data);
            // return $pdf->stream();
        }

        $data['print'] = 'print';
        $data['title'] = trans('reports.' . Str::of(substr($function, 0, -1))->snake());
        return view('reports.statistic.' . Str::snake($view), $data);
    }





    /**
     * Title: 
     *
     * @return view
     */
    public function classesAndStudentsStatisticsForEachLevel_(Request $request)
    {
        $return['_'] = '';
        $course = Course::find($request->course);
        $return['course'] = $course;


        $curriculums_array = [];
        foreach ($course->classRooms as $key => $classRoom) {
            $curriculums_array = array_merge($curriculums_array,  $classRoom->curriculums);
        }
        $curriculums_statistics  = [];
        foreach ($curriculums_array as $key => $curriculum) {
            $curriculums_statistics[$curriculum['id']] = $curriculum;
        }



        foreach ($course->classRooms as $key => $classRoom) {
            foreach ($classRoom->curriculums as $curriculum_id => $curriculum) {
                if (!isset($curriculums_statistics[$curriculum_id]['count_class_rooms'])) {
                    $curriculums_statistics[$curriculum_id]['count_class_rooms'] = 1;
                    $curriculums_statistics[$curriculum_id]['count_students'] = $classRoom->students->count();
                } else {
                    $curriculums_statistics[$curriculum_id]['count_class_rooms']++;
                    $curriculums_statistics[$curriculum_id]['count_students'] += $classRoom->students->count();
                }
            }
        }

        $return['curriculums_statistics'] = $curriculums_statistics;
        $return['total']['count_class_rooms'] = collect(Arr::pluck($curriculums_statistics, 'count_class_rooms'))->sum();
        $return['total']['count_students'] = collect(Arr::pluck($curriculums_statistics, 'count_students'))->sum();


        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function studentDetectionStatisticsInClasses_(Request $request)
    {
        $return['_'] = '';
        $course = Course::find($request->course);
        $return['classRooms'] = $course->classRooms;
        $return['course'] = $course;

        $return['weekDays'] = [
            7 =>  trans('base.Sunday'),
            1 => trans('base.Monday'),
            2 => trans('base.Tuesday'),
            3 => trans('base.Wednesday'),
            4 => trans('base.Thursday'),
            5 => trans('base.Friday'),
            6 => trans('base.Saturday'),
        ];


        return $return;
    }



    /**
     * Title: 
     *
     * @return view
     */
    public function studyGroupDataDisclosureStatistics_(Request $request)
    {
        $return['_'] = '';
        $course = Course::find($request->course);
        $return['classRooms'] = $course->classRooms;
        $return['course'] = $course;

        $return['weekDays'] = [
            7 =>  trans('base.Sunday'),
            1 => trans('base.Monday'),
            2 => trans('base.Tuesday'),
            3 => trans('base.Wednesday'),
            4 => trans('base.Thursday'),
            5 => trans('base.Friday'),
            6 => trans('base.Saturday'),
        ];
        return $return;
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function studentsNamesSheet_(Request $request)
    {
        $return['_'] = '';
        $course = Course::find($request->course);
        $return['classRooms'] = $course->classRooms;
        $return['course'] = $course;

        $return['weekDays'] = [
            7 =>  trans('base.Sunday'),
            1 => trans('base.Monday'),
            2 => trans('base.Tuesday'),
            3 => trans('base.Wednesday'),
            4 => trans('base.Thursday'),
            5 => trans('base.Friday'),
            6 => trans('base.Saturday'),
        ];
        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function studentsMarksSheet_(Request $request)
    {
        $return['_'] = '';
        $course = Course::find($request->course);
        $return['classRooms'] = $course->classRooms;
        $return['course'] = $course;

        $return['weekDays'] = [
            7 =>  trans('base.Sunday'),
            1 => trans('base.Monday'),
            2 => trans('base.Tuesday'),
            3 => trans('base.Wednesday'),
            4 => trans('base.Thursday'),
            5 => trans('base.Friday'),
            6 => trans('base.Saturday'),
        ];
        return $return;
    }



    /**
     * Title: 
     *
     * @return view
     */
    public function perfectionMarksByClassSheet_(Request $request)
    {
        $return['_'] = '';
        $course = Course::find($request->course);
        $return['course'] = $course;


        // get class rooms curriculums statistics
        $classRooms = $course->classRooms;
        $classRoomCurriculumsStatistics = collect([]);
        foreach ($classRooms as $classRoom) {
            foreach ($classRoom->curriculums as $curriculum_key => $curriculum) {
                $classRoomCurriculumsStatistics->push([
                    'name' => $classRoom->long_name[$curriculum_key],
                    'curriculum_id' => $curriculum['id'],
                    'marks' => collect(),
                    'students' => $classRoom->students->pluck('id')->toArray(),
                ]);
            }
        }




        // get students marks
        $studentsMarks = StudentMarks::where('course_id', $course->id)->get();
        $courseStatistics = collect();

        // push statistics to each class room curriculum
        foreach ($studentsMarks as $studentMark) {

            // if total_mark is 0, skip it
            if ($studentMark->total_mark == 0) {
                continue;
            }

            $curriculum_id = $studentMark->curriculum->id;
            $student_id = $studentMark->student_id;
            // find the class room curriculum by curriculum_id and student_id
            $classRoomCurriculum = $classRoomCurriculumsStatistics->first(function ($item) use ($curriculum_id, $student_id) {
                return $item['curriculum_id'] == $curriculum_id && in_array($student_id, $item['students']);
            });
            // if found, push the marks to the class room curriculum
            if ($classRoomCurriculum) {
                $classRoomCurriculum['marks']->push([
                    'total_mark' => $studentMark->total_mark,
                ]);

                $courseStatistics->push([
                    'total_mark' => $studentMark->total_mark,
                ]);
            }
        }




        // calculate the statistics for each class room curriculum
        foreach ($classRoomCurriculumsStatistics as $key => $classRoomCurriculum) {
            $classRoomCurriculum['count'] = $classRoomCurriculum['marks']->count();
            $classRoomCurriculum['average'] = number_format($classRoomCurriculum['marks']->avg('total_mark') ?? 0, 2);

            if ($classRoomCurriculum['count'] == 0) {
                $classRoomCurriculum['less_than_70_per'] = '-';
                $classRoomCurriculum['more_than_70_per'] = '-';
                $classRoomCurriculum['more_than_80_per'] = '-';
            } else {
                $classRoomCurriculum['less_than_70_per'] = number_format($classRoomCurriculum['marks']->where('total_mark', '<', 70)->count() / $classRoomCurriculum['count'] * 100, 2);
                $classRoomCurriculum['more_than_70_per'] = number_format($classRoomCurriculum['marks']->where('total_mark', '>', 70)->count() / $classRoomCurriculum['count'] * 100, 2);
                $classRoomCurriculum['more_than_80_per'] = number_format($classRoomCurriculum['marks']->where('total_mark', '>', 80)->count() / $classRoomCurriculum['count'] * 100, 2);
            }

            $classRoomCurriculumsStatistics[$key] = $classRoomCurriculum;
        }

        // calculate the course statistics
        $courseStatistics['count'] = $courseStatistics->count();
        $courseStatistics['average'] = number_format($courseStatistics->avg('total_mark') ?? 0, 2);
        $courseStatistics['less_than_70_per'] = number_format($courseStatistics->where('total_mark', '<', 70)->count() / $courseStatistics['count'] * 100, 2);
        $courseStatistics['more_than_70_per'] = number_format($courseStatistics->where('total_mark', '>', 70)->count() / $courseStatistics['count'] * 100, 2);
        $courseStatistics['more_than_80_per'] = number_format($courseStatistics->where('total_mark', '>', 80)->count() / $courseStatistics['count'] * 100, 2);

        // prepare the return data
        $return['classRoomCurriculumsStatistics'] = $classRoomCurriculumsStatistics;
        $return['courseStatistics'] = $courseStatistics;

        return $return;
    }
}
