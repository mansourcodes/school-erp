<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
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

        $request = request();
        return [
            'course_id' => 'required',
            'student_id' => [
                'required',
                // Rule::unique('student_id', 'type'),
                Rule::unique('payments')->where(function ($query) use ($request) {
                    return
                        $query->where('course_id', $request->course_id)
                        ->where('type', $request->type)
                        ->where('student_id', $request->student_id);
                })
            ],
            'source' => 'required',
            'type' => 'required',
            'amount' => 'required|numeric',
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
            'student_id.unique' => 'الرصيد لهذا الطالب+الدورة الدراسية+نوع الرصيد : موجود من قبل', // custom message

        ];
    }
}
