<?php

namespace App\Http\Requests\ProvincialOffice;

use App\Models\DeliveryReceipt;
use App\Rules\NoControlCharacters;
use App\Services\DeliveryReceiptInventoryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
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
            'delivery_receipt_id' => [
                'required',
                'integer',
                'exists:delivery_receipts,id',
            ],

            'project_code' => [
                'required',
                'string',
                'max:255',
                new NoControlCharacters(),
            ],

            'project_title' => [
                'required',
                'string',
                'max:255',
                new NoControlCharacters(),
            ],

            'location' => [
                'required',
                'string',
                'max:255',
                new NoControlCharacters(),
            ],

            'designation_date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],

            'number_of_days' => [
                'required',
                'integer',
                'min:1',
                'max:3650',
            ],

            'number_of_beneficiaries' => [
                'required',
                'integer',
                'min:1',
                'max:1000000',
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
                new NoControlCharacters(),
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'items.*' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (
                Validator $validator
            ): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $provinceId = $this->user()?->province_id;

                if (! $provinceId) {
                    $validator->errors()->add(
                        'delivery_receipt_id',
                        'Your account has no assigned province.'
                    );

                    return;
                }

                $receipt = DeliveryReceipt::query()
                    ->with([
                        'items.item',
                        'provinceDistribution.distributionBatch.callOff',
                    ])
                    ->whereKey(
                        (int) $this->input(
                            'delivery_receipt_id'
                        )
                    )
                    ->where(
                        'province_id',
                        $provinceId
                    )
                    ->where(
                        'status',
                        'Received'
                    )
                    ->first();

                if (! $receipt) {
                    $validator->errors()->add(
                        'delivery_receipt_id',
                        'The selected Delivery Receipt is unavailable.'
                    );

                    return;
                }

                $callOff = $receipt
                    ->provinceDistribution
                    ?->distributionBatch
                    ?->callOff;

                if (
                    ! $callOff
                    || ! in_array(
                        $callOff->status,
                        [
                            'Approved',
                            'Completed',
                        ],
                        true
                    )
                ) {
                    $validator->errors()->add(
                        'delivery_receipt_id',
                        'The selected Delivery Receipt does not belong to an approved Call-Off.'
                    );

                    return;
                }

                try {
                    app(
                        DeliveryReceiptInventoryService::class
                    )->validateProjectQuantities(
                        $receipt,
                        $this->input(
                            'items',
                            []
                        )
                    );
                } catch (
                    ValidationException $exception
                ) {
                    foreach (
                        $exception->errors() as $field => $messages
                    ) {
                        foreach ($messages as $message) {
                            $validator->errors()->add(
                                $field,
                                $message
                            );
                        }
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_receipt_id.required' => 'Select a Delivery Receipt.',

            'delivery_receipt_id.exists' => 'The selected Delivery Receipt does not exist.',

            'project_code.required' => 'The project code is required.',

            'project_title.required' => 'The project title is required.',

            'location.required' => 'The project location is required.',

            'designation_date.required' => 'The designation date is required.',

            'number_of_days.min' => 'The number of days must be at least 1.',

            'number_of_beneficiaries.min' => 'The number of beneficiaries must be at least 1.',

            'are_document.required' => 'The ARE PDF document is required.',

            'are_document.mimes' => 'The ARE document must be a PDF file.',

            'are_document.max' => 'The ARE PDF must not exceed 10 MB.',

            'items.required' => 'Enter at least one PPE quantity.',
        ];
    }
}
