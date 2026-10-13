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
            // Teachers join in Phase 3 (PLAN.md §8).
            'role' => ['required', 'in:student'],
            'cpr' => ['required', 'digits:9'],
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
