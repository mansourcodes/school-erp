<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
    public function balanceStatementReport_()
    {

        return view();
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function detectingHelpersReport_()
    {

        return view();
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function listOfAssistanceStudentsWhoParticipatedInThePaymentReport_()
    {

        return view();
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function listOfUnconfirmedStudentsReport_()
    {

        return view();
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function listOfNonPayingStudentsReport_()
    {

        return view();
    }
}
