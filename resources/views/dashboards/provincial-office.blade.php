<x-po_dashboard_layout title="Provincial Office Dashboard">

    @php
        $inventoryChart =
            $charts['inventoryComposition']
            ?? [
                'labels' => [],
                'data' => [],
            ];

        $monthlyChart =
            $charts['monthlyMovement']
            ?? [
                'labels' => [],
                'datasets' => [],
            ];

        $inventorySummary =
            $recentActivities['inventorySummary']
            ?? collect();

        $recentReceipts =
            $recentActivities['recentReceipts']
            ?? collect();

        $recentDesignations =
            $recentActivities['recentDesignations']
            ?? collect();

        $provinceName =
            auth()->user()->provinceName()
            ?? 'Provincial Office';
    @endphp

    <div class="mx-auto max-w-[1800px] space-y-6">

        {{-- Heading --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-red-950 via-red-800 to-red-600"></div>

            <div class="flex flex-col gap-5 px-7 py-7 lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <div class="flex flex-wrap items-center gap-3">

                        <span
                            class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-red-900 ring-1 ring-red-200">
                            Provincial Office
                        </span>

                        <span
                            class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 ring-1 ring-green-200">
                            Inventory active
                        </span>

                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        {{ $provinceName }} PPE Dashboard
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Monitor available PPE, approved deliveries,
                        received quantities, and project distributions
                        for your provincial office.
                    </p>

                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Last refreshed
                    </p>

                    <p class="mt-1 font-semibold text-slate-900">
                        {{ now()->format('F d, Y · h:i A') }}
                    </p>

                </div>

            </div>

        </section>

        {{-- Statistics --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">

            @php
                $cards = [
                    [
                        'label' => 'Current PPE Inventory',
                        'value' => $statistics['available_items'] ?? 0,
                        'description' => 'Available items',
                        'class' => 'bg-red-50 text-red-800',
                    ],
                    [
                        'label' => 'Total Received',
                        'value' => $statistics['total_received'] ?? 0,
                        'description' => 'Stock-in quantity',
                        'class' => 'bg-blue-50 text-blue-800',
                    ],
                    [
                        'label' => 'Issued to Projects',
                        'value' => $statistics['total_issued'] ?? 0,
                        'description' => 'Stock-out quantity',
                        'class' => 'bg-violet-50 text-violet-800',
                    ],
                    [
                        'label' => 'Pending Allocations',
                        'value' => $statistics['pending_allocations'] ?? 0,
                        'description' => 'Ready for receiving',
                        'class' => 'bg-amber-50 text-amber-800',
                    ],
                    [
                        'label' => 'Received Deliveries',
                        'value' => $statistics['received_deliveries'] ?? 0,
                        'description' => 'Complete deliveries',
                        'class' => 'bg-emerald-50 text-emerald-800',
                    ],
                    [
                        'label' => 'Partially Received',
                        'value' => $statistics['partially_received'] ?? 0,
                        'description' => 'With shortage',
                        'class' => 'bg-orange-50 text-orange-800',
                    ],
                    [
                        'label' => 'Project Designations',
                        'value' => $statistics['project_designations'] ?? 0,
                        'description' => 'Projects supplied',
                        'class' => 'bg-cyan-50 text-cyan-800',
                    ],
                ];
            @endphp

            @foreach($cards as $card)

                <article
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">

                    <div class="inline-flex rounded-xl px-3 py-2 text-xs font-bold {{ $card['class'] }}">
                        {{ $card['label'] }}
                    </div>

                    <p class="mt-4 text-3xl font-bold text-slate-950">
                        {{ number_format($card['value']) }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ $card['description'] }}
                    </p>

                </article>

            @endforeach

        </section>

        {{-- Charts --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Pie chart --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-5">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="text-lg font-bold text-slate-950">
                        Available PPE Inventory
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Current stock grouped by PPE category.
                    </p>

                </div>

                <div class="h-[410px] p-6">
                    <canvas id="inventoryCompositionChart"></canvas>
                </div>

            </article>

            {{-- Monthly bar chart --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-7">

                <div
                    class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <h2 class="text-lg font-bold text-slate-950">
                            Monthly Received versus Issued
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            PPE movement during the latest six months.
                        </p>

                    </div>

                    <a href="{{ route('provincial.inventory-ledger.index') }}"
                        class="text-sm font-bold text-red-800 hover:text-red-950">
                        View ledger →
                    </a>

                </div>

                <div class="h-[410px] p-6">
                    <canvas id="monthlyMovementChart"></canvas>
                </div>

            </article>

        </section>

        {{-- Tables --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Inventory summary --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-5">

                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">

                    <div>

                        <h2 class="text-lg font-bold text-slate-950">
                            Current Inventory Summary
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Available PPE by item and size.
                        </p>

                    </div>

                    <a href="{{ route('provincial.current-inventory.index') }}"
                        class="text-sm font-bold text-red-800 hover:text-red-950">
                        View all
                    </a>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-slate-200">

                        <thead class="bg-slate-50">

                            <tr class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                <th class="px-6 py-4 text-left">
                                    PPE
                                </th>

                                <th class="px-4 py-4 text-left">
                                    Size
                                </th>

                                <th class="px-6 py-4 text-right">
                                    Available
                                </th>
                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @forelse($inventorySummary as $inventory)

                                <tr class="hover:bg-slate-50">

                                    <td class="px-6 py-4 font-semibold text-slate-900">
                                        {{ $inventory->item->item_name }}
                                    </td>

                                    <td class="px-4 py-4 text-slate-600">
                                        {{ $inventory->item->label ?: '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-right font-bold text-red-900">
                                        {{ number_format($inventory->quantity) }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-sm text-slate-500">
                                        No inventory records available.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </article>

            {{-- Recent receiving --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-3">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="text-lg font-bold text-slate-950">
                        Recent Receiving
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Latest Delivery Receipts.
                    </p>

                </div>

                <div class="divide-y divide-slate-100">

                    @forelse($recentReceipts as $receipt)

                                    <div class="px-6 py-4 hover:bg-slate-50">

                                        <div class="flex items-start justify-between gap-3">

                                            <div>

                                                <p class="font-semibold text-slate-900">
                                                    {{ $receipt->dr_number }}
                                                </p>

                                                <p class="mt-1 text-xs text-slate-500">
                                                    {{ $receipt
                        ->provinceDistribution
                        ?->distributionBatch
                        ?->callOff
                            ?->call_off_number
                        ?? 'Call-Off unavailable' }}
                                                </p>

                                            </div>

                                            <span
                                                class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700 ring-1 ring-green-200">
                                                Received
                                            </span>

                                        </div>

                                        <p class="mt-3 text-xs text-slate-500">
                                            {{ $receipt->delivery_date?->format('M d, Y') }}
                                        </p>

                                    </div>

                    @empty

                        <div class="px-6 py-12 text-center text-sm text-slate-500">
                            No receiving activity available.
                        </div>

                    @endforelse

                </div>

            </article>

            {{-- Recent projects --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-4">

                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">

                    <div>

                        <h2 class="text-lg font-bold text-slate-950">
                            Recent Project Designations
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Latest projects supplied with PPE.
                        </p>

                    </div>

                    <a href="{{ route('provincial.project-designations.index') }}"
                        class="text-sm font-bold text-red-800 hover:text-red-950">
                        View all
                    </a>

                </div>

                <div class="divide-y divide-slate-100">

                    @forelse($recentDesignations as $designation)

                                        <div class="px-6 py-4 hover:bg-slate-50">

                                            <div class="flex items-start justify-between gap-4">

                                                <div>

                                                    <p class="font-semibold text-slate-900">
                                                        {{ $designation->project_code }}
                                                    </p>

                                                    <p class="mt-1 text-sm text-slate-600">
                                                        {{ $designation->project_title }}
                                                    </p>

                                                </div>

                                                <span
                                                    class="shrink-0 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-800 ring-1 ring-red-200">
                                                    {{ number_format(
                            $designation->items->sum('quantity')
                        ) }}
                                                    PPE
                                                </span>

                                            </div>

                                            <p class="mt-3 text-xs text-slate-500">
                                                {{ $designation->designation_date?->format('M d, Y') }}
                                                ·
                                                {{ $designation->location }}
                                            </p>

                                        </div>

                    @empty

                        <div class="px-6 py-12 text-center text-sm text-slate-500">
                            No project designations available.
                        </div>

                    @endforelse

                </div>

            </article>

        </section>

    </div>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                if (
                    typeof window.Chart
                    === 'undefined'
                ) {
                    console.error(
                        'Chart.js is not loaded.'
                    );

                    return;
                }

                const inventoryData =
                    @json($inventoryChart);

                const monthlyData =
                    @json($monthlyChart);

                const formatter =
                    new Intl.NumberFormat('en-PH');

                const inventoryCanvas =
                    document.getElementById(
                        'inventoryCompositionChart'
                    );

                if (inventoryCanvas) {
                    new window.Chart(
                        inventoryCanvas,
                        {
                            type: 'doughnut',

                            data: {
                                labels:
                                    inventoryData.labels,

                                datasets: [
                                    {
                                        data:
                                            inventoryData.data,

                                        backgroundColor: [
                                            '#DF979B',
                                            '#ED1B24',
                                            '#C51017',
                                            '#970C13',
                                            '#641D21',
                                            '#FFBABE',
                                        ],

                                        borderColor:
                                            '#ffffff',

                                        borderWidth:
                                            4,

                                        hoverOffset:
                                            10,
                                    },
                                ],
                            },

                            options: {
                                responsive: true,

                                maintainAspectRatio:
                                    false,

                                cutout: '63%',

                                plugins: {
                                    legend: {
                                        position: 'bottom',

                                        labels: {
                                            usePointStyle:
                                                true,

                                            padding: 16,

                                            color:
                                                '#475569',

                                            font: {
                                                weight:
                                                    '600',
                                            },
                                        },
                                    },

                                    tooltip: {
                                        callbacks: {
                                            label:
                                                function (
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
                                                                    sum
                                                                    + Number(
                                                                        value
                                                                        || 0
                                                                    ),
                                                                0
                                                            );

                                                    const value =
                                                        Number(
                                                            context.raw
                                                            || 0
                                                        );

                                                    const percent =
                                                        total > 0
                                                            ? (
                                                                (
                                                                    value
                                                                    / total
                                                                ) * 100
                                                            ).toFixed(1)
                                                            : '0.0';

                                                    return `${context.label}: ${formatter.format(value)} items (${percent}%)`;
                                                },
                                        },
                                    },
                                },
                            },
                        }
                    );
                }

                const monthlyCanvas =
                    document.getElementById(
                        'monthlyMovementChart'
                    );

                if (monthlyCanvas) {
                    new window.Chart(
                        monthlyCanvas,
                        {
                            type: 'bar',

                            data: {
                                labels:
                                    monthlyData.labels,

                                datasets: [
                                    {
                                        label:
                                            'Received',

                                        data:
                                            monthlyData
                                                .datasets[
                                                0
                                            ]?.data
                                            ?? [],

                                        backgroundColor:
                                            '#C51017',

                                        borderRadius:
                                            6,

                                        borderSkipped:
                                            false,
                                    },

                                    {
                                        label:
                                            'Issued to Projects',

                                        data:
                                            monthlyData
                                                .datasets[
                                                1
                                            ]?.data
                                            ?? [],

                                        backgroundColor:
                                            '#641D21',

                                        borderRadius:
                                            6,

                                        borderSkipped:
                                            false,
                                    },
                                ],
                            },

                            options: {
                                responsive:
                                    true,

                                maintainAspectRatio:
                                    false,

                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },

                                plugins: {
                                    legend: {
                                        position: 'top',

                                        labels: {
                                            usePointStyle:
                                                true,

                                            padding: 18,

                                            color:
                                                '#475569',

                                            font: {
                                                weight:
                                                    '600',
                                            },
                                        },
                                    },

                                    tooltip: {
                                        callbacks: {
                                            label:
                                                function (
                                                    context
                                                ) {
                                                    return `${context.dataset.label}: ${formatter.format(context.raw)} items`;
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
                                            color:
                                                '#475569',

                                            font: {
                                                weight:
                                                    '600',
                                            },
                                        },
                                    },

                                    y: {
                                        beginAtZero:
                                            true,

                                        grid: {
                                            color:
                                                'rgba(148, 163, 184, 0.18)',
                                        },

                                        ticks: {
                                            callback:
                                                function (
                                                    value
                                                ) {
                                                    return formatter
                                                        .format(value);
                                                },
                                        },

                                        title: {
                                            display:
                                                true,

                                            text:
                                                'PPE quantity',
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