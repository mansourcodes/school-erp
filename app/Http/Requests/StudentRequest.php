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

        $rules = [
            // 'name' => 'required|min:5|max:255'

            'name' => 'required',
            'cpr' => 'required|numeric',
            // 'email',
            'mobile' => 'required|numeric',
            'mobile2' => 'nullable|numeric',
            // 'dob',
            // 'address',
            // 'live_in_state'  => ['required'],
            // 'relationship_state'  => ['required'],
            // 'family_members',
            // 'family_depends',
            // 'degree',
            // 'hawza_history',
            // 'hawza_history_details',
            // 'health_history',
            // 'health_history_details',
            // 'financial_state' => ['required'],
            // 'financial_details',
            // 'student_notes',
            'registration_at' => 'required',

        ];

        // if the student is new
        if (!request()->id) {
            $rules['password'] = 'nullable';
        } else {
            $rules['password'] = 'nullable|confirmed';
        }

        return $rules;
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
