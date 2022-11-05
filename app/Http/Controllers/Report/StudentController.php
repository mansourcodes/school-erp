<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
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
        }

        $data['print'] = 'print';
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

        return $return;
    }
}
