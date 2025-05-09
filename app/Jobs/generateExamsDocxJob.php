<?php

namespace App\Jobs;

use App\Models\ClassRoom;
use App\Models\ExamTool;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Log\Logger;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class generateExamsDocxJob // implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $examTool;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ExamTool $examTool)
    {
        $this->examTool = $examTool;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        $classRooms = ClassRoom::where([
            ['attend_table', 'like', "%curriculum_id_:_{$this->examTool->curriculum_id}_,%"],
            ['course_id', $this->examTool->course_id]
        ])->get();

        $len = count($classRooms->toArray()) - 1;
        foreach ($classRooms as $key => $classRoom) {
            if ($key === $len) {
                dispatch(new generateExamsDocxForClassRoomJob($classRoom, $this->examTool, true));
            } else {
                dispatch(new generateExamsDocxForClassRoomJob($classRoom, $this->examTool));
            }
        }
    }
}
