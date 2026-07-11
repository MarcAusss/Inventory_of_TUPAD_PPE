<?php

namespace App\Http\Requests\ProvincialOffice;

use App\Models\ProvincialInventory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSupplyDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->name
            === 'Provincial Office';
    }

    public function rules(): array
    {
        return [
            'project_code' => [
                'required',
                'string',
                'max:255',
            ],

            'project_title' => [
                'required',
                'string',
                'max:255',
            ],

            'location' => [
                'required',
                'string',
                'max:255',
            ],

            'designation_date' => [
                'required',
                'date',
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
                'mimetypes:application/pdf',
                'max:10240',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'items' => [
                'required',
                'array',
            ],

            'items.*' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $provinceId = $this->user()?->province_id;

                if (! $provinceId) {
                    $validator->errors()->add(
                        'items',
                        'Your account has no assigned province.'
                    );

                    return;
                }

                $items = $this->input(
                    'items',
                    []
                );

                $hasQuantity = false;

                foreach ($items as $itemId => $quantity) {
                    $quantity = (int) $quantity;

                    if ($quantity <= 0) {
                        continue;
                    }

                    $hasQuantity = true;

                    $inventory = ProvincialInventory::query()
                        ->with('item')
                        ->where(
                            'province_id',
                            $provinceId
                        )
                        ->where(
                            'item_id',
                            $itemId
                        )
                        ->first();

                    if (! $inventory) {
                        $validator->errors()->add(
                            "items.{$itemId}",
                            'This PPE item is not available in your provincial inventory.'
                        );

                        continue;
                    }

                    if (
                        $quantity
                        > (int) $inventory->quantity
                    ) {
                        $itemName = trim(
                            $inventory->item->item_name
                            .' '
                            .($inventory->item->label ?? '')
                        );

                        $validator->errors()->add(
                            "items.{$itemId}",
                            "{$itemName} has only "
                            .number_format(
                                $inventory->quantity
                            )
                            ." available."
                        );
                    }
                }

                if (! $hasQuantity) {
                    $validator->errors()->add(
                        'items',
                        'Enter at least one PPE quantity.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'project_code.required' =>
                'The project code is required.',

            'project_title.required' =>
                'The project title is required.',

            'location.required' =>
                'The project location is required.',

            'designation_date.required' =>
                'The designation date is required.',

            'number_of_days.min' =>
                'The number of days must be at least 1.',

            'number_of_beneficiaries.min' =>
                'The number of beneficiaries must be at least 1.',

            'are_document.required' =>
                'The ARE PDF document is required.',

            'are_document.mimes' =>
                'The ARE document must be a PDF file.',

            'are_document.max' =>
                'The ARE PDF must not exceed 10 MB.',

            'items.required' =>
                'Enter at least one PPE quantity.',
        ];
    }
}