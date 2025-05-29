<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\CurriculumMarksDetails;


class Curriculum extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'curriculum_name',
        'short_name',
        'book_name',
        'weight_in_hours',
        'curriculum_category_id',
        'marks_labels',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'marks_labels' => CurriculumMarksDetails::class,
    ];

    protected $appends = [
        'long_name',
        'marks_labels_flat'
    ];


    public function curriculumCategory()
    {
        // return $this->hasOne(\App\Models\CurriculumCategory::class);
        return $this->belongsTo(\App\Models\CurriculumCategory::class);
    }

    public function academicPaths()
    {
        return $this->hasMany(\App\Models\AcademicPath::class);
        // return $this->belongsToMany(\App\Models\AcademicPath::class);
    }


    public function getLongNameAttribute()
    {
        return "[" . $this->curriculum_name . "] " . $this->book_name;
    }


    public function getMarksLabelsFlatAttribute()
    {
        $flat_array = array();
        foreach ($this->marks_labels as $key => $value_array) {
            if ($value_array && StudentMarks::$standard_marks_composer[$key] == 'Single')
                foreach ($value_array as $key => $value) {
                    $tmp_label = '';
                    $tmp_label .= $value->label ?? 'لا يوجد عنوان';
                    $tmp_label .= isset($value->mark) ? "($value->mark)" : '(لا يوجد درجة)';

                    $flat_array[] = $tmp_label;
                }
        }

        if (now() < '2025-08-08') {

            //sort array by marks
            usort($flat_array, function ($a, $b) {
                preg_match('/\((\d+)\)/', $a, $matchesA);
                preg_match('/\((\d+)\)/', $b, $matchesB);
                $markA = isset($matchesA[1]) ? (int)$matchesA[1] : 0;
                $markB = isset($matchesB[1]) ? (int)$matchesB[1] : 0;
                return $markB <=> $markA; // Sort in descending order
            });
        }

        return $flat_array;
    }
}
