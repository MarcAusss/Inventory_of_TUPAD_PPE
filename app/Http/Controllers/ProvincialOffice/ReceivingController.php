<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProvincialOffice\StoreDeliveryReceiptRequest;
use App\Models\DeliveryReceipt;
use App\Models\ProvinceDistribution;
use App\Services\ReceivingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReceivingController extends Controller
{
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
                'deliveryReceipt',
            ])
            ->where('province_id', $provinceId)
            ->whereHas(
                'distributionBatch.callOff',
                fn ($query) => $query->where(
                    'status',
                    'Approved'
                )
            )
            ->latest('scheduled_delivery_date')
            ->paginate(10);

        return view(
            'provincial.receiving.index',
            compact('allocations')
        );
    }

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
            'deliveryReceipt.items.item',
        ]);

        return view(
            'provincial.receiving.show',
            compact('provinceDistribution')
        );
    }

    public function create(
        ProvinceDistribution $provinceDistribution
    ): View {
        $this->ensureProvinceAccess(
            $provinceDistribution
        );

        $provinceDistribution->load([
            'distributionBatch.callOff',
            'distributionBatch.purchaseOrder.supplier',
            'province',
            'items.item',
            'deliveryReceipt',
        ]);

        abort_unless(
            $provinceDistribution
                ->distributionBatch
                ?->callOff
                ?->status === 'Approved',
            403,
            'The Call-Off must be approved before receiving.'
        );

        if ($provinceDistribution->deliveryReceipt) {
            return redirect()
                ->route(
                    'provincial.receiving.show',
                    $provinceDistribution
                )
                ->with(
                    'error',
                    'This allocation has already been received.'
                );
        }

        abort_unless(
            $provinceDistribution->canBeReceived(),
            403,
            'This allocation is not available for receiving.'
        );

        return view(
            'provincial.receiving.create',
            compact('provinceDistribution')
        );
    }

    public function store(
        StoreDeliveryReceiptRequest $request,
        ProvinceDistribution $provinceDistribution,
        ReceivingService $receivingService
    ): RedirectResponse {
        $this->ensureProvinceAccess(
            $provinceDistribution
        );

        $receipt = $receivingService->receive(
            $provinceDistribution,
            $request->validated(),
            $request->file('document')
        );

        return redirect()
            ->route(
                'provincial.receiving.show',
                $receipt->provinceDistribution
            )
            ->with(
                'success',
                'Delivery Receipt submitted and provincial inventory updated successfully.'
            );
    }

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
            ->where('province_id', $provinceId)
            ->latest('delivery_date')
            ->paginate(10);

        return view(
            'provincial.receiving.history',
            compact('receipts')
        );
    }

    private function ensureProvinceAccess(
        ProvinceDistribution $provinceDistribution
    ): void {
        $provinceId = Auth::user()->province_id;

        abort_unless(
            $provinceId
            && (int) $provinceDistribution->province_id
                === (int) $provinceId,
            403,
            'You cannot access another province’s allocation.'
        );
    }
}
