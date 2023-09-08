<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Backpack\Settings\app\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Class 
 */
class CourseController extends Controller
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

        if (gettype($data) == 'object') {
            return $data;
        }

        $print = $request->input('print');
        if ($print == 'pdf') {
            $data['print'] = 'pdf';
            // $pdf = PDF::loadView('reports.' . $view, $data);
            // return $pdf->stream();
        }

        $data['print'] = 'print';
        $data['title'] = trans('reports.' . Str::of(substr($function, 0, -1))->snake());
        return view('reports.course.' . Str::snake($view), $data);
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function attendanceMarksTemplate_(Request $request)
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


        // dd(Setting::get('attendance_marks_template.title'));

        return $return;
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function rememberMarksTemplate_(Request $request)
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

        // dd(Setting::get('attendance_marks_template.title'));

        return $return;
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function monthlyExamsTemplate_(Request $request)
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


        // dd(Setting::get('attendance_marks_template.title'));

        return $return;
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function classroomList_(Request $request)
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


        // dd(Setting::get('attendance_marks_template.title'));

        return $return;
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function attendSheetTemplate_(Request $request)
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


        // dd(Setting::get('attendance_marks_template.title'));

        return $return;
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function teacherTable_(Request $request)
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


        // dd(Setting::get('attendance_marks_template.title'));

        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function parentMeetingTemplate_(Request $request)
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


        // dd(Setting::get('attendance_marks_template.title'));

        return $return;
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function registrationForm_(Request $request)
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


        // dd(Setting::get('attendance_marks_template.title'));

        return $return;
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function studentsMarks_(Request $request)
    {
        $return['_'] = '';
        // dd('reports?view=transcript&course=' . $request->course);
        return redirect('/admin/reports?view=transcript&course=' . $request->course)->with('status', 'Profile updated!');
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function teacherFileLabel_(Request $request)
    {
        $return['_'] = '';
        $course = Course::find($request->course);

        foreach ($course->classRooms as  $classRoom) {
            foreach ($classRoom->curriculums as  $curriculum) {
                $key = $classRoom->id . '_' . $curriculum['id'];
                $sessions[$key]['class_room_number'] =  $classRoom->class_room_number;
                $sessions[$key]['curriculum'] =  $curriculum;
                $sessions[$key]['long_name'] =  $classRoom->long_name[$curriculum['id']];

                if (!empty($curriculum['attend_table'])) {
                    $sessions[$key]['orderKey'] =  array_shift($curriculum['attend_table']);
                } else {
                    $sessions[$key]['orderKey'] =  "empty";
                }
            }
        }

        $sessions = new Collection($sessions);
        $sessions = $sessions->sortBy('orderKey')->groupBy('orderKey');
        $return['sessions'] = $sessions;

        $return['weekDays'] = [
            7 =>  trans('base.Sunday'),
            1 => trans('base.Monday'),
            2 => trans('base.Tuesday'),
            3 => trans('base.Wednesday'),
            4 => trans('base.Thursday'),
            5 => trans('base.Friday'),
            6 => trans('base.Saturday'),
        ];


        // dd(Setting::get('attendance_marks_template.title'));

        return $return;
    }
}
