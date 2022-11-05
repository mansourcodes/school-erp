<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;

/**
 * Class StudentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StudentController extends Controller
{

    /**
     * Title: 
     *
     * @return view
     */
    protected function updateStudentsInfo()
    {

        return view('reports.student.update_students_info');
    }
}
