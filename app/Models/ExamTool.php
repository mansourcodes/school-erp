<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamTool extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject',
        'course_id',
        'file',
        'status',
        'zip_file_path',
        'zip_file_size',
        'meta',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'course_id' => 'integer',
    ];


    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class);
    }


    public function setFileAttribute($value)
    {
        $attribute_name = "file";
        $disk = "public";
        $destination_path = "examtools/exams";

        $this->uploadFileToDisk($value, $attribute_name, $disk, $destination_path);

        // return $this->attributes[{$attribute_name}]; // uncomment if this is a translatable field
    }


    public function getZipFileUrlAttribute($crud = false)
    {

        if (!$this->zip_file_path) {
            return '';
        }

        $file_path = explode('/', $this->zip_file_path);
        $file_path = array_splice($file_path, count($file_path) - 3, 3);
        return  url('/') . '/storage/' . implode('/', $file_path);
    }

    public function downloadLinkHtml($crud = false)
    {
        if ($this->zip_file_url) {
            return '<a class="btn btn-sm btn-success" target="_blank" href="' . $this->zip_file_url . '" data-toggle="tooltip" title="Just a demo custom button."><i class="fa fa-search"></i> ' . trans('examtool.download') . '</a>';
        } else {
            return '';
        }
    }
}
