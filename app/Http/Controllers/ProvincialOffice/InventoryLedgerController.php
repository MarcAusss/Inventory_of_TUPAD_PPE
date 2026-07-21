<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Models\ProvinceDistribution;
use App\Services\InventoryMovementReportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryLedgerController extends Controller
{
    public function index(Request $request, InventoryMovementReportService $reportService): View
    {
        $provinceId = $this->provinceId();
        $year = $this->resolveYear($request);
        $search = trim((string) $request->query('search', ''));
        $callOffId = max(0, (int) $request->query('province_distribution_id', 0));
        $callOffAllocations = $this->callOffAllocations($provinceId, $year, $search);
        $reportRows = collect();
        $selectedDeliveryReceipt = null;

        if ($callOffId > 0) {
            $allocation = $this->findAllocation($provinceId, $callOffId);
            $selectedDeliveryReceipt = $allocation->deliveryReceipts
                ->where('status', 'Received')
                ->sortByDesc(fn ($receipt): string => ($receipt->delivery_date?->format('Y-m-d') ?? '0000-00-00').'|'.str_pad((string) $receipt->id, 20, '0', STR_PAD_LEFT))
                ->first();
            $reportRows = $reportService->buildForCallOff($provinceId, $callOffId);
        }

        $summary = $this->buildSummary($reportRows);
        $availableYears = $this->availableYears($provinceId, $year);
        $rows = $this->paginate($reportRows, 10, $request, 'page');

        return view('provincial.inventory-ledger.index', compact(
            'rows', 'summary', 'year', 'availableYears', 'search', 'callOffId',
            'callOffAllocations', 'selectedDeliveryReceipt'
        ));
    }

    public function print(Request $request, InventoryMovementReportService $reportService): View
    {
        $provinceId = $this->provinceId();
        $callOffId = max(0, (int) $request->query('province_distribution_id', 0));
        abort_if($callOffId <= 0, 422, 'Select a Call-Off before printing the inventory ledger.');
        $year = $this->resolveYear($request);
        $allocation = $this->findAllocation($provinceId, $callOffId);
        $selectedDeliveryReceipt = $allocation->deliveryReceipts
            ->where('status', 'Received')
            ->sortByDesc(fn ($receipt): string => ($receipt->delivery_date?->format('Y-m-d') ?? '0000-00-00').'|'.str_pad((string) $receipt->id, 20, '0', STR_PAD_LEFT))
            ->firstOrFail();
        $rows = $reportService->buildForCallOff($provinceId, $callOffId);
        $summary = $this->buildSummary($rows);
        $user = Auth::user();
        $provinceName = $user?->province?->name ?? $allocation->province?->name ?? 'Provincial Office';
        $preparedBy = $user?->name ?? '';
        $reviewedBy = '';
        $printedAt = now();

        return view('provincial.inventory-ledger.print', compact(
            'rows', 'summary', 'year', 'selectedDeliveryReceipt', 'provinceName',
            'preparedBy', 'reviewedBy', 'printedAt', 'callOffId'
        ));
    }

    private function findAllocation(int $provinceId, int $id): ProvinceDistribution
    {
        return ProvinceDistribution::query()
            ->with(['province', 'deliveryReceipts.items.item', 'distributionBatch.callOff', 'distributionBatch.purchaseOrder.supplier'])
            ->where('province_id', $provinceId)
            ->whereHas('distributionBatch.callOff', fn ($q) => $q->whereIn('status', ['Approved', 'Completed']))
            ->findOrFail($id);
    }

    private function callOffAllocations(int $provinceId, int $year, string $search): Collection
    {
        return ProvinceDistribution::query()
            ->with(['deliveryReceipts', 'distributionBatch.callOff', 'distributionBatch.purchaseOrder.supplier'])
            ->where('province_id', $provinceId)
            ->whereHas('deliveryReceipts', fn ($q) => $q->where('status', 'Received')->whereYear('delivery_date', $year))
            ->whereHas('distributionBatch.callOff', function ($q) use ($search): void {
                $q->whereIn('status', ['Approved', 'Completed']);
                if ($search !== '') $q->where('call_off_number', 'like', "%{$search}%");
            })
            ->orderByDesc('id')->get();
    }

    private function availableYears(int $provinceId, int $selectedYear): Collection
    {
        $years = \App\Models\DeliveryReceipt::query()->where('province_id', $provinceId)
            ->where('status', 'Received')->whereNotNull('delivery_date')
            ->selectRaw('YEAR(delivery_date) AS report_year')->distinct()->orderByDesc('report_year')
            ->pluck('report_year')->map(fn ($year): int => (int) $year)->values();
        if (! $years->contains($selectedYear)) $years->prepend($selectedYear);
        return $years;
    }

    private function resolveYear(Request $request): int
    {
        $year = (int) $request->query('year', now()->year);
        return $year >= 2000 && $year <= 2100 ? $year : (int) now()->year;
    }

    private function buildSummary(Collection $rows): array
    {
        return [
            'row_count' => $rows->count(),
            'call_off_count' => $rows->pluck('province_distribution_id')->filter()->unique()->count(),
            'project_count' => $rows->pluck('supply_designation_id')->filter()->unique()->count(),
            'beginning_total' => (int) ($rows->first()['beginning_total'] ?? 0),
            'actual_total' => (int) $rows->sum('actual_total'),
            'ending_total' => (int) ($rows->last()['ending_total'] ?? 0),
        ];
    }

    private function provinceId(): int
    {
        $provinceId = Auth::user()?->province_id;
        abort_unless($provinceId, 403, 'This Provincial Office account has no assigned province.');
        return (int) $provinceId;
    }

    private function paginate(Collection $items, int $perPage, Request $request, string $pageName): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        return new LengthAwarePaginator($items->forPage($page, $perPage)->values(), $items->count(), $perPage, $page, [
            'path' => $request->url(), 'query' => $request->query(), 'pageName' => $pageName,
        ]);
    }
}
