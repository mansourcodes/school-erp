<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Backpack\Settings\app\Models\Setting;
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

        return view();
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function listOfAssistanceStudentsWhoParticipatedInThePaymentReport_(Request $request)
    {

        return view();
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function listOfUnconfirmedStudentsReport_(Request $request)
    {

        return view();
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function listOfNonPayingStudentsReport_(Request $request)
    {

        return view();
    }
}
