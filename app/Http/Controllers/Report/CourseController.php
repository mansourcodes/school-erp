<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Backpack\Settings\app\Models\Setting;
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

        $print = $request->input('print');
        if ($print == 'pdf') {
            $data['print'] = 'pdf';
            // $pdf = PDF::loadView('reports.' . $view, $data);
            // return $pdf->stream();
        }

        $data['print'] = 'print';
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
}
