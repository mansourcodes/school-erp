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


        $this->replaceWithCard($this->examTool, $this->classRoom->students[0]);

        dd();
        if ($this->isLast) {
            // dispatch(new zipExamFilesJob($this->examTool));
        }
    }



    function replaceWithCard($examTool, $student)
    {
        $cardTemplate = $this->getCardTemplate();

        $studentArray = $student->toArray();
        $studentArray['course']  = $examTool->course->id;
        $studentCard = $this->createStudentCard($cardTemplate, $studentArray);

        echo $cardTemplate;
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

    function createStudentCard($cardTemplate, $studentArray)
    {
        foreach ($studentArray as $key => $value) {
            $cardTemplate = str_replace('XX' . $key . 'XX', $value, $cardTemplate);
        }
        return $cardTemplate;
    }
}
