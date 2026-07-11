<?php

namespace App\Http\Requests\ProvincialOffice;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isProvincial() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dr_number' => [
                'required',
                'string',
                'max:100',
                'unique:delivery_receipts,dr_number',
            ],

            'delivery_date' => [
                'required',
                'date',
            ],

            'physical_receiver_name' => [
                'required',
                'string',
                'max:255',
            ],

            'document' => [
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

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'dr_number' => strtoupper(
                trim((string) $this->input('dr_number'))
            ),

            'physical_receiver_name' => trim(
                (string) $this->input('physical_receiver_name')
            ),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dr_number.required' => 'Please enter the Delivery Receipt Number.',

            'dr_number.unique' => 'The Delivery Receipt Number has already been used.',

            'delivery_date.required' => 'Please provide the actual delivery date.',

            'physical_receiver_name.required' => 'Please enter the name of the physical receiver.',

            'document.required' => 'Please upload the Delivery Receipt PDF.',

            'document.mimes' => 'The Delivery Receipt document must be a PDF file.',

            'document.max' => 'The Delivery Receipt PDF must not exceed 10 MB.',

            'items.required' => 'Please enter the received PPE quantities.',

            'items.*.integer' => 'Every received quantity must be a whole number.',

            'items.*.min' => 'Received quantities cannot be negative.',
        ];
    }
}
