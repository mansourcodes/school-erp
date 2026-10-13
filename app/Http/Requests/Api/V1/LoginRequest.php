<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Students sign in with their CPR, teachers with their email (PLAN.md §3.1, §8).
            'role' => ['required', 'in:student,teacher'],
            'cpr' => ['required_if:role,student', 'nullable', 'digits:9'],
            'email' => ['required_if:role,teacher', 'nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:128'],
            'turnstile_token' => ['nullable', 'string', 'max:2048'],
            // Honeypot: any value is accepted here and answered as a failed login.
            'website' => ['nullable'],
        ];
    }

    public function attributes()
    {
        return __('portal.attributes');
    }
}
