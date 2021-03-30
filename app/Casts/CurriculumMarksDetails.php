<?php

namespace App\Casts;

use Route;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class CurriculumMarksDetails implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        // dd(Route::getCurrentRoute()->getName());
        if (Route::getCurrentRoute()->getName() == 'curriculum.edit') {
            return $value;
        }

        $value = json_decode($value);

        if (!$value) {
            $value = [];
        } else {

            foreach ($value as $key => $obj) :
                $obj->finalexam_mark_details = json_decode($obj->finalexam_mark_details);
                $obj->midexam_marks_details = json_decode($obj->midexam_marks_details);
                $obj->class_mark_details = json_decode($obj->class_mark_details);
                $obj->marks_details = json_decode($obj->marks_details);
                $obj->project_marks_details = json_decode($obj->project_marks_details);
                $obj->practice_mark_details = json_decode($obj->practice_mark_details);
                @$obj->attend_mark_details = json_decode($obj->attend_mark_details);
            endforeach;
        }

        if (isset($value[0])) {
            return  (array)$value[0];
        } else {
            return  (array)$value;
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {

        return $value;
    }
}
