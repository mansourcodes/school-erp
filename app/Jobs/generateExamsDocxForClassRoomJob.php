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
        $tmpPath = Storage::path('public') . "/examtools/tmp/examtool_" . $this->examTool->id;
        if (!file_exists($tmpPath)) {
            mkdir($tmpPath, 0755, true);
        }

        $cardTemplate = $this->getCardTemplate();
        $student = $this->classRoom->students[0];
        $counter = 1;
        foreach ($this->classRoom->students as $key => $student) {
            $studentArray = $this->getStudentArray($student);
            $studentCard = $this->createStudentCard($cardTemplate,  $studentArray, $this->examTool);
            $studentExamFileNameAndPath = $tmpPath . '/' . $this->getStudentExamFileName($student, $counter++);
            $this->createStudentExamFile(
                Storage::path('public/') . $this->examTool->file,
                $studentCard,
                $studentExamFileNameAndPath
            );
        }



        if ($this->isLast) {
            dispatch(new zipExamFilesJob($this->examTool));
        }
    }



    function getStudentExamFileName($student, $counter)
    {
        $studentExamFileName = $counter . '_' . $this->classRoom->id . '_' . $student->student_id . ".docx";
        $studentExamFileName =  str_replace(' ', '_',  $studentExamFileName);
        return $studentExamFileName;
    }

    function getStudentArray($student)
    {
        $studentArray = $student->toArray();

        $studentArray['course']  = $this->examTool->course->id;
        $studentArray['class_name']  = $this->classRoom->class_room_name;
        $studentArray = Arr::only($studentArray, [
            'name',
            'cpr',
            'student_id',
            'mobile',
            // 'course',
            'class_name',
        ]);
        return $studentArray;
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


    function createStudentExamFile($templateFile, $cardTempalte, $newFileNameWithPath)
    {
        $zip = new clsTbsZip();
        $needle_tag = "++student++";


        // Open the document
        $zip->Open($templateFile);
        $content = $zip->FileRead('word/document.xml');
        $p = strpos($content, '</w:body>');
        if ($p === false) {
            abort(500, "Tag </w:body> not found in document. Not supported document.");
        }

        $p_tag = strpos($content, '++student++');
        if ($p_tag === false) {
            // dd($content);
            abort(500, "Tag ++student++ not found in document.It`s needed for adding the student information.");
        }


        $content = str_replace($needle_tag, $cardTempalte, $content);
        $zip->FileReplace('word/document.xml', $content, TBSZIP_STRING);

        // Save as a new file
        $zip->Flush(TBSZIP_FILE, $newFileNameWithPath);
    }
}
