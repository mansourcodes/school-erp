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
class StatisticController extends Controller
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
        return view('reports.statistic.' . Str::snake($view), $data);
    }





    /**
     * Title: 
     *
     * @return view
     */
    public function classesAndStudentsStatisticsForEachLevel_(Request $request)
    {
        $return['_'] = '';
        $course = Course::find($request->course);


        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function studentDetectionStatisticsInClasses_(Request $request)
    {
        $return['_'] = '';

        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function statisticsForTheNumberOfStudentsInClasses_(Request $request)
    {
        $return['_'] = '';

        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function studyGroupDataDisclosureStatistics_(Request $request)
    {
        $return['_'] = '';

        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function studentListStatistics_(Request $request)
    {
        $return['_'] = '';

        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function studentListStatisticsForEachGrade_(Request $request)
    {
        $return['_'] = '';

        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function statisticsOfStudentsScoresAccordingToGrades_(Request $request)
    {
        $return['_'] = '';

        return $return;
    }


    /**
     * Title: 
     *
     * @return view
     */
    public function classAverageScoreStatistics_(Request $request)
    {
        $return['_'] = '';

        return $return;
    }
}
