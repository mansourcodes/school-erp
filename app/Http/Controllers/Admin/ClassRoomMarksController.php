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

        return Response([
            'curriculum_id' => $request->input('curriculum_id'),
            'course_id' => $request->input('course_id'),
            'data' => $request->input('data'),
        ]);
    }
}
