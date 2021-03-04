<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassRoom extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'class_room_name',
        'class_room_number',
        'course_id',
        'teachers',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'teachers' => 'array',
    ];


    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class);
        // return $this->hasOne(\App\Models\Course::class);
    }

    public function students()
    {
        return $this->belongsToMany(\App\Models\Student::class);
        // return $this->hasMany(\App\Models\Student::class);
    }

    public function studentsCount()
    {
        return $this->belongsToMany(\App\Models\Student::class);
        // return $this->hasMany(\App\Models\Student::class);
    }

    public function getPrintDropdown()
    {

        // TODO: list the print
        $list = [
            [
                'label' => trans('reports.transcript'),
                'url' => backpack_url('reports?view=transcript&classroom=' . $this->id),
            ],
            [
                'label' => trans('reports.student_edu_statement'),
                'url' => backpack_url('reports?view=student_edu_statement&classroom=' . $this->id),
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
