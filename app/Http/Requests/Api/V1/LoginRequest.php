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
            // Students sign in with their CPR, teachers with their username (PLAN.md §3.1, §8).
            'role' => ['required', 'in:student,teacher'],
            // CPR: digits only, 1–12 long (not every student has a 9-digit Bahraini CPR).
            'cpr' => ['required_if:role,student', 'nullable', 'digits_between:1,12'],
            // Length only: the format is not checked here, so a malformed name fails like a wrong one.
            'username' => ['required_if:role,teacher', 'nullable', 'string', 'max:50'],
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
