<?php

namespace App\Casts;

use Route;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MarksDetailsCast implements CastsAttributes
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
        if (Route::getCurrentRoute()->getName() == 'studentmarks.edit') {
            return $value;
        }

        $valueObject = json_decode($value, true);
        foreach ($valueObject as $key1 => $level_1) {
            foreach ($level_1 as $key2 => $index_value) {
                if (is_string($index_value)) {
                    $valueObject[$key1][$key2] = json_decode($index_value);
                }
            }
        }

        return $valueObject;
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
