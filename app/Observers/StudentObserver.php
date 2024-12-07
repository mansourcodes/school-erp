<?php

namespace App\Observers;

use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class StudentObserver
{
    /**
     * Handle the ExamTool "creating" event.
     *
     * @param  \App\Models\Student  $model
     * @return void
     */    public function creating(Student $model)
    {
        $model->password = Hash::make($model->mobile);
    }
}
