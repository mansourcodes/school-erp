<?php

namespace App\Jobs;

use App\Models\ClassRoom;
use App\Models\ExamTool;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use phpDocumentor\Reflection\Types\Boolean;

class generateExamsDocxForClassRoomJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $classRoom;
    private $examTool;
    private $isLast;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ClassRoom $classRoom, ExamTool $examTool, $isLast = false)
    {
        $this->classRoom = $classRoom;
        $this->examTool = $examTool;
        $this->isLast = $isLast;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        dd($this->classRoom->students);


        if ($this->isLast) {
            // dispatch(new zipExamFilesJob($this->examTool));
        }
    }
}
