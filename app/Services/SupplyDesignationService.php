<?php

namespace App\Services;

use App\Models\ProvincialInventory;
use App\Models\SupplyDesignation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplyDesignationService extends BaseService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(
        array $data,
        UploadedFile $areDocument
    ): SupplyDesignation {
        $this->requireProvincial();

        $provinceId = $this->provinceId();

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        return DB::transaction(function () use (
            $data,
            $areDocument,
            $provinceId
        ): SupplyDesignation {
            $inventories = ProvincialInventory::query()
                ->with('item')
                ->where('province_id', $provinceId)
                ->whereIn(
                    'item_id',
                    array_map(
                        'intval',
                        array_keys($data['items'])
                    )
                )
                ->lockForUpdate()
                ->get()
                ->keyBy('item_id');

            $this->validateRequestedItems(
                $inventories,
                $data['items']
            );

            $documentPath = $areDocument->store(
                'are-documents',
                'public'
            );

            $designation = SupplyDesignation::create([
                'delivery_receipt_id' => null,
                'province_id' => $provinceId,
                'created_by' => $this->userId(),

                /*
                 * Keep legacy fields synchronized.
                 */
                'designation_number' => $data['project_code'],
                'project_name' => $data['project_title'],

                'designation_date' => $data['designation_date'],
                'project_code' => $data['project_code'],
                'project_title' => $data['project_title'],
                'location' => $data['location'],
                'number_of_days' => $data['number_of_days'],
                'number_of_beneficiaries' => $data['number_of_beneficiaries'],
                'are_document' => $documentPath,
                'status' => 'Completed',
                'submitted_at' => now(),
                'remarks' => $data['remarks'] ?? null,
            ]);

            foreach ($data['items'] as $itemId => $quantity) {
                $quantity = (int) $quantity;

                if ($quantity <= 0) {
                    continue;
                }

                $inventory = $inventories->get(
                    (int) $itemId
                );

                $designation->items()->create([
                    'item_id' => (int) $itemId,
                    'quantity' => $quantity,
                ]);

                $inventory->decrement(
                    'quantity',
                    $quantity
                );
            }

            return $designation->load([
                'province',
                'creator',
                'items.item',
            ]);
        });
    }

    /**
     * @param  mixed  $inventories
     * @param  array<int|string, mixed>  $submittedItems
     */
    private function validateRequestedItems(
        $inventories,
        array $submittedItems
    ): void {
        $errors = [];

        foreach ($submittedItems as $itemId => $quantity) {
            $itemId = (int) $itemId;
            $quantity = (int) $quantity;

            if ($quantity <= 0) {
                continue;
            }

            $inventory = $inventories->get($itemId);

            if (! $inventory) {
                $errors[
                    "items.{$itemId}"
                ] = 'This PPE item is not available in your provincial inventory.';

                continue;
            }

            if ($quantity > $inventory->quantity) {
                $itemName = $inventory
                    ->item
                    ?->item_name
                    ?? 'PPE item';

                $label = $inventory
                    ->item
                    ?->label;

                $displayName = $label
                    ? "{$itemName} ({$label})"
                    : $itemName;

                $errors[
                    "items.{$itemId}"
                ] = "{$displayName} has only {$inventory->quantity} available, but {$quantity} was requested.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }
}
