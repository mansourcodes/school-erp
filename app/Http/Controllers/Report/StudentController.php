<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Account\Payment;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Student;
use App\Models\StudentMarks;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

/**
 * Class StudentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StudentController extends Controller
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
        } elseif ($print == 'redirect') {
            return redirect($data['url']);
        }

        $data['print'] = 'print';
        $data['title'] = trans('reports.' . Str::of(substr($function, 0, -1))->snake());
        return view('reports.student.' . Str::snake($view), $data);
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function updateStudentsInfo_(Request $request)
    {
        $return['_'] = '';
        $course = Course::find($request->course);
        $return['classRooms'] = $course->classRooms;


        return $return;
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function studentCards_(Request $request)
    {
        $return['_'] = '';
        $course = Course::find($request->course);
        $return['classRooms'] = $course->classRooms;
        return $return;
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function studentTables_(Request $request)
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
    public function singleStudentTable(Request $request)
    {
        $student_id = $request->student;

        $course = Course::findOrFail($request->course);
        $student = Student::findOrFail($request->student);
        $classRooms = ClassRoom::where('course_id', $request->course)->whereHas('students', function ($q) use ($student_id) {
            $q->where('student_id', $student_id);
        })->get();



        $data['course'] = $course;
        $data['student'] = $student;
        $data['classRooms'] = $classRooms;


        $data['weekDays'] = [
            7 =>  trans('base.Sunday'),
            1 => trans('base.Monday'),
            2 => trans('base.Tuesday'),
            3 => trans('base.Wednesday'),
            4 => trans('base.Thursday'),
            5 => trans('base.Friday'),
            6 => trans('base.Saturday'),
        ];

        $data['print'] = 'print';
        return view('reports.student.single_student_table', $data);
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function singleUpdateStudentsInfo(Request $request)
    {
        $student_id = $request->student;

        $course = Course::findOrFail($request->course);
        $student = Student::findOrFail($request->student);
        $classRooms = ClassRoom::where('course_id', $request->course)->whereHas('students', function ($q) use ($student_id) {
            $q->where('student_id', $student_id);
        })->get();



        $data['course'] = $course;
        $data['student'] = $student;
        $data['classRooms'] = $classRooms;


        $data['weekDays'] = [
            7 =>  trans('base.Sunday'),
            1 => trans('base.Monday'),
            2 => trans('base.Tuesday'),
            3 => trans('base.Wednesday'),
            4 => trans('base.Thursday'),
            5 => trans('base.Friday'),
            6 => trans('base.Saturday'),
        ];

        $data['print'] = 'print';
        return view('reports.student.single_update_student_info', $data);
    }


    public function transcript_(Request $request)
    {
        $course = Course::findOrFail($request->course);
        $student = Student::findOrFail($request->student);

        // find $student_marks 
        $student_marks = StudentMarks::where('student_id', $student->id)->where('course_id', $course->id)->first();

        if (!$student_marks) {
            abort(404, __('localize.student_marks_not_found'));
        }

        //redirect to 
        $url = backpack_url('reports?view=' . $request->view . '&studentmarks=' . $student_marks->id);
        return [
            'url' => $url
        ];
    }

    public function studentEduStatement_(Request $request)
    {
        return $this->transcript_($request);
    }
    public function studentCoursesTranscript_(Request $request)
    {
        return $this->transcript_($request);
    }
}
