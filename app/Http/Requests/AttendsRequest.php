<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {


        $id = $this->id;
        $date = $this->date;
        $start_time = $this->start_time;
        $class_room_id = $this->class_room_id;
        $curriculum_id = $this->curriculum_id;


        return [
            // 'name' => 'required|min:5|max:255'
            'date' => [
                'required',
                Rule::unique('student_marks')->where(function ($query) use ($id, $date, $start_time, $class_room_id, $curriculum_id) {
                    return $query
                        ->where('id', '!=', $id)
                        ->where('student_id', $date)
                        ->where('start_time', $start_time)
                        ->where('class_room_id', $class_room_id)
                        ->where('curriculum_id', $curriculum_id);
                }),
            ],
            'start_time' => 'required',
            'class_room_id' => 'required',
            'course_id' => 'required',
            'curriculum_id' => 'required',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
