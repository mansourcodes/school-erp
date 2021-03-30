<?php

namespace App\Models;

use App\Helpers\HtmlHelper;
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
        'attend_table',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'teachers' => 'array',
        'attend_table' => 'array',
    ];


    protected $appends = [
        'long_name'
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

    public function getLongNameAttribute()
    {
        return $this->class_room_name . " [" . $this->class_room_number . "]";
    }

    public function getPrintDropdown()
    {

        $list = [
            [
                'label' => trans('reports.scoring_sheet'),
                'url' => backpack_url('reports?view=scoring_sheet&classroom=' . $this->id),
            ],
            [
                'label' => trans('reports.transcript'),
                'url' => backpack_url('reports?view=transcript&classroom=' . $this->id),
            ],
            [
                'label' => trans('reports.student_edu_statement'),
                'url' => backpack_url('reports?view=student_edu_statement&classroom=' . $this->id),
            ],
        ];



        $html = HtmlHelper::dropdownMenuButton($list);


        return $html;
    }
}
