<?php

namespace App\Http\Requests;

use App\Rules\NoControlCharacters;
use App\Models\CallOff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCallOffRequest extends FormRequest
{
    /**
     * Only the TSSD Unit may assign a Call-Off Number.
     */
    public function authorize(): bool
    {
        return $this->user()?->isTssd() === true;
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tssd_distribution_batch_id' => [
                'required',
                'integer',
                'exists:tssd_distribution_batches,id',
                Rule::unique(
                    CallOff::class,
                    'tssd_distribution_batch_id'
                ),
            ],

            'call_off_number' => [
                'required',
                'string',
                'max:100',
                new NoControlCharacters(),
                'regex:/^[A-Za-z0-9][A-Za-z0-9 ._\\\/-]*$/',
                Rule::unique(CallOff::class, 'call_off_number'),
            ],

            'assigned_at' => [
                'required',
                'date_format:Y-m-d',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
                new NoControlCharacters(),
            ],
        ];
    }

    /**
     * Normalize values before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'call_off_number' => strtoupper(
                trim((string) $this->input('call_off_number'))
            ),
        ]);
    }

    /**
     * Friendly validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tssd_distribution_batch_id.required' => 'Please select a distribution batch.',

            'tssd_distribution_batch_id.exists' => 'The selected distribution batch does not exist.',

            'tssd_distribution_batch_id.unique' => 'The selected distribution batch already has a Call-Off Number.',

            'call_off_number.required' => 'Please enter the Call-Off Number.',

            'call_off_number.unique' => 'The Call-Off Number has already been used.',

            'assigned_at.required' => 'Please provide the Call-Off assignment date.',
        ];
    }
}
