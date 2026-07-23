<x-po_dashboard_layout title="Supply Unit Dashboard">

    @php
        $purchaseComposition = $charts['ppePurchaseComposition'] ?? [
            'labels' => [],
            'data' => [],
        ];

        $poStatusChart = $charts['poDistributionStatus'] ?? [
            'labels' => [],
            'data' => [],
        ];

        $monthlyPurchaseOrders = $charts['monthlyPurchaseOrders'] ?? [
            'labels' => [],
            'counts' => [],
            'values' => [],
        ];

        $latestPurchaseOrders = $recentActivities['latestPurchaseOrders'] ?? collect();

        $pendingCallOffs = $recentActivities['pendingCallOffs'] ?? collect();

        $supplierSummary = $recentActivities['supplierSummary'] ?? collect();

        $ppeStockSummary = $recentActivities['ppeStockSummary'] ?? collect();
    @endphp

    <div class="mx-auto max-w-[1800px] space-y-6">

        {{-- Header --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#2D94BE] to-[#339DCB]">
            </div>

            <div class="flex flex-col gap-5 px-7 py-7 lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <div class="flex flex-wrap items-center gap-3">

                        <span
                            class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-800 ring-1 ring-emerald-200">
                            Supply Unit
                        </span>

                        <span
                            class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-200">
                            Procurement Monitoring
                        </span>

                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        Purchase Order and Supply Dashboard
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Monitor suppliers, Purchase Orders, purchased PPE,
                        distribution balances, and pending Call-Off approvals.
                    </p>

                </div>

                <div class="grid grid-cols-2 gap-3 sm:flex sm:items-center">

                    <a href="{{ route('supply.purchase-orders.create') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-[#339DCB] px-5 py-3 text-sm font-bold text-white transition hover:hover:bg-[#2D94BE]">
                        New Purchase Order
                    </a>

                    <a href="{{ route('supply.call-offs.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        Review Call-Offs
                    </a>

                </div>

            </div>

        </section>

        {{-- Statistic cards --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8">

            @php
                $statCards = [
                    [
                        'label' => 'Active Suppliers',
                        'value' => $statistics['total_suppliers'] ?? 0,
                        'description' => 'Registered suppliers',
                        'style' => 'bg-[#B7D6E6]/35 text-[#143A52] ring-[#90C4DD]',
                    ],
                    [
                        'label' => 'Purchase Orders',
                        'value' => $statistics['total_purchase_orders'] ?? 0,
                        'description' => 'All procurement records',
                        'style' => 'bg-blue-50 text-blue-800 ring-blue-200',
                    ],
                    [
                        'label' => 'PO Total Value',
                        'value' => '₱' . number_format($statistics['total_po_value'] ?? 0, 2),
                        'description' => 'Total procurement amount',
                        'style' => 'bg-violet-50 text-violet-800 ring-violet-200',
                    ],
                    [
                        'label' => 'Pending Distribution',
                        'value' => $statistics['pending_distributions'] ?? 0,
                        'description' => 'POs awaiting allocation',
                        'style' => 'bg-red-50 text-red-800 ring-red-200',
                    ],
                    [
                        'label' => 'Distributed POs',
                        'value' => $statistics['distributed_purchase_orders'] ?? 0,
                        'description' => 'With TSSD distributions',
                        'style' => 'bg-cyan-50 text-cyan-800 ring-cyan-200',
                    ],
                    [
                        'label' => 'Pending Call-Offs',
                        'value' => $statistics['pending_calloff_approvals'] ?? 0,
                        'description' => 'Awaiting Supply review',
                        'style' => 'bg-orange-50 text-orange-800 ring-orange-200',
                    ],
                    [
                        'label' => 'Approved Call-Offs',
                        'value' => $statistics['approved_calloffs'] ?? 0,
                        'description' => 'Approved for delivery',
                        'style' => 'bg-green-50 text-green-800 ring-green-200',
                    ],
                    [
                        'label' => 'Purchased PPE',
                        'value' => $statistics['total_purchased_items'] ?? 0,
                        'description' => 'Total purchased quantity',
                        'style' => 'bg-slate-100 text-slate-800 ring-slate-200',
                    ],
                ];
            @endphp

            @foreach ($statCards as $card)
                <article
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">

                    <span
                        class="inline-flex rounded-lg px-3 py-1.5 text-[11px] font-bold uppercase tracking-wide ring-1 {{ $card['style'] }}">
                        {{ $card['label'] }}
                    </span>

                    <p class="mt-4 break-words text-2xl font-bold text-slate-950 xl:text-3xl">
                        @if (is_numeric($card['value']))
                            {{ number_format($card['value']) }}
                        @else
                            {{ $card['value'] }}
                        @endif
                    </p>

                    <p class="mt-2 text-xs leading-5 text-slate-500">
                        {{ $card['description'] }}
                    </p>

                </article>
            @endforeach

        </section>

        {{-- Main charts --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Monthly PO chart --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-8">

                <div
                    class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <h2 class="text-lg font-bold text-slate-950">
                            Monthly Purchase Order Activity
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Purchase Order count and procurement value during
                            the latest six months.
                        </p>

                    </div>

                    <a href="{{ route('supply.purchase-orders.index') }}"
                        class="text-sm font-bold text-emerald-800 hover:text-emerald-950">
                        View Purchase Orders →
                    </a>

                </div>

                <div class="h-[420px] p-6">
                    <canvas id="monthlyPurchaseOrderChart"></canvas>
                </div>

            </article>

            {{-- PO status chart --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-4">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="text-lg font-bold text-slate-950">
                        Purchase Order Status
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Distribution progress of all Purchase Orders.
                    </p>

                </div>

                <div class="h-[420px] p-6">
                    <canvas id="purchaseOrderStatusChart"></canvas>
                </div>

            </article>

        </section>

        {{-- Composition and balance --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Purchased composition --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-4">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="text-lg font-bold text-slate-950">
                        Purchased PPE Composition
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Share of purchased quantities by PPE category.
                    </p>

                </div>

                <div class="h-[390px] p-6">
                    <canvas id="purchaseCompositionChart"></canvas>
                </div>

            </article>

            {{-- Purchased/distributed balance --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-8">

                <div
                    class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <h2 class="text-lg font-bold text-slate-950">
                            Purchased, Distributed and Remaining PPE
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Consolidated PPE quantities across all Purchase Orders.
                        </p>

                    </div>

                    <a href="{{ route('supply.items.index') }}"
                        class="text-sm font-bold text-emerald-800 hover:text-emerald-950">
                        Manage PPE Items →
                    </a>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-slate-200">

                        <thead class="bg-slate-50">

                            <tr class="text-xs font-bold uppercase tracking-wide text-slate-500">

                                <th class="px-6 py-4 text-left">
                                    PPE Item
                                </th>

                                <th class="px-4 py-4 text-left">
                                    Size / Label
                                </th>

                                <th class="px-4 py-4 text-center">
                                    Purchased
                                </th>

                                <th class="px-4 py-4 text-center">
                                    Distributed
                                </th>

                                <th class="px-6 py-4 text-center">
                                    Remaining
                                </th>

                                <th class="min-w-52 px-6 py-4 text-left">
                                    Distribution Progress
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @forelse($ppeStockSummary as $summary)
                                @php
                                    $distributionPercentage =
                                        $summary->purchased > 0
                                            ? min(100, round(($summary->distributed / $summary->purchased) * 100))
                                            : 0;
                                @endphp

                                <tr class="transition hover:bg-slate-50">

                                    <td class="px-6 py-4 font-semibold text-slate-900">
                                        {{ $summary->item->item_name }}
                                    </td>

                                    <td class="px-4 py-4 text-slate-600">
                                        {{ $summary->item->label ?: '—' }}
                                    </td>

                                    <td class="px-4 py-4 text-center font-semibold text-blue-700">
                                        {{ number_format($summary->purchased) }}
                                    </td>

                                    <td class="px-4 py-4 text-center font-semibold text-amber-700">
                                        {{ number_format($summary->distributed) }}
                                    </td>

                                    <td class="px-6 py-4 text-center font-bold text-emerald-800">
                                        {{ number_format($summary->remaining) }}
                                    </td>

                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-3">

                                            <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-200">

                                                <div class="h-full rounded-full bg-[#339DCB]"
                                                    style="width: {{ $distributionPercentage }}%"></div>

                                            </div>

                                            <span class="w-12 text-right text-sm font-bold text-slate-700">
                                                {{ $distributionPercentage }}%
                                            </span>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">
                                        No PPE stock summary is available.
                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </article>

        </section>

        {{-- Call-Offs and Suppliers --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Pending Call-Offs --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-7">

                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">

                    <div>

                        <h2 class="text-lg font-bold text-slate-950">
                            Pending Call-Off Approvals
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Call-Off requests requiring Supply Unit review.
                        </p>

                    </div>

                    <a href="{{ route('supply.call-offs.index') }}"
                        class="text-sm font-bold text-emerald-800 hover:text-emerald-950">
                        Review all
                    </a>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-slate-200">

                        <thead class="bg-slate-50">

                            <tr class="text-xs font-bold uppercase tracking-wide text-slate-500">

                                <th class="px-6 py-4 text-left">
                                    Call-Off
                                </th>

                                <th class="px-6 py-4 text-left">
                                    Purchase Order
                                </th>

                                <th class="px-4 py-4 text-center">
                                    Provinces
                                </th>

                                <th class="px-6 py-4 text-left">
                                    Assigned By
                                </th>

                                <th class="px-6 py-4 text-left">
                                    Date
                                </th>

                                <th class="px-6 py-4 text-center">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @forelse($pendingCallOffs as $callOff)
                                <tr class="transition hover:bg-slate-50">

                                    <td class="px-6 py-4 font-semibold text-slate-900">
                                        {{ $callOff->call_off_number }}
                                    </td>

                                    <td class="px-6 py-4">

                                        <p class="font-semibold text-slate-900">
                                            {{ $callOff->purchaseOrder?->po_number }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $callOff->purchaseOrder?->supplier?->supplier_name }}
                                        </p>

                                    </td>

                                    <td class="px-4 py-4 text-center font-semibold text-slate-700">
                                        {{ $callOff->distributionBatch?->provinceDistributions?->count() ?? 0 }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-600">
                                        {{ $callOff->assignedBy?->name ?? 'TSSD Unit' }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-600">
                                        {{ $callOff->assigned_at?->format('M d, Y') ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-center">

                                        <a href="{{ route('supply.call-offs.show', $callOff) }}"
                                            class="inline-flex rounded-lg bg-emerald-800 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-900">
                                            Review
                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">
                                        There are no pending Call-Off approvals.
                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </article>

            {{-- Supplier summary --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-5">

                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">

                    <div>

                        <h2 class="text-lg font-bold text-slate-950">
                            Top Suppliers
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Suppliers ranked by Purchase Order value.
                        </p>

                    </div>

                    <a href="{{ route('supply.suppliers.index') }}"
                        class="text-sm font-bold text-emerald-800 hover:text-emerald-950">
                        View suppliers
                    </a>

                </div>

                <div class="divide-y divide-slate-100">

                    @forelse($supplierSummary as $supplier)
                        <div class="flex items-center justify-between gap-5 px-6 py-4 transition hover:bg-slate-50">

                            <div class="min-w-0">

                                <p class="truncate font-semibold text-slate-900">
                                    {{ $supplier->supplier_name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ number_format($supplier->purchase_orders_count) }}
                                    Purchase Orders
                                </p>

                            </div>

                            <div class="shrink-0 text-right">

                                <p class="font-bold text-emerald-800">
                                    ₱{{ number_format($supplier->purchase_orders_sum_total_amount ?? 0, 2) }}
                                </p>

                                <span
                                    class="mt-1 inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-[#2D94BE] ring-1 ring-emerald-200">
                                    Active
                                </span>

                            </div>

                        </div>

                    @empty

                        <div class="px-6 py-12 text-center text-sm text-slate-500">
                            No supplier records are available.
                        </div>
                    @endforelse

                </div>

            </article>

        </section>

        {{-- Latest Purchase Orders --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div
                class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <h2 class="text-lg font-bold text-slate-950">
                        Latest Purchase Orders
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Recently created procurement records and their
                        distribution status.
                    </p>

                </div>

                <a href="{{ route('supply.purchase-orders.index') }}"
                    class="text-sm font-bold text-emerald-800 hover:text-emerald-950">
                    View all Purchase Orders →
                </a>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">

                        <tr class="text-xs font-bold uppercase tracking-wide text-slate-500">

                            <th class="px-6 py-4 text-left">
                                PO Number
                            </th>

                            <th class="px-6 py-4 text-left">
                                Supplier
                            </th>

                            <th class="px-6 py-4 text-left">
                                NEFA Number
                            </th>

                            <th class="px-4 py-4 text-center">
                                PPE Quantity
                            </th>

                            <th class="px-6 py-4 text-right">
                                Total Amount
                            </th>

                            <th class="px-6 py-4 text-left">
                                PO Date
                            </th>

                            <th class="px-6 py-4 text-center">
                                Status
                            </th>

                            <th class="px-6 py-4 text-center">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($latestPurchaseOrders as $purchaseOrder)
                            @php
                                $poStatusClass = match ($purchaseOrder->status) {
                                    'Distributed' => 'bg-blue-50 text-blue-700 ring-blue-200',

                                    'Completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',

                                    default => 'bg-amber-50 text-amber-700 ring-amber-200',
                                };
                            @endphp

                            <tr class="transition hover:bg-slate-50">

                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    {{ $purchaseOrder->po_number }}
                                </td>

                                <td class="px-6 py-4">

                                    <p class="font-semibold text-slate-900">
                                        {{ $purchaseOrder->supplier?->supplier_name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $purchaseOrder->supplier?->address }}
                                    </p>

                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $purchaseOrder->nefa_number ?: '—' }}
                                </td>

                                <td class="px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($purchaseOrder->items->sum('quantity')) }}
                                </td>

                                <td class="px-6 py-4 text-right font-bold text-emerald-800">
                                    ₱{{ number_format($purchaseOrder->total_amount, 2) }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $purchaseOrder->po_date?->format('M d, Y') }}
                                </td>

                                <td class="px-6 py-4 text-center">

                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $poStatusClass }}">
                                        {{ $purchaseOrder->status }}
                                    </span>

                                </td>

                                <td class="px-6 py-4 text-center">

                                    <a href="{{ route('supply.purchase-orders.show', $purchaseOrder) }}"
                                        class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50">
                                        View
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">
                                    No Purchase Orders are available.
                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

    </div>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function() {
                if (
                    typeof window.Chart ===
                    'undefined'
                ) {
                    console.error(
                        'Chart.js is not loaded.'
                    );

                    return;
                }

                const purchaseComposition =
                    @json($purchaseComposition);

                const poStatusChart =
                    @json($poStatusChart);

                const monthlyPurchaseOrders =
                    @json($monthlyPurchaseOrders);

                const numberFormatter =
                    new Intl.NumberFormat(
                        'en-PH'
                    );

                const currencyFormatter =
                    new Intl.NumberFormat(
                        'en-PH', {
                            style: 'currency',
                            currency: 'PHP',
                            maximumFractionDigits: 2,
                        }
                    );

                /*
                 * Monthly Purchase Order chart
                 */
                const monthlyCanvas =
                    document.getElementById(
                        'monthlyPurchaseOrderChart'
                    );

                if (monthlyCanvas) {
                    new window.Chart(
                        monthlyCanvas, {
                            type: 'bar',

                            data: {
                                labels: monthlyPurchaseOrders
                                    .labels,

                                datasets: [{
                                        type: 'bar',

                                        label: 'Purchase Order Count',

                                        data: monthlyPurchaseOrders
                                            .counts,

                                        backgroundColor: '#339DCB',

                                        borderColor: '#339DCB',

                                        borderRadius: 7,

                                        borderSkipped: false,

                                        yAxisID: 'countAxis',
                                    },

                                    {
                                        type: 'line',

                                        label: 'Purchase Order Value',

                                        data: monthlyPurchaseOrders
                                            .values,

                                        borderColor: '#339DCB',

                                        backgroundColor: '#339DCB',

                                        pointBackgroundColor: '#ffffff',

                                        pointBorderColor: '#339DCB',

                                        pointBorderWidth: 3,

                                        pointRadius: 5,

                                        pointHoverRadius: 7,

                                        tension: 0.35,

                                        fill: false,

                                        yAxisID: 'valueAxis',
                                    },
                                ],
                            },

                            options: {
                                responsive: true,

                                maintainAspectRatio: false,

                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },

                                plugins: {
                                    legend: {
                                        position: 'top',

                                        labels: {
                                            usePointStyle: true,

                                            padding: 18,

                                            color: '#475569',

                                            font: {
                                                weight: '600',
                                            },
                                        },
                                    },

                                    tooltip: {
                                        backgroundColor: '#143A52',

                                        padding: 14,

                                        cornerRadius: 10,

                                        callbacks: {
                                            label: function(
                                                context
                                            ) {
                                                if (
                                                    context.dataset
                                                    .yAxisID ===
                                                    'valueAxis'
                                                ) {
                                                    return `${context.dataset.label}: ${currencyFormatter.format(context.raw)}`;
                                                }

                                                return `${context.dataset.label}: ${numberFormatter.format(context.raw)}`;
                                            },
                                        },
                                    },
                                },

                                scales: {
                                    x: {
                                        grid: {
                                            display: false,
                                        },

                                        ticks: {
                                            color: '#475569',

                                            font: {
                                                weight: '600',
                                            },
                                        },
                                    },

                                    countAxis: {
                                        type: 'linear',

                                        position: 'left',

                                        beginAtZero: true,

                                        grid: {
                                            color: 'rgba(148, 163, 184, 0.18)',
                                        },

                                        ticks: {
                                            precision: 0,

                                            color: '#64748b',
                                        },

                                        title: {
                                            display: true,

                                            text: 'Number of Purchase Orders',

                                            color: '#334155',

                                            font: {
                                                weight: '700',
                                            },
                                        },
                                    },

                                    valueAxis: {
                                        type: 'linear',

                                        position: 'right',

                                        beginAtZero: true,

                                        grid: {
                                            drawOnChartArea: false,
                                        },

                                        ticks: {
                                            color: '#641D21',

                                            callback: function(
                                                value
                                            ) {
                                                return new Intl
                                                    .NumberFormat(
                                                        'en-PH', {
                                                            notation: 'compact',

                                                            style: 'currency',

                                                            currency: 'PHP',

                                                            maximumFractionDigits: 1,
                                                        }
                                                    )
                                                    .format(value);
                                            },
                                        },

                                        title: {
                                            display: true,

                                            text: 'Purchase Order Value',

                                            color: '#641D21',

                                            font: {
                                                weight: '700',
                                            },
                                        },
                                    },
                                },
                            },
                        }
                    );
                }

                /*
                 * PO status doughnut
                 */
                const poStatusCanvas =
                    document.getElementById(
                        'purchaseOrderStatusChart'
                    );

                if (poStatusCanvas) {
                    new window.Chart(
                        poStatusCanvas, {
                            type: 'doughnut',

                            data: {
                                labels: poStatusChart.labels,

                                datasets: [{
                                    data: poStatusChart.data,

                                    backgroundColor: [
                                        '#143A52',
                                        '#2D94BE',
                                        '#339DCB',
                                        '#61AFD2',
                                        '#90C4DD',
                                    ],

                                    borderColor: '#ffffff',

                                    borderWidth: 4,

                                    hoverOffset: 10,
                                }, ],
                            },

                            options: {
                                responsive: true,

                                maintainAspectRatio: false,

                                cutout: '66%',

                                plugins: {
                                    legend: {
                                        position: 'bottom',

                                        labels: {
                                            usePointStyle: true,

                                            pointStyle: 'circle',

                                            padding: 17,

                                            color: '#36566E',

                                            font: {
                                                weight: '600',
                                            },
                                        },
                                    },

                                    tooltip: {
                                        callbacks: {
                                            label: function(
                                                context
                                            ) {
                                                const total =
                                                    context
                                                    .dataset
                                                    .data
                                                    .reduce(
                                                        (
                                                            sum,
                                                            value
                                                        ) =>
                                                        sum +
                                                        Number(
                                                            value ||
                                                            0
                                                        ),
                                                        0
                                                    );

                                                const value =
                                                    Number(
                                                        context.raw ||
                                                        0
                                                    );

                                                const percentage =
                                                    total > 0 ?
                                                    (
                                                        (
                                                            value /
                                                            total
                                                        ) * 100
                                                    ).toFixed(1) :
                                                    '0.0';

                                                return `${context.label}: ${numberFormatter.format(value)} (${percentage}%)`;
                                            },
                                        },
                                    },
                                },
                            },
                        }
                    );
                }

                /*
                 * Purchased PPE composition
                 */
                const compositionCanvas =
                    document.getElementById(
                        'purchaseCompositionChart'
                    );

                if (compositionCanvas) {
                    new window.Chart(
                        compositionCanvas, {
                            type: 'pie',

                            data: {
                                labels: purchaseComposition
                                    .labels,

                                datasets: [{
                                    data: purchaseComposition
                                        .data,

                                    backgroundColor: [
                                        '#143A52',
                                        '#2D94BE',
                                        '#339DCB',
                                        '#61AFD2',
                                        '#90C4DD',
                                    ],

                                    borderColor: '#ffffff',

                                    borderWidth: 4,

                                    hoverOffset: 10,
                                }, ],
                            },

                            options: {
                                responsive: true,

                                maintainAspectRatio: false,

                                plugins: {
                                    legend: {
                                        position: 'bottom',

                                        labels: {
                                            usePointStyle: true,

                                            pointStyle: 'circle',

                                            padding: 15,

                                            color: '#475569',

                                            font: {
                                                size: 11,

                                                weight: '600',
                                            },
                                        },
                                    },

                                    tooltip: {
                                        callbacks: {
                                            label: function(
                                                context
                                            ) {
                                                const values =
                                                    context
                                                    .dataset
                                                    .data;

                                                const total =
                                                    values.reduce(
                                                        (
                                                            sum,
                                                            value
                                                        ) =>
                                                        sum +
                                                        Number(
                                                            value ||
                                                            0
                                                        ),
                                                        0
                                                    );

                                                const value =
                                                    Number(
                                                        context.raw ||
                                                        0
                                                    );

                                                const percentage =
                                                    total > 0 ?
                                                    (
                                                        (
                                                            value /
                                                            total
                                                        ) * 100
                                                    ).toFixed(1) :
                                                    '0.0';

                                                return `${context.label}: ${numberFormatter.format(value)} items (${percentage}%)`;
                                            },
                                        },
                                    },
                                },
                            },
                        }
                    );
                }
            }
        );
    </script>

</x-po_dashboard_layout>
