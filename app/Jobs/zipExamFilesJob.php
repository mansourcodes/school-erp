<?php

namespace App\Jobs;

use App\Models\ExamTool;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class zipExamFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $examTool;


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

        $this->create();
        // zip files on /public/examtools/tmp/examtool_{id}/*
        // save zip file on /public/examtools/exams/zip/exam_{subject}_{id}.zip
        // update with path on Examtool 
        // update status
        // delete all files
    }


    /**
     * @throws RuntimeException If the file cannot be opened
     */
    public function create()
    {
        // $filePath = 'app/course/files.zip';
        $filePath = Storage::path('public') . "/examtools/exams/examtool_" . $this->examTool->id . ".zip";
        $contentPath = Storage::path('public') . "/examtools/tmp/examtool_" . $this->examTool->id;

        $zip = new \ZipArchive();

        if ($zip->open($filePath, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Cannot open ' . $filePath);
        }

        // $this->addContent($zip, realpath('app/course'));
        $this->addContent($zip, $contentPath);
        $zip->close();

        dd(1);
    }


    /**
     * This takes symlinks into account.
     *
     * @param ZipArchive $zip
     * @param string     $path
     */
    private function addContent(\ZipArchive $zip, string $path)
    {
        /** @var SplFileInfo[] $files */
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::FOLLOW_SYMLINKS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        while ($iterator->valid()) {
            if (!$iterator->isDot()) {
                $filePath = $iterator->getPathName();
                $relativePath = substr($filePath, strlen($path) + 1);

                if (!$iterator->isDir()) {
                    $zip->addFile($filePath, $relativePath);
                } else {
                    if ($relativePath !== false) {
                        $zip->addEmptyDir($relativePath);
                    }
                }
            }
            $iterator->next();
        }
    }
}
