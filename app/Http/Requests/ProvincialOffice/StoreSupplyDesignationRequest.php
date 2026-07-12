<?php

namespace App\Http\Requests\ProvincialOffice;

use App\Models\ProvinceDistribution;
use App\Services\CallOffInventoryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSupplyDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isProvincial()
            === true;
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input(
            'items',
            []
        );

        if (! is_array($items)) {
            $items = [];
        }

        $normalizedItems = [];

        foreach (
            $items as $itemId => $quantity
        ) {
            $normalizedItems[$itemId] =
                $quantity === ''
                    || $quantity === null
                    ? 0
                    : $quantity;
        }

        $this->merge([
            'project_code' => strtoupper(
                trim(
                    (string) $this->input(
                        'project_code'
                    )
                )
            ),

            'project_title' => trim(
                (string) $this->input(
                    'project_title'
                )
            ),

            'location' => trim(
                (string) $this->input(
                    'location'
                )
            ),

            'items' => $normalizedItems,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'province_distribution_id' => [
                'required',
                'integer',
                'exists:province_distributions,id',
            ],

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

    public function after(): array
    {
        return [
            function (
                Validator $validator
            ): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $provinceId =
                    $this->user()?->province_id;

                if (! $provinceId) {
                    $validator
                        ->errors()
                        ->add(
                            'province_distribution_id',
                            'Your account has no assigned province.'
                        );

                    return;
                }

                $allocationId = (int) $this->input(
                    'province_distribution_id'
                );

                $allocation =
                    ProvinceDistribution::query()
                        ->with([
                            'distributionBatch.callOff',
                            'items.item',
                        ])
                        ->whereKey($allocationId)
                        ->where(
                            'province_id',
                            $provinceId
                        )
                        ->first();

                if (! $allocation) {
                    $validator
                        ->errors()
                        ->add(
                            'province_distribution_id',
                            'The selected Call-Off allocation does not belong to your Provincial Office.'
                        );

                    return;
                }

                $callOff = $allocation
                    ->distributionBatch
                    ?->callOff;

                if (! $callOff) {
                    $validator
                        ->errors()
                        ->add(
                            'province_distribution_id',
                            'The selected allocation has no Call-Off record.'
                        );

                    return;
                }

                if (
                    ! in_array(
                        $callOff->status,
                        [
                            'Approved',
                            'Completed',
                        ],
                        true
                    )
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'province_distribution_id',
                            'The selected Call-Off is not approved for project designation.'
                        );

                    return;
                }

                if (
                    ! in_array(
                        $allocation->status,
                        [
                            'Partially Received',
                            'Received',
                        ],
                        true
                    )
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'province_distribution_id',
                            'PPE must first be physically received before it can be designated to a project.'
                        );

                    return;
                }

                $service = app(
                    CallOffInventoryService::class
                );

                $balances = $service->balances(
                    $allocation
                );

                $items = $this->input(
                    'items',
                    []
                );

                $hasQuantity = false;

                foreach (
                    $items as $itemId => $quantity
                ) {
                    $itemId = (int) $itemId;

                    $quantity = (int) $quantity;

                    if ($quantity <= 0) {
                        continue;
                    }

                    $hasQuantity = true;

                    if (
                        ! isset(
                            $balances[$itemId]
                        )
                    ) {
                        $validator
                            ->errors()
                            ->add(
                                "items.{$itemId}",
                                'This PPE item does not belong to the selected Call-Off.'
                            );

                        continue;
                    }

                    $available = (int) $balances[
                        $itemId
                    ]['available_for_projects'];

                    if ($quantity > $available) {
                        $item = $balances[
                            $itemId
                        ]['item'];

                        $itemName = trim(
                            ($item?->item_name
                                ?? 'PPE item')
                            .' '
                            .($item?->label ?? '')
                        );

                        $validator
                            ->errors()
                            ->add(
                                "items.{$itemId}",
                                "{$itemName} has only "
                                .number_format(
                                    $available
                                )
                                .' available under the selected Call-Off.'
                            );
                    }
                }

                if (! $hasQuantity) {
                    $validator
                        ->errors()
                        ->add(
                            'items',
                            'Enter at least one PPE quantity greater than zero.'
                        );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'province_distribution_id.required' => 'Please select a Call-Off.',

            'province_distribution_id.exists' => 'The selected Call-Off allocation does not exist.',

            'project_code.required' => 'The project code is required.',

            'project_title.required' => 'The project title is required.',

            'location.required' => 'The project location is required.',

            'designation_date.required' => 'The designation date is required.',

            'number_of_days.required' => 'The number of days is required.',

            'number_of_days.min' => 'The number of days must be at least 1.',

            'number_of_beneficiaries.required' => 'The number of beneficiaries is required.',

            'number_of_beneficiaries.min' => 'The number of beneficiaries must be at least 1.',

            'are_document.required' => 'The ARE PDF document is required.',

            'are_document.mimes' => 'The ARE document must be a PDF file.',

            'are_document.mimetypes' => 'The uploaded ARE document must be a valid PDF file.',

            'are_document.max' => 'The ARE PDF must not exceed 10 MB.',

            'items.required' => 'Enter at least one PPE quantity.',

            'items.array' => 'The submitted PPE quantities are invalid.',

            'items.*.integer' => 'Every PPE quantity must be a whole number.',

            'items.*.min' => 'PPE quantities cannot be negative.',
        ];
    }
}
