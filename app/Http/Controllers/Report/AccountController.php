<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Account\CoursePaymentStatus;
use Backpack\Settings\app\Models\Setting;
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
        return view('backend.reports.account.' . Str::snake($view), $data);
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


    /** Students with at least one payment in the course (paid and exempt ones included, flagged). */
    public function paidStudentsReport_(Request $request)
    {
        return $this->studentsByPaymentStatus($request, 'paid');
    }


    /** Students with an exemption ticket and no payment. */
    public function exemptStudentsReport_(Request $request)
    {
        return $this->studentsByPaymentStatus($request, 'exempt');
    }


    /** Students with no payment and no exemption ticket. */
    public function listOfNonPayingStudentsReport_(Request $request)
    {
        return $this->studentsByPaymentStatus($request, 'unpaid');
    }


    /** Students who paid although they hold an exemption ticket. */
    public function paidAndExemptStudentsReport_(Request $request)
    {
        return $this->studentsByPaymentStatus($request, 'paidAndExempt');
    }


    /** @param  string  $group  CoursePaymentStatus method: paid, exempt, unpaid or paidAndExempt */
    protected function studentsByPaymentStatus(Request $request, $group)
    {
        $request->validate(['course' => 'required|integer']);

        $course = Course::findOrFail($request->input('course'));
        $status = new CoursePaymentStatus($course);

        return [
            'course' => $course,
            'status' => $status,
            'totals' => $status->totals(),
            'students' => $status->{$group}(),
        ];
    }
}
