<?php

namespace App\Http\Requests;

use App\Models\ClassRoom;
use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;

/** Admin message form: on create a classroom and its students (or all of them); always the text. */
class MessageRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check() && backpack_user()->hasRole('Superadmin');
    }

    protected function prepareForValidation()
    {
        $this->merge(['body' => trim((string) $this->input('body'))]);
    }

    public function rules()
    {
        $rules = ['body' => ['required', 'string', 'max:' . Message::MAX_LENGTH]];

        if ($this->isMethod('post')) {
            $rules += [
                'class_room_id' => ['required', 'integer', 'exists:class_rooms,id'],
                'student_ids' => ['required_unless:all_students,1', 'array'],
                'student_ids.*' => ['integer', 'distinct'],
            ];
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (! $this->isMethod('post') || $validator->errors()->isNotEmpty()) {
                return;
            }

            $enrolled = ClassRoom::find($this->input('class_room_id'))->students()->pluck('students.id')->all();
            if ($this->boolean('all_students')) {
                if (! $enrolled) {
                    $validator->errors()->add('class_room_id', trans('message.empty_classroom'));
                }
            } elseif (array_diff(array_map('intval', $this->input('student_ids', [])), $enrolled)) {
                $validator->errors()->add('student_ids', trans('portal.messages.foreign_students'));
            }
        });
    }

    public function attributes()
    {
        return [
            'body' => trans('message.body'),
            'class_room_id' => trans('classroom.classroom'),
            'student_ids' => trans('message.recipients'),
        ];
    }
}
