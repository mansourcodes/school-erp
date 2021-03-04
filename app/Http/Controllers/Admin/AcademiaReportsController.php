<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\FormBuilderHelper;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\ReportsSettings;
use App\Models\Curriculum;
use App\Models\StudentMarks;
use Illuminate\Http\Request;
use PDF;

class AcademiaReportsController extends Controller
{
    // /**
    //  * Display a listing of the resource.
    //  *
    //  */
    // public function index()
    // {
    //     $data = [
    //         [
    //             'id' => 'transcript',
    //             'title' => 'our title',
    //             'open' => [
    //                 'route' => 'reports/transcript',
    //                 'method' => 'get',
    //             ],
    //             'fields' => [
    //                 [
    //                     'name' => 'course',
    //                     'type' => 'list',
    //                     'options' =>  [1 => 'a', 2 => 'b'],
    //                 ],
    //                 [
    //                     'name' => 'note',
    //                     'type' => 'text',
    //                 ],
    //             ],
    //         ],
    //     ];
    //     $formList = [];
    //     foreach ($data as $form) {
    //         $formList[] = new FormBuilderHelper($form);
    //     }

    //     return view('reports.index', [
    //         'formList' => $formList
    //     ]);
    // }




    public function print(Request $request)
    {

        $view = $request->input('view');
        $function = 'report' . ucfirst($view);
        $data = $this->{$function}($request);

        $data['settings'] = ReportsSettings::where('key', 'like', $view . '.%')->get()->keyBy('key');

        $print = $request->input('print');
        if ($print == 'pdf') {
            $data['print'] = 'pdf';
            $pdf = PDF::loadView('reports.' . $view, $data);
            return $pdf->stream();
        } else {
            $data['print'] = 'print';
            return view('reports.' . $view, $data);
        }
    }


    private function reportTranscript(Request $request)
    {

        $studentmarks = $request->input('studentmarks');
        $classroom = $request->input('classroom');
        $course = $request->input('course');
        $counter = 0;
        if ($studentmarks) {

            $data['studentmarks'][$counter] = StudentMarks::find($studentmarks);
            foreach ($data['studentmarks'][$counter]->marks as $mark_key => $mark) {
                if (!isset($data['curriculums'][(int)$mark['curriculumـid']])) {
                    $data['curriculums'][(int)$mark['curriculumـid']]  = Curriculum::find((int)$mark['curriculumـid']);
                }
            }
        } elseif ($classroom) {


            $classroom = ClassRoom::find($classroom);

            $student_ids_array = $classroom->students->pluck('id')->toArray();
            $data['studentmarks'] = StudentMarks::whereIn('student_id', $student_ids_array)->where('course_id', $classroom->course->id)->get();

            $data['curriculums'] = [];

            foreach ($data['studentmarks'] as $studentmark) {
                if ($studentmark->marks === null) {
                    $studentmark->marks = [];
                }

                foreach ($studentmark->marks as $mark_key => $mark) {
                    if (!isset($data['curriculums'][(int)$mark['curriculumـid']])) {
                        $data['curriculums'][(int)$mark['curriculumـid']]  = Curriculum::find((int)$mark['curriculumـid']);
                    }
                }
            }
        } elseif ($course) {

            $course = Course::find($course);


            $classroom_ids_array = $course->classRooms->pluck('id')->toArray();
            $data['classrooms'] = ClassRoom::whereIn('id', $classroom_ids_array)->get();
            $data['curriculums'] = [];
            $data['studentmarks'] = [];
            foreach ($data['classrooms'] as $classroom) {

                $student_ids_array = $classroom->students->pluck('id')->toArray();
                $studentmarks = StudentMarks::whereIn('student_id', $student_ids_array)
                    ->where(
                        'course_id',
                        $classroom->course->id
                    )->get();



                foreach ($studentmarks as $studentmark) {
                    if ($studentmark->marks === null) {
                        $studentmark->marks = [];
                    }

                    foreach ($studentmark->marks as $mark_key => $mark) {
                        if (!isset($data['curriculums'][(int)$mark['curriculumـid']])) {
                            $data['curriculums'][(int)$mark['curriculumـid']]  = Curriculum::find((int)$mark['curriculumـid']);
                        }
                    }
                    $data['studentmarks'][] = $studentmark;
                }
            }
        }

        return $data;
    }
}
