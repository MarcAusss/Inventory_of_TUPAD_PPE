<x-po_dashboard_layout title="TSSD Dashboard">

    @php
        $provinceChart = $charts['provinceDistribution'] ?? [
            'labels' => [],
            'shortLabels' => [],
            'datasets' => [],
        ];

        $callOffChart = $charts['callOffStatus'] ?? [
            'labels' => [],
            'data' => [],
        ];

        $receivingProgress =
            $recentActivities['receivingProgress']
            ?? [];

        $latestBatches =
            $recentActivities['latestBatches']
            ?? collect();

        $recentReceipts =
            $recentActivities['recentReceipts']
            ?? collect();
    @endphp

    <div class="mx-auto max-w-[1800px] space-y-6">

        {{-- Page heading --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
        >

            <div
                class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-blue-900 via-blue-700 to-cyan-600"
            ></div>

            <div
                class="flex flex-col gap-5 px-7 py-7 lg:flex-row lg:items-center lg:justify-between"
            >

                <div>

                    <div
                        class="flex flex-wrap items-center gap-3"
                    >

                        <span
                            class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-blue-800 ring-1 ring-blue-200"
                        >
                            TSSD Unit
                        </span>

                        <span
                            class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 ring-1 ring-green-200"
                        >
                            System operational
                        </span>

                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl"
                    >
                        Distribution Monitoring Dashboard
                    </h1>

                    <p
                        class="mt-2 max-w-3xl text-sm leading-6 text-slate-600"
                    >
                        Monitor provincial PPE allocations, Call-Off
                        processing, delivery progress, and receiving
                        activity across all six provincial offices.
                    </p>

                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4"
                >

                    <p
                        class="text-xs font-bold uppercase tracking-wider text-slate-500"
                    >
                        Last refreshed
                    </p>

                    <p
                        class="mt-1 font-semibold text-slate-900"
                    >
                        {{ now()->format('F d, Y · h:i A') }}
                    </p>

                </div>

            </div>

        </section>

        {{-- Statistic bento cards --}}
        <section
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6"
        >

            <article
                class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >

                <div
                    class="flex items-start justify-between"
                >

                    <div>

                        <p
                            class="text-sm font-semibold text-slate-500"
                        >
                            Purchase Orders
                        </p>

                        <p
                            class="mt-3 text-3xl font-bold text-slate-950"
                        >
                            {{ number_format(
                                $statistics['purchase_orders']
                                ?? 0
                            ) }}
                        </p>

                        <p
                            class="mt-1 text-xs text-slate-500"
                        >
                            Available for distribution
                        </p>

                    </div>

                    <div
                        class="rounded-xl bg-blue-50 p-3 text-blue-700"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M9 12h6m-6 4h6M9 8h6m3 12H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h7l5 5v9a2 2 0 0 1-2 2Z"
                            />
                        </svg>
                    </div>

                </div>

            </article>

            <article
                class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >

                <div
                    class="flex items-start justify-between"
                >

                    <div>

                        <p
                            class="text-sm font-semibold text-slate-500"
                        >
                            Distribution Batches
                        </p>

                        <p
                            class="mt-3 text-3xl font-bold text-slate-950"
                        >
                            {{ number_format(
                                $statistics[
                                    'distribution_batches'
                                ] ?? 0
                            ) }}
                        </p>

                        <p
                            class="mt-1 text-xs text-slate-500"
                        >
                            Active and completed
                        </p>

                    </div>

                    <div
                        class="rounded-xl bg-indigo-50 p-3 text-indigo-700"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M3 7h18M5 7l1 13h12l1-13M9 11v5m6-5v5M8 4h8"
                            />
                        </svg>
                    </div>

                </div>

            </article>

            <article
                class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >

                <div
                    class="flex items-start justify-between"
                >

                    <div>

                        <p
                            class="text-sm font-semibold text-slate-500"
                        >
                            Pending Call-Offs
                        </p>

                        <p
                            class="mt-3 text-3xl font-bold text-slate-950"
                        >
                            {{ number_format(
                                $statistics[
                                    'pending_calloffs'
                                ] ?? 0
                            ) }}
                        </p>

                        <p
                            class="mt-1 text-xs text-slate-500"
                        >
                            Awaiting approval
                        </p>

                    </div>

                    <div
                        class="rounded-xl bg-amber-50 p-3 text-amber-700"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 7v5l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                            />
                        </svg>
                    </div>

                </div>

            </article>

            <article
                class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >

                <div
                    class="flex items-start justify-between"
                >

                    <div>

                        <p
                            class="text-sm font-semibold text-slate-500"
                        >
                            Approved Call-Offs
                        </p>

                        <p
                            class="mt-3 text-3xl font-bold text-slate-950"
                        >
                            {{ number_format(
                                $statistics[
                                    'approved_calloffs'
                                ] ?? 0
                            ) }}
                        </p>

                        <p
                            class="mt-1 text-xs text-slate-500"
                        >
                            Approved for delivery
                        </p>

                    </div>

                    <div
                        class="rounded-xl bg-emerald-50 p-3 text-emerald-700"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="m5 12 4 4L19 6"
                            />
                        </svg>
                    </div>

                </div>

            </article>

            <article
                class="rounded-2xl border border-cyan-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >

                <div
                    class="flex items-start justify-between"
                >

                    <div>

                        <p
                            class="text-sm font-semibold text-slate-500"
                        >
                            Provinces Receiving
                        </p>

                        <p
                            class="mt-3 text-3xl font-bold text-slate-950"
                        >
                            {{ number_format(
                                $statistics[
                                    'active_provinces'
                                ] ?? 0
                            ) }}
                        </p>

                        <p
                            class="mt-1 text-xs text-slate-500"
                        >
                            Active provincial offices
                        </p>

                    </div>

                    <div
                        class="rounded-xl bg-cyan-50 p-3 text-cyan-700"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 21s6-5.3 6-12A6 6 0 1 0 6 9c0 6.7 6 12 6 12Zm0-9a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                            />
                        </svg>
                    </div>

                </div>

            </article>

            <article
                class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >

                <div
                    class="flex items-start justify-between"
                >

                    <div>

                        <p
                            class="text-sm font-semibold text-slate-500"
                        >
                            Total Allocated PPE
                        </p>

                        <p
                            class="mt-3 text-3xl font-bold text-slate-950"
                        >
                            {{ number_format(
                                $statistics[
                                    'total_allocated_items'
                                ] ?? 0
                            ) }}
                        </p>

                        <p
                            class="mt-1 text-xs text-slate-500"
                        >
                            All distributed units
                        </p>

                    </div>

                    <div
                        class="rounded-xl bg-violet-50 p-3 text-violet-700"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M4 7h16v13H4V7Zm3-3h10v3H7V4Zm2 8h6m-6 4h6"
                            />
                        </svg>
                    </div>

                </div>

            </article>

        </section>

        {{-- Main charts bento row --}}
        <section
            class="grid grid-cols-1 gap-6 xl:grid-cols-12"
        >

            {{-- Province distribution bar chart --}}
            <article
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-9"
            >

                <div
                    class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                >

                    <div>

                        <h2
                            class="text-lg font-bold text-slate-950"
                        >
                            Total PPE Distributed to Provincial Offices
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Grouped totals for the five PPE categories
                            across all six provinces.
                        </p>

                    </div>

                    <a
                        href="{{ route('tssd.distributions.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-800 transition hover:bg-blue-100"
                    >
                        View distributions
                    </a>

                </div>

                <div
                    class="overflow-x-auto px-4 pb-6 pt-5 sm:px-6"
                >

                    <div
                        class="h-[410px] min-w-[800px]"
                    >
                        <canvas
                            id="provinceDistributionChart"
                        ></canvas>
                    </div>

                </div>

            </article>

            {{-- Call-Off doughnut --}}
            <article
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-3"
            >

                <div
                    class="border-b border-slate-200 px-6 py-5"
                >

                    <h2
                        class="text-lg font-bold text-slate-950"
                    >
                        Call-Off Status
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Current Call-Off workflow breakdown.
                    </p>

                </div>

                <div
                    class="flex min-h-[410px] items-center justify-center p-6"
                >

                    <div
                        class="h-[330px] w-full"
                    >
                        <canvas
                            id="callOffStatusChart"
                        ></canvas>
                    </div>

                </div>

            </article>

        </section>

        {{-- Receiving table and receipts --}}
        <section
            class="grid grid-cols-1 gap-6 xl:grid-cols-12"
        >

            <article
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-8"
            >

                <div
                    class="border-b border-slate-200 px-6 py-5"
                >

                    <h2
                        class="text-lg font-bold text-slate-950"
                    >
                        Province Receiving Progress
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Receiving status of provincial allocations.
                    </p>

                </div>

                <div class="overflow-x-auto">

                    <table
                        class="min-w-full divide-y divide-slate-200"
                    >

                        <thead class="bg-slate-50">

                            <tr
                                class="text-xs font-bold uppercase tracking-wide text-slate-500"
                            >

                                <th
                                    class="px-6 py-4 text-left"
                                >
                                    Province
                                </th>

                                <th
                                    class="px-4 py-4 text-center"
                                >
                                    Received
                                </th>

                                <th
                                    class="px-4 py-4 text-center"
                                >
                                    Partial
                                </th>

                                <th
                                    class="px-4 py-4 text-center"
                                >
                                    Pending
                                </th>

                                <th
                                    class="min-w-56 px-6 py-4 text-left"
                                >
                                    Progress
                                </th>

                            </tr>

                        </thead>

                        <tbody
                            class="divide-y divide-slate-100"
                        >

                            @forelse(
                                $receivingProgress
                                as $progress
                            )

                                <tr
                                    class="transition hover:bg-slate-50"
                                >

                                    <td
                                        class="px-6 py-4 font-semibold text-slate-900"
                                    >
                                        {{ $progress['province'] }}
                                    </td>

                                    <td
                                        class="px-4 py-4 text-center text-emerald-700"
                                    >
                                        {{ number_format(
                                            $progress['received']
                                        ) }}
                                    </td>

                                    <td
                                        class="px-4 py-4 text-center text-amber-700"
                                    >
                                        {{ number_format(
                                            $progress['partial']
                                        ) }}
                                    </td>

                                    <td
                                        class="px-4 py-4 text-center text-slate-600"
                                    >
                                        {{ number_format(
                                            $progress['pending']
                                        ) }}
                                    </td>

                                    <td class="px-6 py-4">

                                        <div
                                            class="flex items-center gap-3"
                                        >

                                            <div
                                                class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-200"
                                            >

                                                <div
                                                    class="h-full rounded-full bg-blue-700"
                                                    style="width: {{ $progress['progress'] }}%"
                                                ></div>

                                            </div>

                                            <span
                                                class="w-12 text-right text-sm font-bold text-slate-700"
                                            >
                                                {{ $progress['progress'] }}%
                                            </span>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="5"
                                        class="px-6 py-12 text-center text-sm text-slate-500"
                                    >
                                        No provincial receiving records are available.
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </article>

            <article
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-4"
            >

                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-5"
                >

                    <div>

                        <h2
                            class="text-lg font-bold text-slate-950"
                        >
                            Recent Receiving
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Latest provincial DR submissions.
                        </p>

                    </div>

                </div>

                <div class="divide-y divide-slate-100">

                    @forelse(
                        $recentReceipts
                        as $receipt
                    )

                        <div
                            class="px-6 py-4 transition hover:bg-slate-50"
                        >

                            <div
                                class="flex items-start justify-between gap-4"
                            >

                                <div>

                                    <p
                                        class="font-semibold text-slate-900"
                                    >
                                        {{ $receipt->province?->name }}
                                    </p>

                                    <p
                                        class="mt-1 text-sm text-slate-500"
                                    >
                                        {{ $receipt->dr_number }}
                                    </p>

                                </div>

                                <span
                                    class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200"
                                >
                                    Received
                                </span>

                            </div>

                            <p
                                class="mt-3 text-xs text-slate-500"
                            >
                                {{ $receipt->submitted_at?->format(
                                    'M d, Y · h:i A'
                                ) ?? $receipt->created_at?->format(
                                    'M d, Y · h:i A'
                                ) }}
                            </p>

                        </div>

                    @empty

                        <div
                            class="px-6 py-12 text-center text-sm text-slate-500"
                        >
                            No recent receiving activity.
                        </div>

                    @endforelse

                </div>

            </article>

        </section>

        {{-- Latest distribution batches --}}
        <section
            class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
        >

            <div
                class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
            >

                <div>

                    <h2
                        class="text-lg font-bold text-slate-950"
                    >
                        Latest Distribution Batches
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Most recently created TSSD distribution records.
                    </p>

                </div>

                <a
                    href="{{ route('tssd.distributions.index') }}"
                    class="text-sm font-bold text-blue-700 hover:text-blue-900"
                >
                    View all distributions →
                </a>

            </div>

            <div class="overflow-x-auto">

                <table
                    class="min-w-full divide-y divide-slate-200"
                >

                    <thead class="bg-slate-50">

                        <tr
                            class="text-xs font-bold uppercase tracking-wide text-slate-500"
                        >

                            <th class="px-6 py-4 text-left">
                                Batch
                            </th>

                            <th class="px-6 py-4 text-left">
                                Purchase Order
                            </th>

                            <th class="px-6 py-4 text-left">
                                Call-Off
                            </th>

                            <th class="px-6 py-4 text-center">
                                Provinces
                            </th>

                            <th class="px-6 py-4 text-center">
                                Total Items
                            </th>

                            <th class="px-6 py-4 text-left">
                                Date
                            </th>

                            <th class="px-6 py-4 text-center">
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody
                        class="divide-y divide-slate-100"
                    >

                        @forelse(
                            $latestBatches
                            as $batch
                        )

                            @php
                                $batchTotal = $batch
                                    ->provinceDistributions
                                    ->sum(
                                        fn ($distribution) =>
                                            $distribution
                                                ->items
                                                ->sum('quantity')
                                    );

                                $batchStatusClass = match(
                                    $batch->status
                                ) {
                                    'Completed' =>
                                        'bg-emerald-50 text-emerald-700 ring-emerald-200',

                                    'Approved' =>
                                        'bg-blue-50 text-blue-700 ring-blue-200',

                                    'Partially Received' =>
                                        'bg-amber-50 text-amber-700 ring-amber-200',

                                    'Cancelled' =>
                                        'bg-slate-100 text-slate-600 ring-slate-200',

                                    default =>
                                        'bg-indigo-50 text-indigo-700 ring-indigo-200',
                                };
                            @endphp

                            <tr
                                class="transition hover:bg-slate-50"
                            >

                                <td
                                    class="px-6 py-4 font-semibold text-slate-900"
                                >
                                    #{{ $batch->id }}
                                </td>

                                <td class="px-6 py-4">

                                    <p
                                        class="font-semibold text-slate-900"
                                    >
                                        {{ $batch->purchaseOrder?->po_number }}
                                    </p>

                                </td>

                                <td
                                    class="px-6 py-4 text-slate-600"
                                >
                                    {{ $batch->callOff?->call_off_number ?? 'Not assigned' }}
                                </td>

                                <td
                                    class="px-6 py-4 text-center font-semibold text-slate-700"
                                >
                                    {{ $batch->provinceDistributions->count() }}
                                </td>

                                <td
                                    class="px-6 py-4 text-center font-semibold text-slate-700"
                                >
                                    {{ number_format(
                                        $batchTotal
                                    ) }}
                                </td>

                                <td
                                    class="px-6 py-4 text-slate-600"
                                >
                                    {{ $batch->distribution_date?->format(
                                        'M d, Y'
                                    ) }}
                                </td>

                                <td class="px-6 py-4 text-center">

                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $batchStatusClass }}"
                                    >
                                        {{ $batch->status }}
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center text-sm text-slate-500"
                                >
                                    No distribution batches are available.
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

                const provinceChartData =
                    @json($provinceChart);

                const callOffChartData =
                    @json($callOffChart);

                const numberFormatter =
                    new Intl.NumberFormat(
                        'en-PH'
                    );

                /*
                 * Match the supplied chart reference using five related
                 * red-family government colors.
                 */
                const distributionColors = [
                    '#DF979B',
                    '#ED1B24',
                    '#C51017',
                    '#970C13',
                    '#641D21',
                ];

                const provinceCanvas =
                    document.getElementById(
                        'provinceDistributionChart'
                    );

                if (provinceCanvas) {
                    new window.Chart(
                        provinceCanvas,
                        {
                            type: 'bar',

                            data: {
                                labels:
                                    provinceChartData
                                        .shortLabels,

                                datasets:
                                    provinceChartData
                                        .datasets
                                        .map(
                                            (
                                                dataset,
                                                index
                                            ) => ({
                                                label:
                                                    dataset.label,

                                                data:
                                                    dataset.data,

                                                unit:
                                                    dataset.unit,

                                                backgroundColor:
                                                    distributionColors[
                                                        index
                                                    ],

                                                borderColor:
                                                    distributionColors[
                                                        index
                                                    ],

                                                borderWidth:
                                                    0,

                                                borderRadius:
                                                    4,

                                                borderSkipped:
                                                    false,

                                                maxBarThickness:
                                                    30,

                                                categoryPercentage:
                                                    0.78,

                                                barPercentage:
                                                    0.86,
                                            })
                                        ),
                            },

                            options: {
                                responsive: true,

                                maintainAspectRatio:
                                    false,

                                interaction: {
                                    mode:
                                        'nearest',

                                    intersect:
                                        true,
                                },

                                animation: {
                                    duration:
                                        500,
                                },

                                plugins: {
                                    legend: {
                                        position:
                                            'top',

                                        align:
                                            'center',

                                        labels: {
                                            usePointStyle:
                                                true,

                                            pointStyle:
                                                'rectRounded',

                                            boxWidth:
                                                10,

                                            boxHeight:
                                                10,

                                            padding:
                                                18,

                                            color:
                                                '#334155',

                                            font: {
                                                size:
                                                    12,

                                                weight:
                                                    '600',
                                            },
                                        },
                                    },

                                    tooltip: {
                                        enabled:
                                            true,

                                        backgroundColor:
                                            'rgba(15, 23, 42, 0.96)',

                                        titleColor:
                                            '#ffffff',

                                        bodyColor:
                                            '#e2e8f0',

                                        padding:
                                            14,

                                        cornerRadius:
                                            10,

                                        displayColors:
                                            true,

                                        callbacks: {
                                            title:
                                                function (
                                                    context
                                                ) {
                                                    const index =
                                                        context[
                                                            0
                                                        ].dataIndex;

                                                    return provinceChartData
                                                        .labels[
                                                            index
                                                        ];
                                                },

                                            label:
                                                function (
                                                    context
                                                ) {
                                                    const value =
                                                        Number(
                                                            context.raw
                                                            || 0
                                                        );

                                                    const unit =
                                                        context.dataset
                                                            .unit
                                                        || 'items';

                                                    return `${context.dataset.label}: ${numberFormatter.format(value)} ${unit}`;
                                                },

                                            afterLabel:
                                                function (
                                                    context
                                                ) {
                                                    const provinceIndex =
                                                        context
                                                            .dataIndex;

                                                    const provinceTotal =
                                                        provinceChartData
                                                            .datasets
                                                            .reduce(
                                                                (
                                                                    total,
                                                                    dataset
                                                                ) =>
                                                                    total
                                                                    + Number(
                                                                        dataset
                                                                            .data[
                                                                            provinceIndex
                                                                        ]
                                                                        || 0
                                                                    ),
                                                                0
                                                            );

                                                    return `Province total: ${numberFormatter.format(provinceTotal)} items`;
                                                },
                                        },
                                    },
                                },

                                scales: {
                                    x: {
                                        stacked:
                                            false,

                                        grid: {
                                            display:
                                                false,
                                        },

                                        ticks: {
                                            color:
                                                '#334155',

                                            font: {
                                                size:
                                                    12,

                                                weight:
                                                    '700',
                                            },
                                        },

                                        title: {
                                            display:
                                                true,

                                            text:
                                                'Provincial Offices',

                                            color:
                                                '#334155',

                                            font: {
                                                size:
                                                    13,

                                                weight:
                                                    '700',
                                            },

                                            padding: {
                                                top:
                                                    14,
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
                                            color:
                                                '#64748b',

                                            callback:
                                                function (
                                                    value
                                                ) {
                                                    return Intl
                                                        .NumberFormat(
                                                            'en-PH',
                                                            {
                                                                notation:
                                                                    value
                                                                    >= 1000
                                                                        ? 'compact'
                                                                        : 'standard',

                                                                maximumFractionDigits:
                                                                    1,
                                                            }
                                                        )
                                                        .format(
                                                            value
                                                        );
                                                },
                                        },

                                        title: {
                                            display:
                                                true,

                                            text:
                                                'Total items distributed',

                                            color:
                                                '#334155',

                                            font: {
                                                size:
                                                    13,

                                                weight:
                                                    '700',
                                            },
                                        },
                                    },
                                },
                            },
                        }
                    );
                }

                const callOffCanvas =
                    document.getElementById(
                        'callOffStatusChart'
                    );

                if (callOffCanvas) {
                    new window.Chart(
                        callOffCanvas,
                        {
                            type:
                                'doughnut',

                            data: {
                                labels:
                                    callOffChartData
                                        .labels,

                                datasets: [
                                    {
                                        data:
                                            callOffChartData
                                                .data,

                                        backgroundColor: [
                                            '#DF979B',
                                            '#ED1B24',
                                            '#C51017',
                                            '#970C13',
                                            '#641D21',
                                        ],

                                        borderColor:
                                            '#ffffff',

                                        borderWidth:
                                            4,

                                        hoverOffset:
                                            8,
                                    },
                                ],
                            },

                            options: {
                                responsive:
                                    true,

                                maintainAspectRatio:
                                    false,

                                cutout:
                                    '68%',

                                plugins: {
                                    legend: {
                                        position:
                                            'bottom',

                                        labels: {
                                            usePointStyle:
                                                true,

                                            pointStyle:
                                                'circle',

                                            padding:
                                                14,

                                            color:
                                                '#475569',

                                            font: {
                                                size:
                                                    11,

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
                                                    const data =
                                                        context
                                                            .dataset
                                                            .data;

                                                    const total =
                                                        data.reduce(
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

                                                    const percentage =
                                                        total > 0
                                                            ? (
                                                                (
                                                                    value
                                                                    / total
                                                                ) * 100
                                                            ).toFixed(
                                                                1
                                                            )
                                                            : '0.0';

                                                    return `${context.label}: ${numberFormatter.format(value)} (${percentage}%)`;
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