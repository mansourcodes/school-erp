<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
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
        return [
            // 'name' => 'required|min:5|max:255'

            'student_name' => 'required',
            'cpr' => 'required|numeric',
            // 'email',
            'mobile' => 'required|numeric',
            // 'mobile2',
            // 'dob',
            // 'address',
            // 'live_inـstate'  => ['required'],
            // 'relationshipـstate'  => ['required'],
            // 'family_members',
            // 'family_depends',
            // 'degree',
            // 'hawzaـhistory',
            // 'hawzaـhistory_details',
            // 'healthـhistory',
            // 'healthـhistory_details',
            // 'financialـstate' => ['required'],
            // 'financial_details',
            // 'student_notes',
            'registration_at' => 'required',
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
