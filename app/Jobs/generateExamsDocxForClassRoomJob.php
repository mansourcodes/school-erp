<?php

namespace App\Jobs;

use App\Helpers\lib\tbszip\clsTbsZip;
use App\Models\ClassRoom;
use App\Models\ExamTool;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
        $cardTemplate = $this->getCardTemplate();
        $student = $this->classRoom->students[0];

        foreach ($this->classRoom->students as $key => $student) {
            $studentArray = $student->toArray();

            $studentArray['course']  = $this->examTool->course->id;
            $studentArray['class_name']  = $this->classRoom->class_room_name;
            $studentArray = Arr::only($studentArray, [
                'student_name',
                'cpr',
                'student_id',
                'mobile',
                // 'course',
                'class_name',
            ]);

            $studentCard = $this->createStudentCard($cardTemplate,  $studentArray, $this->examTool);

            var_dump($studentCard);
            break;
        }



        dd();
        if ($this->isLast) {
            // dispatch(new zipExamFilesJob($this->examTool));
        }
    }



    function getCardTemplate()
    {
        $zip = new clsTbsZip();

        // Open the document
        $zip->Open(public_path() . '/files/card_template.docx');
        $content = $zip->FileRead('word/document.xml');
        $p_start = strpos($content, '<w:body>') + strlen('<w:body>');
        $p_end = strpos($content, '</w:body>');

        $content = substr($content, $p_start, $p_end - $p_start);
        return $content;
    }

    function createStudentCard($cardTemplate, $studentArray, $examTool)
    {

        foreach ($studentArray as $key => $value) {
            $cardTemplate = str_replace('XX' . $key . 'XX', $value, $cardTemplate);
        }

        return $cardTemplate;
    }
}
