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
                'student_id' => 'student_id',
                'name' => __('student.name'),
                ...$marks_template
            ];

            //body
            foreach ($classRoom->students as $student) {

                $marks_array = $this->_findStudentMarks($classRoom->course_id, $curriculum['curriculum'], $student);

                $table[] = [
                    'student_id' => $student->id,
                    'name' => $student->name,
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


    private function _findStudentMarks($course_id, Curriculum $curriculum, Student $student)
    {

        $studentMarksObject = StudentMarks::where([
            'student_id' => $student->id,
            'course_id' => $course_id
        ])
            ->where('marks', 'like', "%curriculum_id_:_{$curriculum->id}_,%")
            ->first();

        if (!$studentMarksObject) {
            return array_fill(0, count($curriculum->marks_labels_flat), '');
        }
        $marksCollection = new Collection($studentMarksObject->marks);
        $StudentCurriculumMarks = $marksCollection->where('curriculum_id', $curriculum->id)->first();

        if (!$StudentCurriculumMarks) {
            return array_fill(0, count($curriculum->marks_labels_flat), '');
        }



        // map marks 
        $result = [];
        foreach ($curriculum->marks_labels as $key => $value_part) {
            if (is_array($value_part))
                foreach ($value_part as $key_part => $value) {

                    if (isset($StudentCurriculumMarks[$key][$key_part]->mark)) {
                        $result[]  =  $StudentCurriculumMarks[$key][$key_part]->mark;
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

        foreach ($data as $key => $new_marks_data) {


            $studentMarksObject = StudentMarks::where([
                'student_id' => $new_marks_data['student_id'],
                'course_id' => $course_id
            ])
                ->orWhere([
                    ['marks', 'like', "%\"curriculum_id\":_{$curriculum_id}_,%"],
                    ['marks', 'like', "%\"curriculum_id\":{$curriculum_id},%"],
                ])
                ->first();

            $this->_addStudentMarks($new_marks_data, $course_id, $curriculum, $studentMarksObject);
        }

        return Response([
            'curriculum_id' => $request->input('curriculum_id'),
            'course_id' => $request->input('course_id'),
            'data' => $request->input('data'),
        ]);
    }

    private function _addStudentMarks($new_marks_data, $course_id, Curriculum $curriculum, StudentMarks $studentMarksObject = null)
    {
        $newMarksResult = $curriculum->marks_labels;
        $newMarksResult['curriculum_id'] = $curriculum->id;
        $newMarksResult['total_mark'] = 0;
        $newMarksResult['final_grade'] = "";


        // create new marks array from by merge the template with new data
        $counter = 0;
        foreach ($newMarksResult as $key => $valueArray) {
            if (is_array($valueArray) && StudentMarks::$standard_marks_composer[$key] == 'Single') {
                foreach ($valueArray as $k => $value) {
                    $value->mark = $new_marks_data[$counter++];
                    $newMarksResult['total_mark'] += $value->mark;


                    if ($newMarksResult['total_mark'] >= 90) {
                        $newMarksResult['final_grade'] = "ممتاز";
                    } elseif ($newMarksResult['total_mark'] >= 80) {
                        $newMarksResult['final_grade'] = "جيد جدا";
                    } elseif ($newMarksResult['total_mark'] >= 70) {
                        $newMarksResult['final_grade'] = "جيد";
                    } else {
                        $newMarksResult['final_grade'] = "غير مجتاز";
                    }
                }
            }
        }

        if (is_null($studentMarksObject)) {
            $studentMarksObject = new StudentMarks();
            $studentMarksObject->student_id = $new_marks_data['student_id'];
            $studentMarksObject->course_id = $course_id;
        }


        $studentMarksObject->total_mark = $newMarksResult['total_mark'];
        $studentMarksObject->final_grade = $newMarksResult['final_grade'];
        foreach ($newMarksResult as $key => $value) {
            $newMarksResult[$key] = json_encode($value);
        }
        $studentMarksObject->marks = json_encode([$newMarksResult]);
        $studentMarksObject->save();
    }
}
