<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Account\Payment;
use App\Models\Course;
use App\Models\Student;
use Backpack\Settings\app\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

/**
 * Class 
 */
class AccountController extends Controller
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
        return view('reports.account.' . Str::snake($view), $data);
    }



    /**
     * Title: 
     *
     * @return view
     */
    public function balanceStatementReport_(Request $request)
    {

        $return['_'] = '';
        $course = Course::find($request->course);
        $payments = $course->payments;

        $payment_types = Setting::where('key', 'payment_types')->first();
        $payment_types_array = Arr::pluck(json_decode($payment_types->value), 'key');


        $payment_filtered = [];
        foreach ($payment_types_array as $key => $type) {
            $payment_filtered[$type] = $payments->filter(function ($p, $key) use ($type) {
                return $p->type == $type;
            });
        }


        // dd($return['payments'][0]->student);


        $return['payments'] = $payments;
        $return['payment_filtered'] = $payment_filtered;
        $return['course'] = $course;

        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function detectingHelpersReport_(Request $request)
    {
        $return['_'] = '';
        $course = Course::find($request->course);
        $payment_filtered = $course->payments->filter(function ($p, $key) {
            return $p->type == 'FREE';
        });

        // dd(
        //     $payment_filtered->first()->classRoom->implode('class_room_name', ', ')
        // );

        $return['payments'] = $payment_filtered;
        $return['course'] = $course;

        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function listOfAssistanceStudentsWhoParticipatedInThePaymentReport_(Request $request)
    {

        $return['_'] = '';
        $course = Course::find($request->course);
        $return['course'] = $course;
        $course_students = $course->classRooms->pluck('students');
        $in_classroom_students = Arr::flatten($course_students->pluck('*'));
        $students = new Collection($in_classroom_students);

        $students = $students->filter(function ($value, int $key) {
            return $value->financial_support_status != 'NONE';
        });

        // $return['students'] = $students;


        //-------
        $support_students_ids = Arr::flatten($students->pluck('id')->toArray());
        $payments = Payment::where('course_id', $course->id)->whereIn('student_id', $support_students_ids)->get();

        $return['payments_paid'] = $payments;


        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function listOfUnconfirmedStudentsReport_(Request $request)
    {


        $return['_'] = '';
        $course = Course::find($request->course);
        $course_students = $course->classRooms->pluck('students');
        $in_classroom_students_id = Arr::flatten($course_students->pluck('*.id')->toArray());


        $confirmed_students_id = $course->payments->where('type', 'CONFIRM')->pluck('student_id')->toArray();
        $paid_students_id = $course->payments->where('type', '!=', 'CONFIRM')->pluck('student_id')->toArray();

        $unconfirmed_students_id = [];
        foreach ($in_classroom_students_id as $id) {
            if (in_array($id, $confirmed_students_id) || in_array($id, $paid_students_id)) {
                // comfirmed student
            } else {
                // unconfirmed 
                $unconfirmed_students_id[] = $id;
            }
        }



        $return['students'] = Student::whereIn('id', $unconfirmed_students_id)->get();
        $return['course'] = $course;

        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function listOfNonPayingStudentsReport_(Request $request)
    {


        $return['_'] = '';
        $course = Course::find($request->course);
        $course_students = $course->classRooms->pluck('students');
        $in_classroom_students_id = Arr::flatten($course_students->pluck('*.id')->toArray());

        // supported students 
        $course_students_one_array = Arr::flatten($course_students->pluck('*'));
        $course_students_collection = new Collection($course_students_one_array);
        $support_students = $course_students_collection->filter(function ($value, int $key) {
            return $value->financial_support_status != 'NONE';
        });
        $support_students_ids = Arr::flatten($support_students->pluck('id')->toArray());

        $paid_students_id = $course->payments->where('type', '!=', 'CONFIRM')->pluck('student_id')->toArray();

        $unpaid_students_id = [];
        foreach ($in_classroom_students_id as $id) {
            if (in_array($id, $paid_students_id)) {
                // paid student
            } elseif (in_array($id, $support_students_ids)) {
                // spported student
            } else {
                // unpaid 
                $unpaid_students_id[] = $id;
            }
        }


        $return['students'] = Student::whereIn('id', $unpaid_students_id)->get();
        $return['course'] = $course;

        return $return;
    }
}
