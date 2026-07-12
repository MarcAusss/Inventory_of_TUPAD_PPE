<?php

namespace App\Http\Requests\ProvincialOffice;

use App\Models\DeliveryReceiptItem;
use App\Models\ProvinceDistribution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDeliveryReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isProvincial() === true
            && $this->user()?->province_id !== null;
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

    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (Validator $validator): void {
                /** @var ProvinceDistribution|null $allocation */
                $allocation = $this->route(
                    'provinceDistribution'
                );

                if (! $allocation) {
                    $validator->errors()->add(
                        'province_distribution',
                        'The provincial allocation could not be found.'
                    );

                    return;
                }

                $allocation->loadMissing([
                    'items.item',
                    'distributionBatch.callOff',
                ]);

                $provinceId =
                    $this->user()?->province_id;

                if (
                    ! $provinceId
                    || (int) $allocation->province_id
                        !== (int) $provinceId
                ) {
                    $validator->errors()->add(
                        'province_distribution',
                        'You cannot receive another province’s allocation.'
                    );

                    return;
                }

                if (
                    $allocation
                        ->distributionBatch
                        ?->callOff
                        ?->status !== 'Approved'
                ) {
                    $validator->errors()->add(
                        'province_distribution',
                        'The Call-Off must be approved before receiving PPE.'
                    );

                    return;
                }

                if (! $allocation->canBeReceived()) {
                    $validator->errors()->add(
                        'province_distribution',
                        'This allocation is no longer available for receiving.'
                    );

                    return;
                }

                $submittedItems = $this->input(
                    'items',
                    []
                );

                if (! is_array($submittedItems)) {
                    return;
                }

                $allocationItemIds = $allocation
                    ->items
                    ->pluck('id')
                    ->map(
                        fn ($id): int => (int) $id
                    )
                    ->values()
                    ->all();

                /*
                 * Sum quantities from all previous DRs under this allocation.
                 */
                $previouslyReceived = DeliveryReceiptItem::query()
                    ->whereIn(
                        'province_distribution_item_id',
                        $allocationItemIds
                    )
                    ->whereHas(
                        'deliveryReceipt',
                        fn ($query) => $query->where(
                            'province_distribution_id',
                            $allocation->id
                        )
                    )
                    ->selectRaw(
                        '
                        province_distribution_item_id,
                        SUM(received_quantity) AS total_received
                        '
                    )
                    ->groupBy(
                        'province_distribution_item_id'
                    )
                    ->pluck(
                        'total_received',
                        'province_distribution_item_id'
                    );

                $wholeReceiptTotal = 0;

                foreach (
                    $allocation->items as $allocationItem
                ) {
                    $field =
                        "items.{$allocationItem->id}";

                    if (
                        ! array_key_exists(
                            $allocationItem->id,
                            $submittedItems
                        )
                    ) {
                        $validator->errors()->add(
                            $field,
                            'Enter the quantity received for this PPE item.'
                        );

                        continue;
                    }

                    $submittedQuantity =
                        $submittedItems[
                            $allocationItem->id
                        ];

                    if (
                        filter_var(
                            $submittedQuantity,
                            FILTER_VALIDATE_INT
                        ) === false
                    ) {
                        continue;
                    }

                    $submittedQuantity =
                        (int) $submittedQuantity;

                    if ($submittedQuantity < 0) {
                        continue;
                    }

                    $wholeReceiptTotal +=
                        $submittedQuantity;

                    $alreadyReceived = (int) (
                        $previouslyReceived[
                            $allocationItem->id
                        ] ?? 0
                    );

                    $remainingReceivable = max(
                        0,
                        (int) $allocationItem->quantity
                            - $alreadyReceived
                    );

                    if (
                        $submittedQuantity
                        > $remainingReceivable
                    ) {
                        $itemName =
                            $allocationItem
                                ->item
                                ?->item_name
                            ?? 'PPE item';

                        $label =
                            $allocationItem
                                ->item
                                ?->label;

                        $displayName = $label
                            ? "{$itemName} ({$label})"
                            : $itemName;

                        $validator->errors()->add(
                            $field,
                            "{$displayName} has only "
                            .number_format(
                                $remainingReceivable
                            )
                            .' remaining to receive. '
                            .number_format(
                                $alreadyReceived
                            )
                            .' has already been recorded from previous Delivery Receipts.'
                        );
                    }
                }

                foreach (
                    array_keys($submittedItems) as $submittedId
                ) {
                    if (
                        ! in_array(
                            (int) $submittedId,
                            $allocationItemIds,
                            true
                        )
                    ) {
                        $validator->errors()->add(
                            "items.{$submittedId}",
                            'One submitted PPE item does not belong to this allocation.'
                        );
                    }
                }

                if ($wholeReceiptTotal <= 0) {
                    $validator->errors()->add(
                        'items',
                        'Enter at least one received PPE quantity greater than zero.'
                    );
                }
            }
        );
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input(
            'items',
            []
        );

        $normalizedItems = [];

        if (is_array($items)) {
            foreach (
                $items as $itemId => $quantity
            ) {
                $normalizedItems[$itemId] =
                    $quantity === null
                    || $quantity === ''
                        ? 0
                        : $quantity;
            }
        }

        $this->merge([
            'dr_number' => strtoupper(
                trim(
                    (string) $this->input(
                        'dr_number'
                    )
                )
            ),

            'physical_receiver_name' => trim(
                (string) $this->input(
                    'physical_receiver_name'
                )
            ),

            'remarks' => $this->filled(
                'remarks'
            )
                ? trim(
                    (string) $this->input(
                        'remarks'
                    )
                )
                : null,

            'items' => $normalizedItems,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dr_number.required' => 'Enter the Delivery Receipt Number.',

            'dr_number.unique' => 'The Delivery Receipt Number has already been used.',

            'delivery_date.required' => 'Provide the actual delivery date.',

            'delivery_date.date' => 'Provide a valid actual delivery date.',

            'physical_receiver_name.required' => 'Enter the name of the physical receiver.',

            'document.required' => 'Upload the Delivery Receipt PDF.',

            'document.file' => 'The Delivery Receipt document must be a valid file.',

            'document.mimes' => 'The Delivery Receipt document must be a PDF file.',

            'document.mimetypes' => 'The uploaded document must contain valid PDF data.',

            'document.max' => 'The Delivery Receipt PDF must not exceed 10 MB.',

            'items.required' => 'Enter the received PPE quantities.',

            'items.array' => 'The submitted PPE quantities are invalid.',

            'items.*.required' => 'A received quantity is required for every PPE item.',

            'items.*.integer' => 'Every received quantity must be a whole number.',

            'items.*.min' => 'Received quantities cannot be negative.',
        ];
    }
}
