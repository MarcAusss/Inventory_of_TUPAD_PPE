<?php

namespace App\Http\Requests\ProvincialOffice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplyDesignationRequest extends FormRequest
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
        $provinceId = $this->user()?->province_id;

        return [
            'project_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique(
                    'supply_designations',
                    'project_code'
                )->where(
                    fn ($query) => $query->where(
                        'province_id',
                        $provinceId
                    )
                ),
            ],

            'project_title' => [
                'required',
                'string',
                'max:255',
            ],

            'designation_date' => [
                'required',
                'date',
            ],

            'location' => [
                'required',
                'string',
                'max:500',
            ],

            'number_of_days' => [
                'required',
                'integer',
                'min:1',
            ],

            'number_of_beneficiaries' => [
                'required',
                'integer',
                'min:1',
            ],

            'are_document' => [
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
            'project_code' => strtoupper(
                trim((string) $this->input('project_code'))
            ),

            'project_title' => trim(
                (string) $this->input('project_title')
            ),

            'location' => trim(
                (string) $this->input('location')
            ),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $items = $this->input('items', []);

            if (! is_array($items)) {
                return;
            }

            $total = collect($items)
                ->map(fn ($quantity): int => (int) $quantity)
                ->sum();

            if ($total <= 0) {
                $validator->errors()->add(
                    'items',
                    'Enter at least one PPE quantity greater than zero.'
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_code.required' => 'Please enter the Project Code.',

            'project_code.unique' => 'This Project Code already exists for your province.',

            'project_title.required' => 'Please enter the Project Title.',

            'designation_date.required' => 'Please enter the designation date.',

            'location.required' => 'Please enter the project location.',

            'number_of_days.required' => 'Please enter the number of project days.',

            'number_of_days.min' => 'The project must have at least one day.',

            'number_of_beneficiaries.required' => 'Please enter the number of beneficiaries.',

            'number_of_beneficiaries.min' => 'The project must have at least one beneficiary.',

            'are_document.required' => 'Please upload the ARE PDF.',

            'are_document.mimes' => 'The ARE document must be a PDF file.',

            'are_document.max' => 'The ARE PDF must not exceed 10 MB.',

            'items.required' => 'Please enter the PPE quantities to designate.',

            'items.*.min' => 'PPE quantities cannot be negative.',
        ];
    }
}
