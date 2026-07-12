<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProvincialOffice\StoreDeliveryReceiptRequest;
use App\Models\DeliveryReceipt;
use App\Models\DeliveryReceiptItem;
use App\Models\ProvinceDistribution;
use App\Services\ReceivingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReceivingController extends Controller
{
    /**
     * Display all approved Call-Off allocations assigned to the
     * authenticated Provincial Office.
     */
    public function index(): View
    {
        $provinceId = Auth::user()->province_id;

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        $allocations = ProvinceDistribution::query()
            ->with([
                'distributionBatch.callOff',
                'distributionBatch.purchaseOrder.supplier',
                'province',
                'items.item',
                'deliveryReceipts.items.item',
            ])
            ->where(
                'province_id',
                $provinceId
            )
            ->whereHas(
                'distributionBatch.callOff',
                fn ($query) => $query->whereIn(
                    'status',
                    [
                        'Approved',
                        'Completed',
                    ]
                )
            )
            ->latest('scheduled_delivery_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view(
            'provincial.receiving.index',
            compact('allocations')
        );
    }

    /**
     * Display one Call-Off allocation and all Delivery Receipts
     * recorded under it.
     */
    public function show(
        ProvinceDistribution $provinceDistribution
    ): View {
        $this->ensureProvinceAccess(
            $provinceDistribution
        );

        $provinceDistribution->load([
            'distributionBatch.callOff.assignedBy',
            'distributionBatch.callOff.approvedBy',
            'distributionBatch.purchaseOrder.supplier',
            'province',
            'items.item',

            'deliveryReceipts' => fn ($query) => $query
                ->with([
                    'items.item',
                    'receivedByUser',
                ])
                ->orderBy('delivery_date')
                ->orderBy('id'),
        ]);

        $previouslyReceivedByItem =
            $this->receiptTotalsByAllocationItem(
                $provinceDistribution
            );

        $remainingByItem =
            $this->buildRemainingQuantities(
                $provinceDistribution,
                $previouslyReceivedByItem
            );

        return view(
            'provincial.receiving.show',
            compact(
                'provinceDistribution',
                'previouslyReceivedByItem',
                'remainingByItem'
            )
        );
    }

    /**
     * Display the form for recording another physical delivery.
     */
    public function create(
        ProvinceDistribution $provinceDistribution
    ): View|RedirectResponse {
        $this->ensureProvinceAccess(
            $provinceDistribution
        );

        $provinceDistribution->load([
            'distributionBatch.callOff',
            'distributionBatch.purchaseOrder.supplier',
            'province',
            'items.item',

            'deliveryReceipts' => fn ($query) => $query
                ->with([
                    'items.item',
                    'receivedByUser',
                ])
                ->orderBy('delivery_date')
                ->orderBy('id'),
        ]);

        abort_unless(
            $provinceDistribution
                ->distributionBatch
                ?->callOff
                ?->status === 'Approved',
            403,
            'The Call-Off must be approved before receiving.'
        );

        abort_unless(
            $provinceDistribution->canBeReceived(),
            403,
            'This allocation is not available for receiving.'
        );

        $previouslyReceivedByItem =
            $this->receiptTotalsByAllocationItem(
                $provinceDistribution
            );

        $remainingByItem =
            $this->buildRemainingQuantities(
                $provinceDistribution,
                $previouslyReceivedByItem
            );

        $hasRemainingItems = collect(
            $remainingByItem
        )->contains(
            fn (int $quantity): bool =>
                $quantity > 0
        );

        if (! $hasRemainingItems) {
            return redirect()
                ->route(
                    'provincial.receiving.show',
                    $provinceDistribution
                )
                ->with(
                    'error',
                    'The complete Call-Off allocation has already been received.'
                );
        }

        return view(
            'provincial.receiving.create',
            compact(
                'provinceDistribution',
                'previouslyReceivedByItem',
                'remainingByItem'
            )
        );
    }

    /**
     * Store one Delivery Receipt under the selected allocation.
     */
    public function store(
        StoreDeliveryReceiptRequest $request,
        ProvinceDistribution $provinceDistribution,
        ReceivingService $receivingService
    ): RedirectResponse {
        $this->ensureProvinceAccess(
            $provinceDistribution
        );

        $document = $request->file(
            'document'
        );

        abort_unless(
            $document,
            422,
            'The Delivery Receipt PDF is required.'
        );

        $receipt = $receivingService->receive(
            $provinceDistribution,
            $request->validated(),
            $document
        );

        return redirect()
            ->route(
                'provincial.receiving.show',
                $receipt->provinceDistribution
            )
            ->with(
                'success',
                'Delivery Receipt '
                .$receipt->dr_number
                .' was submitted successfully. Provincial inventory and '
                .'the remaining Call-Off quantities were updated.'
            );
    }

    /**
     * Display all individual Delivery Receipts for the authenticated
     * Provincial Office.
     */
    public function history(): View
    {
        $provinceId = Auth::user()->province_id;

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        $receipts = DeliveryReceipt::query()
            ->with([
                'provinceDistribution.distributionBatch.callOff',
                'provinceDistribution.distributionBatch.purchaseOrder.supplier',
                'province',
                'receivedByUser',
                'items.item',
            ])
            ->where(
                'province_id',
                $provinceId
            )
            ->latest('delivery_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view(
            'provincial.receiving.history',
            compact('receipts')
        );
    }

    /**
     * Prevent a Provincial Office from accessing another province.
     */
    private function ensureProvinceAccess(
        ProvinceDistribution $provinceDistribution
    ): void {
        $provinceId =
            Auth::user()->province_id;

        abort_unless(
            $provinceId
            && (int) $provinceDistribution->province_id
                === (int) $provinceId,
            403,
            'You cannot access another province’s allocation.'
        );
    }

    /**
     * Calculate cumulative received quantities from every previous
     * Delivery Receipt under the same Province Distribution.
     *
     * @return array<int, int>
     */
    private function receiptTotalsByAllocationItem(
        ProvinceDistribution $provinceDistribution
    ): array {
        $allocationItemIds =
            $provinceDistribution
                ->items
                ->pluck('id')
                ->map(
                    fn ($id): int =>
                        (int) $id
                )
                ->values()
                ->all();

        if ($allocationItemIds === []) {
            return [];
        }

        return DeliveryReceiptItem::query()
            ->whereIn(
                'province_distribution_item_id',
                $allocationItemIds
            )
            ->whereHas(
                'deliveryReceipt',
                fn ($query) => $query->where(
                    'province_distribution_id',
                    $provinceDistribution->id
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
            )
            ->map(
                fn ($quantity): int =>
                    (int) $quantity
            )
            ->all();
    }

    /**
     * Calculate the quantity still receivable for every allocation item.
     *
     * @param  array<int, int>  $previouslyReceivedByItem
     * @return array<int, int>
     */
    private function buildRemainingQuantities(
        ProvinceDistribution $provinceDistribution,
        array $previouslyReceivedByItem
    ): array {
        $remainingByItem = [];

        foreach (
            $provinceDistribution->items
            as $allocationItem
        ) {
            $previouslyReceived = (int) (
                $previouslyReceivedByItem[
                    $allocationItem->id
                ] ?? 0
            );

            $remainingByItem[
                $allocationItem->id
            ] = max(
                0,
                (int) $allocationItem->quantity
                    - $previouslyReceived
            );
        }

        return $remainingByItem;
    }
}