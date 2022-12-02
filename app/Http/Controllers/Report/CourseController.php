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
    public function tmp_(Request $request)
    {
        $return['_'] = '';
        $course = Course::find($request->course);
        $return['classRooms'] = $course->classRooms;
        return $return;
    }
}
