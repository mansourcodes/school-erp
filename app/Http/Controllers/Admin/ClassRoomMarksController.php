<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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

        dd($id);
        return view('marks.add_marks_by_class');
    }

    public function saveMarksByClass()
    {
        // TODO: respone to json save 
        dd('save_12312aa');
    }
}
