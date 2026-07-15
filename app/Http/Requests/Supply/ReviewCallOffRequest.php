<?php

namespace App\Http\Requests\Supply;

use App\Models\CallOff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewCallOffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSupply() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'call_off_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique(CallOff::class, 'call_off_number'),
            ],

            'call_off_date' => [
                'required',
                'date',
            ],

            'approval_document' => [
                'required',
                'file',
                'mimes:pdf',
                'max:10240',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'call_off_number' => strtoupper(
                trim((string) $this->input('call_off_number'))
            ),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'call_off_number.required' =>
                'Please enter the Call-Off Number.',
            'call_off_number.unique' =>
                'The Call-Off Number has already been used.',
            'call_off_date.required' =>
                'Please provide the official Call-Off date.',
            'call_off_date.date' =>
                'The official Call-Off date must be valid.',
            'approval_document.required' =>
                'Please upload the approved Call-Off PDF.',
            'approval_document.mimes' =>
                'The approved Call-Off document must be a PDF file.',
            'approval_document.max' =>
                'The approved Call-Off PDF must not exceed 10 MB.',
        ];
    }
}
