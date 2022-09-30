<?php

namespace App\Observers;

use App\Jobs\generateExamsDocxJob;
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
        dispatch(new generateExamsDocxJob($examTool));
    }

    public function deleting(ExamTool $examTool)
    {
        // dd($examTool);
        echo 'hi';
        exit;
    }
}
