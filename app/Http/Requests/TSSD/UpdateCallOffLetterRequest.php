<?php

namespace App\Http\Requests\TSSD;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCallOffLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->name === 'TSSD Unit';
    }

    public function rules(): array
    {
        return [
            'nefa_title' => [
                'required',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nefa_title.required' =>
                'The NEFA project title is required.',

            'nefa_title.max' =>
                'The NEFA project title must not exceed 1,000 characters.',
        ];
    }
}