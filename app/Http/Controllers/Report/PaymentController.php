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
class PaymentController extends Controller
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
        return view('reports.payment.' . Str::snake($view), $data);
    }



    /**
     * Title: 
     *
     * @return view
     */
    public function paymentPrint_(Request $request)
    {

        $return['_'] = '';
        $return['payment'] = Payment::findOrFail($request->payment_id);

        return $return;
    }
}
