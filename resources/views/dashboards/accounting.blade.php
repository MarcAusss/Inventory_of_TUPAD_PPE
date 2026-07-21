<x-po_dashboard_layout title="Accounting Dashboard">

    @php
        $statistics = $statistics ?? [];
        $charts = $charts ?? [];
        $recentActivities = $recentActivities ?? [];

        $financialChart = $charts['monthlyFinancialOverview'] ?? [
            'labels' => [],
            'data' => [],
        ];

        $purchaseOrderStatusChart = $charts['purchaseOrderStatus'] ?? [
            'labels' => [],
            'data' => [],
        ];

        $provinceDistributionChart = $charts['provinceDistribution'] ?? [
            'labels' => [],
            'data' => [],
        ];

        $supplierSummary =
            $recentActivities['supplierSummary']
            ?? collect();

        $latestPurchaseOrders =
            $recentActivities['latestPurchaseOrders']
            ?? collect();

        $totalPurchased =
            (int) ($statistics['total_purchased_items'] ?? 0);

        $totalDistributed =
            (int) ($statistics['total_distributed_items'] ?? 0);

        $remainingQuantity = max(
            0,
            $totalPurchased - $totalDistributed
        );

        $distributionRate = $totalPurchased > 0
            ? min(
                100,
                round(
                    ($totalDistributed / $totalPurchased) * 100
                )
            )
            : 0;
    @endphp

    <div class="mx-auto max-w-[1800px] space-y-6">

        {{-- ============================================================
            PAGE HEADER
        ============================================================ --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#339DCB] to-[#55B7D9]"
            ></div>

            <div
                class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="inline-flex rounded-full bg-[#B7D6E6]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-[#339DCB] ring-1 ring-[#B7D6E6]"
                        >
                            Accounting Unit
                        </span>

                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200"
                        >
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            View-only access
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl"
                    >
                        Financial and Inventory Oversight
                    </h1>

                    <p
                        class="mt-2 max-w-3xl text-sm leading-6 text-slate-600"
                    >
                        Monitor Purchase Order values, procured PPE,
                        provincial distributions, suppliers, and recent
                        procurement activity across the inventory system.
                    </p>
                </div>

                <div
                    class="grid grid-cols-2 gap-3 sm:flex sm:items-center"
                >
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-400"
                        >
                            Last refreshed
                        </p>

                        <p
                            class="mt-1 text-sm font-semibold text-slate-900"
                        >
                            {{ now()->format('M d, Y · h:i A') }}
                        </p>
                    </div>

                    <a
                        href="{{ route('accounting.inventory-ledger.index') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#339DCB] px-5 py-4 text-sm font-bold text-white transition hover:bg-[#143A52]"
                    >
                        View Inventory Ledger
                    </a>
                </div>
            </div>
        </section>

        {{-- ============================================================
            STATISTIC CARDS
        ============================================================ --}}
        <section
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6"
        >
            <article
                class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-400"
                        >
                            Purchase Orders
                        </p>

                        <p
                            class="mt-3 text-3xl font-bold text-slate-950"
                        >
                            {{ number_format(
                                $statistics['purchase_orders'] ?? 0
                            ) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Total procurement records
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-[#B7D6E6]/20 p-3 text-[#339DCB]"
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
                                d="M7 3h10a2 2 0 0 1 2 2v16l-7-3-7 3V5a2 2 0 0 1 2-2Z"
                            />
                        </svg>
                    </div>
                </div>

                <div
                    class="mt-5 h-1 w-10 rounded-full bg-[#B7D6E6] transition-all group-hover:w-16"
                ></div>
            </article>

            <article
                class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-400"
                        >
                            Total PO Value
                        </p>

                        <p
                            class="mt-3 break-words text-2xl font-bold text-slate-950"
                        >
                            ₱{{ number_format(
                                $statistics['po_value'] ?? 0,
                                2
                            ) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Total procurement amount
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-[#55B7D9]/10 p-3 text-[#55B7D9]"
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
                                d="M12 3v18m4-14.5c-1-1-2.4-1.5-4-1.5-2.2 0-4 1.2-4 3s1.8 3 4 3 4 1.2 4 3-1.8 3-4 3c-1.6 0-3-.5-4-1.5"
                            />
                        </svg>
                    </div>
                </div>

                <div
                    class="mt-5 h-1 w-10 rounded-full bg-[#55B7D9] transition-all group-hover:w-16"
                ></div>
            </article>

            <article
                class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-400"
                        >
                            Purchased PPE
                        </p>

                        <p
                            class="mt-3 text-3xl font-bold text-slate-950"
                        >
                            {{ number_format($totalPurchased) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Total purchased quantity
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-[#247BA0]/10 p-3 text-[#247BA0]"
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

                <div
                    class="mt-5 h-1 w-10 rounded-full bg-[#247BA0] transition-all group-hover:w-16"
                ></div>
            </article>

            <article
                class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-400"
                        >
                            Distributed PPE
                        </p>

                        <p
                            class="mt-3 text-3xl font-bold text-slate-950"
                        >
                            {{ number_format($totalDistributed) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Assigned to provinces
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-[#339DCB]/10 p-3 text-[#339DCB]"
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
                                d="M3 7h12v10H3V7Zm12 3h3l3 3v4h-6v-7ZM7 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm11 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"
                            />
                        </svg>
                    </div>
                </div>

                <div
                    class="mt-5 h-1 w-10 rounded-full bg-[#339DCB] transition-all group-hover:w-16"
                ></div>
            </article>

            <article
                class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-400"
                        >
                            Delivery Receipts
                        </p>

                        <p
                            class="mt-3 text-3xl font-bold text-slate-950"
                        >
                            {{ number_format(
                                $statistics['delivery_receipts'] ?? 0
                            ) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Recorded provincial receipts
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-[#143A52]/10 p-3 text-[#143A52]"
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
                                d="M6 3h9l3 3v15H6V3Zm8 0v4h4M9 12h6m-6 4h6M9 8h2"
                            />
                        </svg>
                    </div>
                </div>

                <div
                    class="mt-5 h-1 w-10 rounded-full bg-[#143A52] transition-all group-hover:w-16"
                ></div>
            </article>

            <article
                class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-400"
                        >
                            Projects Supplied
                        </p>

                        <p
                            class="mt-3 text-3xl font-bold text-slate-950"
                        >
                            {{ number_format(
                                $statistics['project_designations'] ?? 0
                            ) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Project PPE designations
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-slate-100 p-3 text-slate-700"
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
                                d="M4 20h16M6 20V8l6-4 6 4v12M9 20v-6h6v6"
                            />
                        </svg>
                    </div>
                </div>

                <div
                    class="mt-5 h-1 w-10 rounded-full bg-slate-500 transition-all group-hover:w-16"
                ></div>
            </article>
        </section>

        {{-- ============================================================
            MAIN FINANCIAL CHART + PO STATUS
        ============================================================ --}}
        <section
            class="grid grid-cols-1 gap-6 xl:grid-cols-12"
        >
            <article
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-8"
            >
                <div
                    class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.16em] text-[#339DCB]"
                        >
                            Procurement value
                        </p>

                        <h2
                            class="mt-1 text-lg font-bold text-slate-950"
                        >
                            Monthly Financial Overview
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Purchase Order value recorded during the latest six months.
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-slate-50 px-4 py-3 text-right"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                        >
                            Total PO value
                        </p>

                        <p
                            class="mt-1 font-bold text-[#339DCB]"
                        >
                            ₱{{ number_format(
                                $statistics['po_value'] ?? 0,
                                2
                            ) }}
                        </p>
                    </div>
                </div>

                <div class="h-[380px] p-5 sm:p-6">
                    <canvas id="accountingFinancialChart"></canvas>
                </div>
            </article>

            <article
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-4"
            >
                <div class="border-b border-slate-200 px-6 py-5">
                    <p
                        class="text-xs font-bold uppercase tracking-[0.16em] text-[#339DCB]"
                    >
                        Workflow status
                    </p>

                    <h2
                        class="mt-1 text-lg font-bold text-slate-950"
                    >
                        Purchase Order Status
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Current Purchase Order distribution progress.
                    </p>
                </div>

                <div
                    class="flex h-[380px] items-center justify-center p-6"
                >
                    <canvas id="accountingPoStatusChart"></canvas>
                </div>
            </article>
        </section>

        {{-- ============================================================
            DISTRIBUTION BAR + INVENTORY UTILIZATION
        ============================================================ --}}
        <section
            class="grid grid-cols-1 gap-6 xl:grid-cols-12"
        >
            <article
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-8"
            >
                <div class="border-b border-slate-200 px-6 py-5">
                    <p
                        class="text-xs font-bold uppercase tracking-[0.16em] text-[#339DCB]"
                    >
                        Provincial monitoring
                    </p>

                    <h2
                        class="mt-1 text-lg font-bold text-slate-950"
                    >
                        PPE Distributed by Province
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Consolidated PPE quantity allocated to each provincial office.
                    </p>
                </div>

                <div class="overflow-x-auto px-4 pb-6 pt-5 sm:px-6">
                    <div class="h-[370px] min-w-[700px]">
                        <canvas id="accountingProvinceChart"></canvas>
                    </div>
                </div>
            </article>

            <article
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-4"
            >
                <div class="border-b border-slate-200 px-6 py-5">
                    <p
                        class="text-xs font-bold uppercase tracking-[0.16em] text-[#339DCB]"
                    >
                        Inventory utilization
                    </p>

                    <h2
                        class="mt-1 text-lg font-bold text-slate-950"
                    >
                        Purchased versus Distributed
                    </h2>
                </div>

                <div class="space-y-6 p-6">
                    <div
                        class="rounded-2xl bg-slate-50 p-5"
                    >
                        <div
                            class="flex items-center justify-between gap-4"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Distribution rate
                                </p>

                                <p
                                    class="mt-2 text-3xl font-bold text-[#339DCB]"
                                >
                                    {{ $distributionRate }}%
                                </p>
                            </div>

                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-full bg-[#B7D6E6]/30 text-lg font-bold text-[#143A52]"
                            >
                                {{ $distributionRate }}%
                            </div>
                        </div>

                        <div
                            class="mt-5 h-3 overflow-hidden rounded-full bg-slate-200"
                        >
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-[#55B7D9] to-[#143A52]"
                                style="width: {{ $distributionRate }}%"
                            ></div>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-3 sm:grid-cols-3 xl:grid-cols-1"
                    >
                        <div
                            class="rounded-2xl border border-slate-200 p-4"
                        >
                            <p class="text-xs font-semibold text-slate-500">
                                Purchased
                            </p>

                            <p
                                class="mt-1 text-xl font-bold text-slate-950"
                            >
                                {{ number_format($totalPurchased) }}
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200 p-4"
                        >
                            <p class="text-xs font-semibold text-slate-500">
                                Distributed
                            </p>

                            <p
                                class="mt-1 text-xl font-bold text-[#247BA0]"
                            >
                                {{ number_format($totalDistributed) }}
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200 p-4"
                        >
                            <p class="text-xs font-semibold text-slate-500">
                                Undistributed balance
                            </p>

                            <p
                                class="mt-1 text-xl font-bold text-[#143A52]"
                            >
                                {{ number_format($remainingQuantity) }}
                            </p>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        {{-- ============================================================
            SUPPLIERS + LATEST PURCHASE ORDERS
        ============================================================ --}}
        <section
            class="grid grid-cols-1 gap-6 xl:grid-cols-12"
        >
            <article
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-4"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-5"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.16em] text-[#339DCB]"
                        >
                            Supplier monitoring
                        </p>

                        <h2
                            class="mt-1 text-lg font-bold text-slate-950"
                        >
                            Top Suppliers
                        </h2>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($supplierSummary as $supplier)
                        <div
                            class="px-6 py-5 transition hover:bg-slate-50"
                        >
                            <div
                                class="flex items-start justify-between gap-4"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate font-semibold text-slate-900"
                                    >
                                        {{ $supplier->supplier_name }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        {{ number_format(
                                            $supplier->purchase_orders_count
                                        ) }}
                                        Purchase Orders
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 rounded-full bg-[#B7D6E6]/20 px-3 py-1 text-xs font-bold text-[#339DCB] ring-1 ring-[#B7D6E6]"
                                >
                                    Active
                                </span>
                            </div>

                            <p
                                class="mt-3 text-lg font-bold text-[#143A52]"
                            >
                                ₱{{ number_format(
                                    $supplier->purchase_orders_sum_total_amount ?? 0,
                                    2
                                ) }}
                            </p>
                        </div>
                    @empty
                        <div
                            class="px-6 py-12 text-center text-sm text-slate-500"
                        >
                            No supplier records are available.
                        </div>
                    @endforelse
                </div>
            </article>

            <article
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-8"
            >
                <div
                    class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.16em] text-[#339DCB]"
                        >
                            Recent procurement
                        </p>

                        <h2
                            class="mt-1 text-lg font-bold text-slate-950"
                        >
                            Latest Purchase Orders
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Most recently recorded procurement transactions.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table
                        class="min-w-[850px] w-full divide-y divide-slate-200"
                    >
                        <thead class="bg-slate-50">
                            <tr
                                class="text-xs font-bold uppercase tracking-wide text-slate-500"
                            >
                                <th class="px-6 py-4 text-left">
                                    PO Number
                                </th>

                                <th class="px-6 py-4 text-left">
                                    Supplier
                                </th>

                                <th class="px-6 py-4 text-left">
                                    Date
                                </th>

                                <th class="px-6 py-4 text-center">
                                    Status
                                </th>

                                <th class="px-6 py-4 text-right">
                                    Amount
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse($latestPurchaseOrders as $purchaseOrder)
                                @php
                                    $statusClass = match(
                                        $purchaseOrder->status
                                    ) {
                                        'Completed' =>
                                            'bg-emerald-50 text-emerald-700 ring-emerald-200',

                                        'Distributed' =>
                                            'bg-[#B7D6E6]/20 text-[#339DCB] ring-[#B7D6E6]',

                                        default =>
                                            'bg-amber-50 text-amber-700 ring-amber-200',
                                    };
                                @endphp

                                <tr
                                    class="transition hover:bg-slate-50"
                                >
                                    <td
                                        class="px-6 py-4 font-semibold text-slate-900"
                                    >
                                        {{ $purchaseOrder->po_number }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <p
                                            class="font-medium text-slate-800"
                                        >
                                            {{ $purchaseOrder->supplier?->supplier_name ?? '—' }}
                                        </p>
                                    </td>

                                    <td
                                        class="px-6 py-4 text-slate-600"
                                    >
                                        {{ $purchaseOrder->po_date?->format('M d, Y') ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}"
                                        >
                                            {{ $purchaseOrder->status }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-6 py-4 text-right font-bold text-[#143A52]"
                                    >
                                        ₱{{ number_format(
                                            $purchaseOrder->total_amount,
                                            2
                                        ) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="5"
                                        class="px-6 py-12 text-center text-sm text-slate-500"
                                    >
                                        No Purchase Orders are available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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

                const financialData =
                    @json($financialChart);

                const poStatusData =
                    @json($purchaseOrderStatusChart);

                const provinceData =
                    @json($provinceDistributionChart);

                const numberFormatter =
                    new Intl.NumberFormat(
                        'en-PH'
                    );

                const currencyFormatter =
                    new Intl.NumberFormat(
                        'en-PH',
                        {
                            style: 'currency',
                            currency: 'PHP',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        }
                    );

                const palette = [
                    '#B7D6E6',
                    '#7CC4E4',
                    '#55B7D9',
                    '#339DCB',
                    '#247BA0',
                    '#143A52',
                ];

                /*
                 * Monthly financial line chart
                 */
                const financialCanvas =
                    document.getElementById(
                        'accountingFinancialChart'
                    );

                if (financialCanvas) {
                    new window.Chart(
                        financialCanvas,
                        {
                            type: 'line',

                            data: {
                                labels:
                                    financialData.labels,

                                datasets: [
                                    {
                                        label:
                                            'Purchase Order Value',

                                        data:
                                            financialData.data,

                                        borderColor:
                                            '#247BA0',

                                        backgroundColor:
                                            'rgba(183, 214, 230, 0.38)',

                                        pointBackgroundColor:
                                            '#339DCB',

                                        pointBorderColor:
                                            '#ffffff',

                                        pointBorderWidth:
                                            3,

                                        pointRadius:
                                            5,

                                        pointHoverRadius:
                                            8,

                                        pointHoverBackgroundColor:
                                            '#55B7D9',

                                        pointHoverBorderColor:
                                            '#ffffff',

                                        borderWidth:
                                            3,

                                        tension:
                                            0.38,

                                        fill:
                                            true,
                                    },
                                ],
                            },

                            options: {
                                responsive:
                                    true,

                                maintainAspectRatio:
                                    false,

                                interaction: {
                                    mode:
                                        'index',

                                    intersect:
                                        false,
                                },

                                plugins: {
                                    legend: {
                                        position:
                                            'top',

                                        align:
                                            'end',

                                        labels: {
                                            usePointStyle:
                                                true,

                                            pointStyle:
                                                'circle',

                                            color:
                                                '#475569',

                                            font: {
                                                weight:
                                                    '600',
                                            },
                                        },
                                    },

                                    tooltip: {
                                        backgroundColor:
                                            'rgba(15, 23, 42, 0.96)',

                                        padding:
                                            14,

                                        cornerRadius:
                                            10,

                                        callbacks: {
                                            label:
                                                function (
                                                    context
                                                ) {
                                                    return `${context.dataset.label}: ${currencyFormatter.format(context.raw || 0)}`;
                                                },
                                        },
                                    },
                                },

                                scales: {
                                    x: {
                                        grid: {
                                            display:
                                                false,
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
                                            color:
                                                '#64748b',

                                            callback:
                                                function (
                                                    value
                                                ) {
                                                    return new Intl
                                                        .NumberFormat(
                                                            'en-PH',
                                                            {
                                                                notation:
                                                                    'compact',

                                                                style:
                                                                    'currency',

                                                                currency:
                                                                    'PHP',

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
                                                'Purchase Order value',

                                            color:
                                                '#334155',

                                            font: {
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

                /*
                 * Purchase Order status doughnut chart
                 */
                const statusCanvas =
                    document.getElementById(
                        'accountingPoStatusChart'
                    );

                if (statusCanvas) {
                    new window.Chart(
                        statusCanvas,
                        {
                            type:
                                'doughnut',

                            data: {
                                labels:
                                    poStatusData.labels,

                                datasets: [
                                    {
                                        data:
                                            poStatusData.data,

                                        backgroundColor:
                                            palette,

                                        hoverBackgroundColor:
                                            palette,

                                        borderColor:
                                            '#ffffff',

                                        borderWidth:
                                            4,

                                        hoverOffset:
                                            12,
                                    },
                                ],
                            },

                            options: {
                                responsive:
                                    true,

                                maintainAspectRatio:
                                    false,

                                cutout:
                                    '66%',

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
                                                16,

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

                /*
                 * Province distribution bar chart
                 */
                const provinceCanvas =
                    document.getElementById(
                        'accountingProvinceChart'
                    );

                if (provinceCanvas) {
                    const provinceColors =
                        provinceData.labels.map(
                            (
                                label,
                                index
                            ) =>
                                palette[
                                    index
                                    % palette.length
                                ]
                        );

                    new window.Chart(
                        provinceCanvas,
                        {
                            type:
                                'bar',

                            data: {
                                labels:
                                    provinceData.labels,

                                datasets: [
                                    {
                                        label:
                                            'Distributed PPE',

                                        data:
                                            provinceData.data,

                                        backgroundColor:
                                            provinceColors,

                                        hoverBackgroundColor:
                                            provinceColors,

                                        borderColor:
                                            provinceColors,

                                        borderWidth:
                                            1,

                                        borderRadius:
                                            8,

                                        borderSkipped:
                                            false,

                                        maxBarThickness:
                                            56,
                                    },
                                ],
                            },

                            options: {
                                responsive:
                                    true,

                                maintainAspectRatio:
                                    false,

                                interaction: {
                                    mode:
                                        'nearest',

                                    intersect:
                                        true,
                                },

                                plugins: {
                                    legend: {
                                        display:
                                            false,
                                    },

                                    tooltip: {
                                        backgroundColor:
                                            'rgba(15, 23, 42, 0.96)',

                                        padding:
                                            14,

                                        cornerRadius:
                                            10,

                                        callbacks: {
                                            title:
                                                function (
                                                    context
                                                ) {
                                                    return context[
                                                        0
                                                    ].label;
                                                },

                                            label:
                                                function (
                                                    context
                                                ) {
                                                    return `Distributed PPE: ${numberFormatter.format(context.raw || 0)} items`;
                                                },
                                        },
                                    },
                                },

                                scales: {
                                    x: {
                                        grid: {
                                            display:
                                                false,
                                        },

                                        ticks: {
                                            color:
                                                '#475569',

                                            font: {
                                                weight:
                                                    '600',
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
                                                weight:
                                                    '700',
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

                                            precision:
                                                0,

                                            callback:
                                                function (
                                                    value
                                                ) {
                                                    return numberFormatter
                                                        .format(
                                                            value
                                                        );
                                                },
                                        },

                                        title: {
                                            display:
                                                true,

                                            text:
                                                'Total distributed PPE',

                                            color:
                                                '#334155',

                                            font: {
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
            }
        );
    </script>

</x-po_dashboard_layout>