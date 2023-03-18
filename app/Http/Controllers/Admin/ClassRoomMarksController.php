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
            foreach ($classRoom->students as $student) {
                $table[] = [$student->id, $student->student_name, ...$marks_template];
            }

            $classMarks[] = [
                'curriculum' => $curriculum['curriculum'],
                'table' => $table
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
