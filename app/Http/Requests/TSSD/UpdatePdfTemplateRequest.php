<?php

namespace App\Http\Requests\TSSD;

use App\Models\PdfTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdatePdfTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTssd() === true;
    }

    public function rules(): array
    {
        return [
            'template_name' => [
                'required',
                'string',
                'max:255',
            ],

            'report_type' => [
                'required',
                Rule::in(
                    PdfTemplate::REPORT_TYPES
                ),
            ],

            'pdf_file' => [
                'nullable',
                File::types(['pdf'])
                    ->max('20mb'),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'template_name' => trim(
                (string) $this->input(
                    'template_name'
                )
            ),

            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
