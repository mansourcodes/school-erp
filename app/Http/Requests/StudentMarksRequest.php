<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentMarksRequest extends FormRequest
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
        $student_id = $this->student_id;
        $course_id = $this->course_id;
        return [
            // 'name' => 'required|min:5|max:255'

            'student_id'  => [
                'required',
                Rule::unique('student_marks')->where(function ($query) use ($id, $student_id, $course_id) {
                    return $query
                        ->where('id', '!=', $id)
                        ->where('student_id', $student_id)
                        ->where('course_id', $course_id);
                }),
            ],
            'course_id'  => 'required',
            // 'marks'  => 'required',
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
            'student_id'                  => 'الطالب',
            'course_id'                  => ' الدورة',

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
            'unique' => trans('studentmark.unique'),
        ];
    }
}
