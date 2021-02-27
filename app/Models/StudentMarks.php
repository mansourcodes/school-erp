<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentMarks extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id',
        'course_id',
        'marks',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];


    public function Student()
    {
        return $this->belongsTo(\App\Models\Student::class);
        // return $this->hasOne(\App\Models\Student::class);
    }

    public function Course()
    {
        return $this->belongsTo(\App\Models\Course::class);
        // return $this->hasOne(\App\Models\Course::class);
    }



    public function getPrintDropdown()
    {

        // TODO: list the print
        $list = [
            [
                'label' => 'reports/transcript',
                'url' => backpack_url('reports/transcript/' . $this->id),
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
