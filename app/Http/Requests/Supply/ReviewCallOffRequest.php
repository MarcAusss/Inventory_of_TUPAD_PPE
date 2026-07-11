<?php

namespace App\Http\Requests\Supply;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewCallOffRequest extends FormRequest
{
    /**
     * Only the Supply Unit may approve or reject Call-Offs.
     */
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
            'decision' => [
                'required',
                Rule::in([
                    'Approved',
                    'Rejected',
                ]),
            ],

            'call_off_date' => [
                'nullable',
                'date',
                'required_if:decision,Approved',
            ],

            'approval_document' => [
                'nullable',
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decision.required' => 'Please choose whether to approve or reject the Call-Off.',

            'decision.in' => 'The selected Call-Off decision is invalid.',

            'call_off_date.required_if' => 'The official Call-Off date is required when approving.',

            'call_off_date.date' => 'The official Call-Off date must be a valid date.',

            'approval_document.file' => 'The approval document must be a valid file.',

            'approval_document.mimes' => 'The approval document must be a PDF file.',

            'approval_document.max' => 'The approval document must not exceed 10 MB.',
        ];
    }
}
