<?php

namespace App\Http\Controllers\StudentPanel;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentMarks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPanelController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function dashboard()
    {

        $student = Auth::guard('student')->user();
        $payments = $student->payments;



        $studentmarks = StudentMarks::where('student_id',$student->id)->get();

        // dd( $studentmarks);
        // if ($studentmarks->marks) {
        //     foreach ($studentmarks->marks as $mark_key => $mark) {
        //         if (!isset($data['curriculums'][(int)$mark['curriculum_id']])) {
        //             $data['curriculums'][(int)$mark['curriculum_id']]  = Curriculum::find((int)$mark['curriculum_id']);
        //         }
        //     }
        // }


        return view('student.dashboard', compact('student', 'payments', 'studentmarks'));
    }
}
