<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;

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
                $table[] = [
                    'id' => $student->id,
                    'name' => $student->student_name,
                    ...array_fill(0, count($marks_template), '')
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

    public function saveMarksByClass()
    {
        // TODO: respone to json save 
        dd('save_12312aa');
    }
}
