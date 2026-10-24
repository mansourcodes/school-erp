<?php

namespace App\Http\Requests;

use App\Models\Account\PaymentExemption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentExemptionRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'course_id' => 'required|integer|exists:courses,id',
            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
                // One live ticket per student per course (soft-deleted tickets don't count).
                Rule::unique('payment_exemptions')
                    ->where('course_id', $this->input('course_id'))
                    ->whereNull('deleted_at')
                    ->ignore($this->route('id')),
            ],
            'reason' => ['required', Rule::in(PaymentExemption::REASONS)],
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'student_id.unique' => __('account.exemption_unique'),
        ];
    }
}
