<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_year',
        'hijri_year',
        'semester',
        'duration',
        'academic_path_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];


    protected $appends = [
        'long_name'
    ];

    public function academicPath()
    {
        return $this->belongsTo(\App\Models\AcademicPath::class);
        // return $this->hasOne(\App\Models\AcademicPath::class);
    }

    public function classRooms()
    {
        return $this->hasMany(\App\Models\ClassRoom::class);
        // return $this->belongsToMany(\App\Models\ClassRoom::class);
    }

    public function getLongNameAttribute()
    {

        if (is_null($this->academicPath)) {
            return  "... [" . $this->course_year . "] " . $this->hijri_year . " [" . $this->semester  . "]";
        } else {
            return $this->academicPath->academic_path_name . " [" . $this->course_year . "] " . $this->hijri_year . " [" . $this->semester  . "]";
        }
    }


    public function getPrintDropdown()
    {

        // TODO: list the print
        $list = [
            [
                'label' => trans('reports.transcript'),
                'url' => backpack_url('reports?view=transcript&course=' . $this->id),
            ],
        ];

        $links = [];
        foreach ($list as $key => $value) {
            $links[] = '<a class="dropdown-item" href="' . $value['url']   . '">' . $value['label'] . '</a>';
        }

        $html = '<div class="btn-group">
        <div class="dropdown">
          <button class="btn btn-primary btn-sm dropdown-toggle" id="dropdownMenuButton" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="la la-print"></i>
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="">

                    ' . implode('', $links) . '

          </div>
        </div>
      </div>';

        return $html;
    }
}
