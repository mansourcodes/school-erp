<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HtmlHelper;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Attends;
use App\Models\ClassRoom;
use App\Models\Course;
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


        $print = $request->input('print');
        if ($print == 'pdf') {
            $data['print'] = 'pdf';
            // $pdf = PDF::loadView('reports.' . $view, $data);
            // return $pdf->stream();
        }

        $data['print'] = 'print';
        return view('backend.reports.' . $view, $data);
    }


    private function reportTranscript(Request $request)
    {

        $studentmarks_id = $request->input('studentmarks');
        $classroom_id = $request->input('classroom');
        $course_id = $request->input('course');
        $counter = 0;
        if ($studentmarks_id) {

            $data['studentmarks'][$counter] = StudentMarks::find($studentmarks_id);

            if ($data['studentmarks'][$counter]->marks) {
                foreach ($data['studentmarks'][$counter]->marks as $mark_key => $mark) {
                    if (!isset($data['curriculums'][(int)$mark['curriculum_id']])) {
                        $data['curriculums'][(int)$mark['curriculum_id']]  = Curriculum::find((int)$mark['curriculum_id']);
                    }
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
                    if (!isset($data['curriculums'][(int)$mark['curriculum_id']])) {
                        $data['curriculums'][(int)$mark['curriculum_id']]  = Curriculum::find((int)$mark['curriculum_id']);
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
                        if (!isset($data['curriculums'][(int)$mark['curriculum_id']])) {
                            $data['curriculums'][(int)$mark['curriculum_id']]  = Curriculum::find((int)$mark['curriculum_id']);
                        }
                    }
                    $data['studentmarks'][] = $studentmark;
                }
            }
        }


        return $data;
    }


    private function reportCertificate(Request $request)
    {

        $studentmarks_id = $request->input('studentmarks');
        $classroom_id = $request->input('classroom');
        $course_id = $request->input('course');
        $counter = 0;
        if ($studentmarks_id) {

            $data['studentmarks'][$counter] = StudentMarks::find($studentmarks_id);

            if ($data['studentmarks'][$counter]->marks) {
                foreach ($data['studentmarks'][$counter]->marks as $mark_key => $mark) {
                    if (!isset($data['curriculums'][(int)$mark['curriculum_id']])) {
                        $data['curriculums'][(int)$mark['curriculum_id']]  = Curriculum::find((int)$mark['curriculum_id']);
                    }
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
                    if (!isset($data['curriculums'][(int)$mark['curriculum_id']])) {
                        $data['curriculums'][(int)$mark['curriculum_id']]  = Curriculum::find((int)$mark['curriculum_id']);
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
                        if (!isset($data['curriculums'][(int)$mark['curriculum_id']])) {
                            $data['curriculums'][(int)$mark['curriculum_id']]  = Curriculum::find((int)$mark['curriculum_id']);
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
                if (!isset($data['curriculums'][(int)$mark['curriculum_id']])) {
                    $data['curriculums'][(int)$mark['curriculum_id']]  = Curriculum::find((int)$mark['curriculum_id']);
                }
            }

            $classroom = ClassRoom::whereHas('students', function (Builder $query) use ($data) {
                $query->where('id', $data['studentmarks'][0]->student->id);
            })
                ->where('course_id', $data['studentmarks'][0]->course->id)->first();

            if (isset($classroom->teachers)) {
                foreach ($classroom->teachers as $teacher) {
                    # code...
                    if (!isset($data['curriculums'][$teacher['curriculum_id']])) {
                        $data['curriculums'][$teacher['curriculum_id']] = new stdClass();
                    }
                    $data['curriculums'][$teacher['curriculum_id']]->teacher_name = $teacher['teacher_name'];
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
                    if (!isset($data['curriculums'][(int)$mark['curriculum_id']])) {
                        $data['curriculums'][(int)$mark['curriculum_id']]  = Curriculum::find((int)$mark['curriculum_id']);
                    }
                }
            }
            if (isset($classroom->teachers)) {

                foreach ($classroom->teachers as $teacher) {
                    # code...
                    if (!isset($data['curriculums'][$teacher['curriculum_id']])) {
                        $data['curriculums'][$teacher['curriculum_id']] = new stdClass();
                    }
                    $data['curriculums'][$teacher['curriculum_id']]->teacher_name = $teacher['teacher_name'];
                }
            }
        }


        foreach ($data['studentmarks'] as $studentmarks) {
            # code...
            if (isset($classroom->teachers)) {

                foreach ($classroom->teachers as $teacher) {
                    # code...
                    if (!isset($data['curriculums'][$teacher['curriculum_id']])) {
                        $data['curriculums'][$teacher['curriculum_id']] = new stdClass();
                    }
                    $data['curriculums'][$teacher['curriculum_id']]->teacher_name = $teacher['teacher_name'];
                }
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
                    if (!isset($data['course_id'][$marksRequred->course_id]['curriculums'][(int)$mark['curriculum_id']])) {
                        $data['course_id'][$marksRequred->course_id]['curriculums'][(int)$mark['curriculum_id']]  = Curriculum::find((int)$mark['curriculum_id']);
                    }
                }
            } else {
                $marksRequred->marks = [];
            }



            $classroom = ClassRoom::whereHas('students', function (Builder $query) use ($marksRequred) {
                $query->where('id', $marksRequred->student->id);
            })->where('course_id', $marksRequred->course->id)->first();
            if (isset($classroom->teachers)) {

                foreach ($classroom->teachers as $teacher) {
                    # code...
                    if (!isset($data['course_id'][$marksRequred->course_id]['curriculums'][$teacher['curriculum_id']])) {
                        $data['course_id'][$marksRequred->course_id]['curriculums'][$teacher['curriculum_id']] = new stdClass();
                    }
                    $data['course_id'][$marksRequred->course_id]['curriculums'][$teacher['curriculum_id']]->teacher_name = $teacher['teacher_name'];
                }
            }
        }



        // echo $data['course_id']['299']['studentmarks']->student->name;
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
                $marks[(int)$mark['curriculum_id']][(int)$studentmark->student_id] = $mark;
            }
        }

        // dd($marks);


        $data['classroom'] = $classroom;
        $data['marks'] = $marks;
        return $data;
    }


    function reportStudentAttendReport(Request $request)
    {
        return $this->reportStudentAttend($request);
    }

    function reportStudentAttend(Request $request)
    {

        $classroom_id = $request->input('classroom');
        $classroom = ClassRoom::find($classroom_id);


        $attends = Attends::Where('course_id', $classroom->course_id);
        $data['total_days'] = $attends->count();

        if ($data['total_days'] == 0) {
            $data['total_days'] = 1;
        }


        $student_report = [];
        foreach ($classroom->students as $student) {
            $student_id = $student->id;


            //attend
            $student_report[$student_id]['attend'] = Attends::whereHas('attendStudents',  function ($query) use ($student_id) {
                $query->whereIn('id', [$student_id]);
            })->get()->count();
            $student_report[$student_id]['attend_per'] =
                round($student_report[$student_id]['attend'] / $data['total_days'] * 100);

            //absent
            $student_report[$student_id]['absent'] = Attends::whereHas('absentStudents',  function ($query) use ($student_id) {
                $query->whereIn('id', [$student_id]);
            })->get()->count();
            $student_report[$student_id]['absent_per'] = round($student_report[$student_id]['absent'] / $data['total_days'] * 100);


            $student_report[$student_id]['absentWithExcuse'] = Attends::whereHas('absentWithExcuseStudents',  function ($query) use ($student_id) {
                $query->whereIn('id', [$student_id]);
            })->get()->count();
            $student_report[$student_id]['absentWithExcuse_per'] = round($student_report[$student_id]['absentWithExcuse'] / $data['total_days'] * 100);



            //late
            $student_report[$student_id]['late'] = Attends::whereHas('lateStudents',  function ($query) use ($student_id) {
                $query->whereIn('id', [$student_id]);
            })->get()->count();
            $student_report[$student_id]['late_per'] = round($student_report[$student_id]['late'] / $data['total_days'] * 100);


            $student_report[$student_id]['lateWithExcuse'] = Attends::whereHas('lateWithExcuseStudents',  function ($query) use ($student_id) {
                $query->whereIn('id', [$student_id]);
            })->get()->count();
            $student_report[$student_id]['lateWithExcuse_per'] = round($student_report[$student_id]['lateWithExcuse'] / $data['total_days'] * 100);
        }



        $data['student_report'] = $student_report;

        $data['classroom'] = $classroom;
        return $data;
    }

    function reportStudentAttendList(Request $request)
    {


        $return = $this->reportStudentAttend($request);
        $classroom_id = $request->input('classroom');
        $classroom = ClassRoom::find($classroom_id);

        $week_days = [
            7 =>  trans('base.Sunday'),
            1 => trans('base.Monday'),
            2 => trans('base.Tuesday'),
            3 => trans('base.Wednesday'),
            4 => trans('base.Thursday'),
            5 => trans('base.Friday'),
            6 => trans('base.Saturday'),
        ];


        $attend_table = $classroom->attend_table ?? [];
        foreach ($attend_table as $key => &$attend_curriculum_table) {
            $attend_curriculum_table['start_time'] = empty($attend_curriculum_table['start_time']) ? null : Carbon::parse($attend_curriculum_table['start_time']);
            $attend_curriculum_table['day_name'] = $week_days[$attend_curriculum_table['day']];
            $attend_curriculum_table['curriculum'] = Curriculum::find((int)$attend_curriculum_table['curriculum_id']);
            $attend_curriculum_table['calander_days'] = HtmlHelper::getDaysInRange($attend_curriculum_table['day'], $classroom->course->start_date, $classroom->course->end_date);
        }

        $teachers = [];

        foreach ($classroom['teachers'] ?? [] as $key => $teachers_object) {
            $teachers[$teachers_object['curriculum_id']] = $teachers_object['teacher_name'];
        }

        // recorded attends: [curriculum_id][date][start hour | '*' | 'other'][student_id] => state
        // matched by hour only: old records saved start_time as 'H:m' (m = month), and
        // ones the repair migration could not match to a timetable slot keep wrong minutes.
        // '*' is the fallback for slots without a start_time in the timetable (the form then
        // records the time it was opened, so the hour can't match).
        // 'other' holds records from other classrooms of the same course, for students moved
        // into this classroom; used only when this classroom has no record for the student.
        // $course_states[date][student_id] is the last fallback: the student's record in any
        // classroom of the course on that date, whatever the curriculum (classrooms of a course
        // usually teach different levels, so a moved student rarely shares the curriculum).
        // Students removed from the classroom are not rendered, so their records are ignored.
        $state_relations = [
            'attend' => 'attendStudents',
            'absent' => 'absentStudents',
            'absent_w_excuse' => 'absentWithExcuseStudents',
            'late' => 'lateStudents',
            'late_w_excuse' => 'lateWithExcuseStudents',
        ];

        $student_ids = $classroom->students->pluck('id')->all();
        $only_classroom_students = function ($query) use ($student_ids) {
            $query->whereIn('students.id', $student_ids);
        };

        $attends = Attends::where('class_room_id', $classroom->id)
            ->orWhere('course_id', $classroom->course_id)
            ->with(array_fill_keys(array_values($state_relations), $only_classroom_students))
            ->get();

        $attend_states = [];
        $course_states = [];
        foreach ($attends as $attend) {
            $date = $attend->date->format('Y-m-d');
            $hour = Carbon::parse($attend->start_time)->format('H');
            $is_own = $attend->class_room_id == $classroom->id;
            $buckets = $is_own ? [$hour, '*'] : ['other'];

            foreach ($state_relations as $state => $relation) {
                foreach ($attend->{$relation} as $student) {
                    foreach ($buckets as $bucket) {
                        $attend_states[(int)$attend->curriculum_id][$date][$bucket][$student->id] = $state;
                    }
                    if (!$is_own) {
                        $course_states[$date][$student->id] = $state;
                    }
                }
            }
        }


        $return['attend_states'] = $attend_states;
        $return['course_states'] = $course_states;
        $return['teachers'] = $teachers;
        $return['attend_table'] = $attend_table;
        $return['classroom'] = $classroom;

        return $return;
    }
}
