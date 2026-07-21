<?php

namespace App\Services;

use App\Models\DeliveryReceipt;
use App\Models\ProvinceDistribution;
use App\Models\SupplyDesignation;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class InventoryMovementReportService
{
    /**
     * Build inventory movement rows for every Call-Off allocation belonging
     * to one province. This is used by the read-only Accounting and TSSD
     * monitoring pages.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function buildForProvince(int $provinceId): Collection
    {
        $allocationIds = ProvinceDistribution::query()
            ->where('province_id', $provinceId)
            ->whereHas('deliveryReceipts', function ($query): void {
                $query->where('status', 'Received');
            })
            ->whereHas('distributionBatch.callOff', function ($query): void {
                $query->whereIn('status', [
                    'Approved',
                    'Completed',
                ]);
            })
            ->orderBy('scheduled_delivery_date')
            ->orderBy('id')
            ->pluck('id');

        $rows = collect();

        foreach ($allocationIds as $allocationId) {
            $rows = $rows->concat(
                $this->buildForCallOff(
                    $provinceId,
                    (int) $allocationId
                )
            );
        }

        return $rows->values();
    }

    /**
     * Build the full inventory history for one Provincial Call-Off allocation.
     *
     * Every received Delivery Receipt remains visible in chronological order.
     * Project distributions linked to each receipt are shown beneath that
     * receipt. The ending balance of one project/receipt becomes the running
     * balance used by the next row. When a new receipt is encountered, its
     * received quantities are added to the previous ending balance to create
     * that receipt's beginning inventory.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function buildForCallOff(
        int $provinceId,
        int $provinceDistributionId
    ): Collection {
        $allocation = ProvinceDistribution::query()
            ->with([
                'province',
                'items.item',
                'distributionBatch.callOff',
                'distributionBatch.purchaseOrder.supplier',
                'deliveryReceipts.items.item',
                'deliveryReceipts.receivedByUser',
                'supplyDesignations.items.item',
            ])
            ->where('province_id', $provinceId)
            ->whereKey($provinceDistributionId)
            ->firstOrFail();

        $receipts = $allocation->deliveryReceipts
            ->where('status', 'Received')
            ->sortBy(fn(DeliveryReceipt $receipt): string => $this->receiptKey($receipt))
            ->values();

        if ($receipts->isEmpty()) {
            return collect();
        }

        $completedDesignations = $allocation->supplyDesignations
            ->where('status', 'Completed')
            ->sortBy(fn(SupplyDesignation $designation): string => $this->designationKey($designation))
            ->values();

        $designationsByReceipt = $this->groupDesignationsByReceipt(
            $receipts,
            $completedDesignations
        );

        $rows = collect();
        $runningBalance = $this->emptyQuantities($allocation);

        foreach ($receipts as $receipt) {
            /*
             * Previous ending + quantities received in this DR = beginning
             * inventory for this receipt cycle.
             */
            $runningBalance = $this->add(
                $runningBalance,
                $this->receiptQuantities($receipt)
            );

            /** @var Collection<int, SupplyDesignation> $receiptDesignations */
            $receiptDesignations = $designationsByReceipt->get(
                (int) $receipt->id,
                collect()
            );

            /*
             * Keep the receipt visible even when it has no project yet.
             */
            if ($receiptDesignations->isEmpty()) {
                $rows->push(
                    $this->row(
                        $allocation,
                        $receipt,
                        null,
                        $runningBalance,
                        $this->zeros($runningBalance),
                        $runningBalance
                    )
                );

                continue;
            }

            foreach ($receiptDesignations as $designation) {
                $beginning = $runningBalance;
                $actual = $this->designationQuantities($designation);
                $ending = $this->subtract($beginning, $actual);

                $rows->push(
                    $this->row(
                        $allocation,
                        $receipt,
                        $designation,
                        $beginning,
                        $actual,
                        $ending
                    )
                );

                $runningBalance = $ending;
            }
        }

        return $rows->values();
    }

    /**
     * Group completed project distributions under the Delivery Receipt they
     * belong to. New records use delivery_receipt_id directly. Legacy records
     * with no receipt ID are assigned to the latest receipt whose delivery date
     * is not later than the project's designation date.
     *
     * @param  Collection<int, DeliveryReceipt>  $receipts
     * @param  Collection<int, SupplyDesignation>  $designations
     * @return Collection<int, Collection<int, SupplyDesignation>>
     */
    private function groupDesignationsByReceipt(
        Collection $receipts,
        Collection $designations
    ): Collection {
        $receiptIds = $receipts
            ->pluck('id')
            ->map(fn($id): int => (int) $id)
            ->all();

        $groups = collect();

        foreach ($receipts as $receipt) {
            $groups->put((int) $receipt->id, collect());
        }

        foreach ($designations as $designation) {
            $receiptId = $designation->delivery_receipt_id !== null
                ? (int) $designation->delivery_receipt_id
                : null;

            if ($receiptId !== null && in_array($receiptId, $receiptIds, true)) {
                $groups->get($receiptId)->push($designation);
                continue;
            }

            /*
             * Compatibility for old project records that do not contain a
             * delivery_receipt_id. Match them to the most recent DR on or
             * before the project distribution date.
             */
            $matchedReceipt = $receipts
                ->filter(
                    fn(DeliveryReceipt $receipt): bool =>
                    $this->receiptDate($receipt)
                    <= $this->designationDate($designation)
                )
                ->last();

            /*
             * If the legacy project predates every receipt, attach it to the
             * first receipt rather than losing it from the report.
             */
            $matchedReceipt ??= $receipts->first();

            if ($matchedReceipt) {
                $groups
                    ->get((int) $matchedReceipt->id)
                    ->push($designation);
            }
        }

        return $groups->map(
            fn(Collection $group): Collection => $group
                ->sortBy(fn(SupplyDesignation $designation): string => $this->designationKey($designation))
                ->values()
        );
    }

    /**
     * @param  array<int, int>  $beginning
     * @param  array<int, int>  $actual
     * @param  array<int, int>  $ending
     * @return array<string, mixed>
     */
    private function row(
        ProvinceDistribution $allocation,
        DeliveryReceipt $receipt,
        ?SupplyDesignation $designation,
        array $beginning,
        array $actual,
        array $ending
    ): array {
        $callOff = $allocation->distributionBatch?->callOff;
        $purchaseOrder = $allocation->distributionBatch?->purchaseOrder;

        return [
            'province_distribution_id' => (int) $allocation->id,
            'province_id' => (int) $allocation->province_id,
            'province_name' => $allocation->province?->name ?? '—',
            'delivery_receipt_id' => (int) $receipt->id,
            'delivery_receipt_number' => $receipt->dr_number ?? '—',
            'delivery_date' => $receipt->delivery_date,
            'call_off_number' => $callOff?->call_off_number ?? '—',
            'purchase_order_number' => $purchaseOrder?->po_number ?? '—',
            'supplier_name' => $purchaseOrder?->supplier?->supplier_name ?? '—',
            'supply_designation_id' => $designation?->id,
            'project_code' => $designation?->project_code ?? '—',
            'project_title' => $designation?->project_title
                ?? $designation?->project_name
                ?? 'No Project Distribution Yet',
            'location' => $designation?->location ?? '—',
            'number_of_beneficiaries' => (int) (
                $designation?->number_of_beneficiaries ?? 0
            ),
            'number_of_days' => (int) (
                $designation?->number_of_days ?? 0
            ),
            'movement_date' => $designation?->designation_date
                ?? $receipt->delivery_date,
            'beginning' => $beginning,
            'actual' => $actual,
            'ending' => $ending,
            'beginning_total' => array_sum($beginning),
            'actual_total' => array_sum($actual),
            'ending_total' => array_sum($ending),
            'receipt' => $receipt,
            'designation' => $designation,
            'allocation' => $allocation,
        ];
    }

    /** @return array<int, int> */
    private function emptyQuantities(
        ProvinceDistribution $allocation
    ): array {
        $values = [];

        foreach ($allocation->items as $item) {
            $values[(int) $item->item_id] = 0;
        }

        return $values;
    }

    /**
     * @param  array<int, int>  $values
     * @return array<int, int>
     */
    private function zeros(array $values): array
    {
        return array_fill_keys(array_keys($values), 0);
    }

    /** @return array<int, int> */
    private function receiptQuantities(
        DeliveryReceipt $receipt
    ): array {
        $values = [];

        foreach ($receipt->items as $item) {
            $itemId = (int) $item->item_id;

            $values[$itemId] = ($values[$itemId] ?? 0)
                + (int) (
                    $item->received_quantity
                    ?? $item->quantity
                    ?? 0
                );
        }

        return $values;
    }

    /** @return array<int, int> */
    private function designationQuantities(
        SupplyDesignation $designation
    ): array {
        $values = [];

        foreach ($designation->items as $item) {
            $itemId = (int) $item->item_id;

            $values[$itemId] = ($values[$itemId] ?? 0)
                + (int) $item->quantity;
        }

        return $values;
    }

    /**
     * @param  array<int, int>  $left
     * @param  array<int, int>  $right
     * @return array<int, int>
     */
    private function add(array $left, array $right): array
    {
        foreach ($right as $itemId => $quantity) {
            $left[(int) $itemId] = ($left[(int) $itemId] ?? 0)
                + (int) $quantity;
        }

        return $left;
    }

    /**
     * @param  array<int, int>  $left
     * @param  array<int, int>  $right
     * @return array<int, int>
     */
    private function subtract(array $left, array $right): array
    {
        foreach ($right as $itemId => $quantity) {
            $left[(int) $itemId] = max(
                0,
                ($left[(int) $itemId] ?? 0) - (int) $quantity
            );
        }

        return $left;
    }

    private function receiptKey(DeliveryReceipt $receipt): string
    {
        return $this->receiptDate($receipt)
            . '|'
            . str_pad((string) $receipt->id, 20, '0', STR_PAD_LEFT);
    }

    private function designationKey(
        SupplyDesignation $designation
    ): string {
        return $this->designationDate($designation)
            . '|'
            . str_pad((string) $designation->id, 20, '0', STR_PAD_LEFT);
    }


    private function receiptDate(DeliveryReceipt $receipt): string
    {
        if ($receipt->delivery_date) {
            return Carbon::parse($receipt->delivery_date)
                ->format('Y-m-d');
        }

        return $receipt->created_at
            ? $receipt->created_at->format('Y-m-d')
            : '0000-00-00';
    }

    private function designationDate(
        SupplyDesignation $designation
    ): string {
        if ($designation->designation_date) {
            return Carbon::parse($designation->designation_date)
                ->format('Y-m-d');
        }

        return $designation->created_at
            ? $designation->created_at->format('Y-m-d')
            : '0000-00-00';
    }
}