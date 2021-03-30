<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\FormBuilderHelper;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\ReportsSettings;
use App\Models\Curriculum;
use App\Models\StudentMarks;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use PDF;
use stdClass;

class AcademiaReportsController extends Controller
{



    public function print(Request $request)
    {

        $view = $request->input('view');
        $function =  Str::camel('report_' . $view);
        $data = $this->{$function}($request);

        $data['settings'] = ReportsSettings::where('key', 'like', $view . '.%')->get()->keyBy('key');

        $print = $request->input('print');
        if ($print == 'pdf') {
            $data['print'] = 'pdf';
            // $pdf = PDF::loadView('reports.' . $view, $data);
            // return $pdf->stream();
        }

        $data['print'] = 'print';
        return view('reports.' . $view, $data);
    }


    private function reportTranscript(Request $request)
    {

        $studentmarks_id = $request->input('studentmarks');
        $classroom_id = $request->input('classroom');
        $course_id = $request->input('course');
        $counter = 0;
        if ($studentmarks_id) {

            $data['studentmarks'][$counter] = StudentMarks::find($studentmarks_id);
            if ($data['studentmarks'][$counter]->marks)
                foreach ($data['studentmarks'][$counter]->marks as $mark_key => $mark) {
                    if (!isset($data['curriculums'][(int)$mark->curriculumـid])) {
                        $data['curriculums'][(int)$mark->curriculumـid]  = Curriculum::find((int)$mark->curriculumـid);
                    }
                }
        } elseif ($classroom_id) {


            $classroom = ClassRoom::find($classroom_id);

            $student_ids_array = $classroom->students->pluck('id')->toArray();
            $data['studentmarks'] = StudentMarks::whereIn('student_id', $student_ids_array)->where('course_id', $classroom->course->id)->get();

            $data['curriculums'] = [];

            foreach ($data['studentmarks'] as $studentmark) {
                if ($studentmark->marks === null) {
                    $studentmark->marks = [];
                }

                foreach ($studentmark->marks as $mark_key => $mark) {
                    if (!isset($data['curriculums'][(int)$mark->curriculumـid])) {
                        $data['curriculums'][(int)$mark->curriculumـid]  = Curriculum::find((int)$mark->curriculumـid);
                    }
                }
            }
        } elseif ($course_id) {

            $course = Course::find($course_id);


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
                        if (!isset($data['curriculums'][(int)$mark->curriculumـid])) {
                            $data['curriculums'][(int)$mark->curriculumـid]  = Curriculum::find((int)$mark->curriculumـid);
                        }
                    }
                    $data['studentmarks'][] = $studentmark;
                }
            }
        }


        return $data;
    }



    function reportStudentEduStatement(Request $request)
    {

        $studentmarks_id = $request->input('studentmarks');
        $classroom_id = $request->input('classroom');
        // $course_id = $request->input('course');
        $counter = 0;
        if ($studentmarks_id) {

            $data['studentmarks'][0] = StudentMarks::find($studentmarks_id);
            foreach ($data['studentmarks'][0]->marks as $mark_key => $mark) {
                if (!isset($data['curriculums'][(int)$mark->curriculumـid])) {
                    $data['curriculums'][(int)$mark->curriculumـid]  = Curriculum::find((int)$mark->curriculumـid);
                }
            }

            $classroom = ClassRoom::whereHas('students', function (Builder $query) use ($data) {
                $query->where('id', $data['studentmarks'][0]->student->id);
            })
                ->where('course_id', $data['studentmarks'][0]->course->id)->first();

            foreach ($classroom->teachers as $teacher) {
                # code...
                if (!isset($data['curriculums'][$teacher['curriculumـid']])) {
                    $data['curriculums'][$teacher['curriculumـid']] = new stdClass();
                }
                $data['curriculums'][$teacher['curriculumـid']]->teacher_name = $teacher['teacher_name'];
            }
        } elseif ($classroom_id) {


            $classroom = ClassRoom::find($classroom_id);

            $student_ids_array = $classroom->students->pluck('id')->toArray();
            $data['studentmarks'] = StudentMarks::whereIn('student_id', $student_ids_array)->where('course_id', $classroom->course->id)->get();

            $data['curriculums'] = [];

            foreach ($data['studentmarks'] as $studentmark) {
                if ($studentmark->marks === null) {
                    $studentmark->marks = [];
                }

                foreach ($studentmark->marks as $mark_key => $mark) {
                    if (!isset($data['curriculums'][(int)$mark->curriculumـid])) {
                        $data['curriculums'][(int)$mark->curriculumـid]  = Curriculum::find((int)$mark->curriculumـid);
                    }
                }
            }

            foreach ($classroom->teachers as $teacher) {
                # code...
                if (!isset($data['curriculums'][$teacher['curriculumـid']])) {
                    $data['curriculums'][$teacher['curriculumـid']] = new stdClass();
                }
                $data['curriculums'][$teacher['curriculumـid']]->teacher_name = $teacher['teacher_name'];
            }
        }


        foreach ($data['studentmarks'] as $studentmarks) {
            # code...

            foreach ($classroom->teachers as $teacher) {
                # code...
                if (!isset($data['curriculums'][$teacher['curriculumـid']])) {
                    $data['curriculums'][$teacher['curriculumـid']] = new stdClass();
                }
                $data['curriculums'][$teacher['curriculumـid']]->teacher_name = $teacher['teacher_name'];
            }
        }

        // dd($data['curriculums']);

        return $data;
    }


    function reportStudentCoursesTranscript(Request $request)
    {


        $studentmarks_id = $request->input('studentmarks');
        $currentMark = StudentMarks::find($studentmarks_id);

        $root_course = $currentMark->course->id;
        if ($currentMark->course->course_root_id) {
            $root_course = $currentMark->course->course_root_id;
        }

        $courses_list = Course::where('course_root_id', $root_course)->orWhere('id', $root_course)->get();
        foreach ($courses_list as $course) {
            $data['course_id'][$course->id]['course'] = $course;
        }

        $path_courses =  Course::where('course_root_id', $root_course)->orWhere('id', $root_course)->pluck('id')->toArray();

        $studentmarks_list = StudentMarks::where('student_id', $currentMark->student_id)
            ->whereIn('course_id', $path_courses)->get();


        foreach ($studentmarks_list as  $marksRequred) {

            $data['course_id'][$marksRequred->course_id]['studentmarks'] = $marksRequred;


            if ($marksRequred->marks) {
                foreach ($marksRequred->marks as $mark_key => $mark) {
                    if (!isset($data['course_id'][$marksRequred->course_id]['curriculums'][(int)$mark->curriculumـid])) {
                        $data['course_id'][$marksRequred->course_id]['curriculums'][(int)$mark->curriculumـid]  = Curriculum::find((int)$mark->curriculumـid);
                    }
                }
            } else {
                $marksRequred->marks = [];
            }



            $classroom = ClassRoom::whereHas('students', function (Builder $query) use ($marksRequred) {
                $query->where('id', $marksRequred->student->id);
            })->where('course_id', $marksRequred->course->id)->first();

            foreach ($classroom->teachers as $teacher) {
                # code...
                if (!isset($data['course_id'][$marksRequred->course_id]['curriculums'][$teacher['curriculumـid']])) {
                    $data['course_id'][$marksRequred->course_id]['curriculums'][$teacher['curriculumـid']] = new stdClass();
                }
                $data['course_id'][$marksRequred->course_id]['curriculums'][$teacher['curriculumـid']]->teacher_name = $teacher['teacher_name'];
            }
        }



        // echo $data['course_id']['299']['studentmarks']->student->student_name;
        // dd($data);

        return $data;
    }


    function reportScoringSheet(Request $request)
    {

        $classroom_id = $request->input('classroom');
        $classroom = ClassRoom::find($classroom_id);



        $student_ids_array = $classroom->students->pluck('id')->toArray();
        $studentmarks = StudentMarks::whereIn('student_id', $student_ids_array)->where('course_id', $classroom->course->id)->get();

        $marks = [];
        foreach ($studentmarks as $studentmark) {
            if ($studentmark->marks === null) {
                $studentmark->marks = [];
            }

            foreach ($studentmark->marks as $mark_key => $mark) {
                $marks[(int)$mark->curriculumـid][(int)$studentmark->student_id] = $mark;
            }
        }

        // dd($marks);


        $data['classroom'] = $classroom;
        $data['marks'] = $marks;
        return $data;
    }
}
