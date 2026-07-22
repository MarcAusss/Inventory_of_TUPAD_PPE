<?php

namespace App\Http\Requests\TSSD;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCallOffLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->name === 'TSSD Unit';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'print_total_amount' => $this->normalizeAmount(
                $this->input('print_total_amount')
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'nefa_title' => [
                'required',
                'string',
                'max:1000',
            ],
            'print_total_amount' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],
            'print_margin_top' => [
                'required',
                'numeric',
                'min:0',
                'max:50',
            ],
            'print_margin_right' => [
                'required',
                'numeric',
                'min:0',
                'max:50',
            ],
            'print_margin_bottom' => [
                'required',
                'numeric',
                'min:27',
                'max:70',
            ],
            'print_margin_left' => [
                'required',
                'numeric',
                'min:0',
                'max:50',
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
            'print_total_amount.required' =>
                'The printed total PO amount is required.',
            'print_total_amount.numeric' =>
                'The printed total PO amount must be a valid number.',
            'print_total_amount.min' =>
                'The printed total PO amount cannot be negative.',
            'print_margin_top.required' =>
                'The top margin is required.',
            'print_margin_right.required' =>
                'The right margin is required.',
            'print_margin_bottom.required' =>
                'The bottom margin is required.',
            'print_margin_left.required' =>
                'The left margin is required.',
            'print_margin_top.numeric' =>
                'The top margin must be a valid number.',
            'print_margin_right.numeric' =>
                'The right margin must be a valid number.',
            'print_margin_bottom.numeric' =>
                'The bottom margin must be a valid number.',
            'print_margin_left.numeric' =>
                'The left margin must be a valid number.',
            'print_margin_top.max' =>
                'The top margin must not exceed 50 mm.',
            'print_margin_right.max' =>
                'The right margin must not exceed 50 mm.',
            'print_margin_bottom.min' =>
                'The bottom margin must be at least 27 mm to protect the footer.',
            'print_margin_bottom.max' =>
                'The bottom margin must not exceed 70 mm.',
            'print_margin_left.max' =>
                'The left margin must not exceed 50 mm.',
        ];
    }

    private function normalizeAmount(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return str_replace(
            [',', '₱', 'P', 'p', ' '],
            '',
            trim($value)
        );
    }
}
