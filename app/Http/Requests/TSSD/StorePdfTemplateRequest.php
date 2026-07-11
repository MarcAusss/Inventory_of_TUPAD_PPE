<?php

namespace App\Http\Requests\TSSD;

use App\Models\PdfTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StorePdfTemplateRequest extends FormRequest
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
                'required',
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

    public function messages(): array
    {
        return [
            'template_name.required' => 'Please enter the PDF template name.',

            'report_type.required' => 'Please choose which report will use this PDF.',

            'pdf_file.required' => 'Please upload the blank PDF layout.',

            'pdf_file.mimes' => 'The uploaded layout must be a PDF file.',
        ];
    }
}
