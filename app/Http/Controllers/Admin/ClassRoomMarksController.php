<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Curriculum;
use App\Models\Student;
use App\Models\StudentMarks;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/**
 * Class StudentMarksCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ClassRoomMarksController extends Controller
{



    public function addMarksByClass($id)
    {

        // TODO: list students and 
        $classRoom = ClassRoom::find($id);

        $classMarks = array();

        foreach ($classRoom->curriculums as $curriculum) {
            $table = array();
            $marks_template = $curriculum['curriculum']->marks_labels_flat;

            //header 
            $table[] = [
                'id' => 'id',
                'name' => __('student.student_name'),
                ...$marks_template
            ];

            //body
            foreach ($classRoom->students as $student) {

                $marks_array = $this->findStudentMarks($classRoom->course_id, $curriculum['curriculum'], $student);

                $table[] = [
                    'id' => $student->id,
                    'name' => $student->student_name,
                    ...$marks_array
                ];
            }

            $colHeaders = array_shift($table);

            //columns
            $columns = array();
            foreach ($colHeaders as $key => $value) {
                $columns[] = [
                    'data' => $key,
                    'type' => 'numeric',

                ];
            }
            $columns[0]['readOnly'] = true;
            $columns[1]['readOnly'] = true;
            $columns[1]['type'] = 'text';

            $classMarks[] = [
                'curriculum' => $curriculum['curriculum'],
                'colHeaders' => $colHeaders,
                'columns' => $columns,
                'table' => $table,
            ];
        }


        $data['classRoom'] = $classRoom;
        $data['classMarks'] = $classMarks;

        return view('marks.add_marks_by_class', $data);
    }


    private function findStudentMarks($course_id, Curriculum $curriculum, Student $student)
    {

        $marks = StudentMarks::where([
            'student_id' => $student->id,
            'course_id' => $course_id
        ])
            ->where('marks', 'like', "%curriculumـid_:_{$curriculum->id}_,%")
            ->first();

        if (!$marks) {
            return array_fill(0, count($curriculum->marks_labels_flat), '');
        }

        $marksCollection = new Collection($marks->marks);
        $StudentCurriculumMarks = $marksCollection->where('curriculumـid', $curriculum->id)->first();

        if (!$StudentCurriculumMarks) {
            return array_fill(0, count($curriculum->marks_labels_flat), '');
        }


        // map marks 
        $result = [];
        foreach ($curriculum->marks_labels as $key => $value_part) {
            if (is_array($value_part))
                foreach ($value_part as $key_part => $value) {
                    // dd($StudentCurriculumMarks->{$key}[$key_part]->mark);
                    if (isset($StudentCurriculumMarks->{$key}[$key_part]->mark)) {
                        $result[]  =  $StudentCurriculumMarks->{$key}[$key_part]->mark;
                    } else {
                        $result[]  = '';
                    }
                }
        }

        return $result;
    }




    public function saveMarksByClassJson(Request $request)
    {
        $course_id = $request->input('course_id');
        $curriculum_id = $request->input('curriculum_id');
        $data = $request->input('data');

        $curriculum = Curriculum::find($curriculum_id);

        foreach ($data as $key => $newMarks) {


            $marks = StudentMarks::where([
                'student_id' => $newMarks['id'],
                'course_id' => $course_id
            ])
                ->where('marks', 'like', "%curriculumـid_:_{$curriculum_id}_,%")
                ->first();

            if (!$marks) {
                $this->addStudentMarks($newMarks, $course_id, $curriculum);
            } else {
                $this->replaceStudentMarks($marks, $newMarks, $course_id, $curriculum);
            }

            dd($newMarks, $curriculum->marks_labels, $marks->marks, json_encode($marks->marks));
        }



        return Response([
            'curriculum_id' => $request->input('curriculum_id'),
            'course_id' => $request->input('course_id'),
            'data' => $request->input('data'),
        ]);
    }

    private function replaceStudentMarks($marks, $newMarks, $course_id, $curriculum)
    {
        $newMarksResult = $curriculum->marks_labels;
        $newMarksResult['curriculumـid'] = $curriculum->id;
        $newMarksResult['total_mark'] = 0;
        $newMarksResult['final_grade'] = "";


        $counter = 0;
        foreach ($newMarksResult as  $valueArray) {
            if (is_array($valueArray)) {
                foreach ($valueArray as $k => $value) {
                    $value->mark = $newMarks[$counter++];

                    $newMarksResult['total_mark'] += $value->mark;
                    if ($newMarksResult['total_mark'] > 50) {
                        $newMarksResult['final_grade'] = "PASS";
                    }
                }
            }
        }

        $old_marks = $marks->marks;

        // remove old marks
        foreach ($old_marks as $key => $old_mark_single_curriculum) {
            if ($old_mark_single_curriculum->curriculumـid == $curriculum->id) {
                unset($old_marks[$key]);
            }
        }
        array_push($old_marks, (object)$newMarksResult);


        dd($old_marks);

        $marks->marks = json_encode($old_marks);
        $marks->save();

        dd((object)$newMarksResult, $marks);

        dd($newMarks, $newMarksResult,  $curriculum->marks_labels, $marks->marks, json_encode($marks->marks));

        dd(1);
    }

    private function addStudentMarks($newMarks, $course_id, $curriculum_id)
    {

        dd('addStudentMarks');
    }
}
