<?php

namespace App\Observers;

use App\Models\ExamTool;

class ExamToolObserver
{
    /**
     * Handle the ExamTool "creating" event.
     *
     * @param  \App\Models\ExamTool  $examTool
     * @return void
     */
    public function creating(ExamTool $examTool)
    {
        dd($examTool);
    }

    public function deleting(ExamTool $examTool)
    {
        dd($examTool);
    }
}
