<?php

namespace App\Observers;

use App\Jobs\generateExamsDocxJob;
use App\Models\ExamTool;
use Illuminate\Support\Facades\Storage;

class ExamToolObserver
{
    /**
     * Handle the ExamTool "created" event.
     *
     * @param  \App\Models\ExamTool  $examTool
     * @return void
     */
    public function created(ExamTool $examTool)
    {
        // $examTool = ExamTool::find($examTool->id);

        dispatch(new generateExamsDocxJob($examTool))->delay(
            now()->addSeconds(10)
        );;
    }

    public function deleting(ExamTool $examTool)
    {
        Storage::delete([
            "/public/" . $examTool->file,
            str_replace('/var/www/html/storage/app', '', $examTool->zip_file_path)
        ]);
    }
}
