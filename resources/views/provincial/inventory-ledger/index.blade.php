<x-po_dashboard_layout title="Inventory Movement History">

    @php
        /*
        |--------------------------------------------------------------------------
        | PPE COLUMN CONFIGURATION
        |--------------------------------------------------------------------------
        |
        | These IDs correspond to the seven fixed PPE variants:
        |
        | 1 - Long Sleeve Medium
        | 2 - Long Sleeve Large
        | 3 - Bucket Hat
        | 4 - Rubber Boots US9
        | 5 - Rubber Boots US10
        | 6 - Hand Gloves
        | 7 - Mask
        |
        */

        $ppeColumns = [
            1 => [
                'short' => 'Medium',
                'group' => 'Long Sleeve',
            ],

            2 => [
                'short' => 'Large',
                'group' => 'Long Sleeve',
            ],

            3 => [
                'short' => 'Bucket Hat',
                'group' => null,
            ],

            4 => [
                'short' => 'US9',
                'group' => 'Rubber Boots',
            ],

            5 => [
                'short' => 'US10',
                'group' => 'Rubber Boots',
            ],

            6 => [
                'short' => 'Gloves',
                'group' => null,
            ],

            7 => [
                'short' => 'Mask',
                'group' => null,
            ],
        ];

        /*
         * First displayed number for the current paginator page.
         */
        $firstRowNumber = $rows->firstItem() ?? 1;

        /*
         * Access the current paginator page as a normal Collection.
         *
         * This is used to determine whether the current row starts
         * a new Call-Off section.
         */
        $pageRows = $rows->getCollection();
    @endphp

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- =========================================================
            PAGE HEADER
        ========================================================== --}}
        <section
            class="relative overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm">
            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b from-[#075985]
                       via-[#0284C7] to-[#38BDF8]">
            </div>

            <div
                class="flex flex-col gap-5 px-6 py-7
                       sm:px-8 lg:flex-row
                       lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#7DD3FC]/20
                                   px-3 py-1 text-xs font-bold
                                   uppercase tracking-[0.16em]
                                   text-[#0284C7]
                                   ring-1 ring-[#7DD3FC]">
                            Provincial Office
                        </span>

                        <span
                            class="rounded-full bg-slate-100
                                   px-3 py-1 text-xs font-semibold
                                   text-slate-700 ring-1
                                   ring-slate-200">
                            Inventory Report
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold
                               tracking-tight text-slate-950
                               sm:text-3xl">
                        Inventory Movement History
                    </h1>

                    <p class="mt-2 max-w-4xl text-sm
                               leading-6 text-slate-600">
                        Call-Off-based PPE inventory movement showing
                        beginning inventory, actual PPE distributed to
                        projects, and the resulting ending inventory.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200
                           bg-slate-50 px-5 py-4">
                    <p class="text-xs font-bold uppercase
                               tracking-wider text-slate-400">
                        Reporting Year
                    </p>

                    <p class="mt-1 text-2xl font-bold
                               text-[#075985]">
                        {{ $year }}
                    </p>
                </div>
            </div>
        </section>

        {{-- =========================================================
            FLASH MESSAGES
        ========================================================== --}}
        @if (session('success'))
            <div
                class="rounded-2xl border border-green-200
                       bg-green-50 px-5 py-4
                       text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="rounded-2xl border border-red-200
                       bg-red-50 px-5 py-4
                       text-sm font-semibold text-red-800">
                {{ session('error') }}
            </div>
        @endif

        {{-- =========================================================
            SUMMARY CARDS
        ========================================================== --}}
        <section class="grid grid-cols-1 gap-4
                   sm:grid-cols-2 xl:grid-cols-5">
            {{-- Call-Off count --}}
            <article
                class="group rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm transition
                       hover:-translate-y-1 hover:shadow-md">
                <div
                    class="mb-4 h-1 w-10 rounded-full
                           bg-[#075985] transition-all
                           group-hover:w-16">
                </div>

                <p class="text-xs font-bold uppercase
                           tracking-wider text-slate-400">
                    Call-Offs
                </p>

                <p class="mt-3 text-3xl font-bold
                           text-slate-950">
                    {{ number_format($summary['call_off_count'] ?? 0) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Call-Off allocations in report
                </p>
            </article>

            {{-- Project count --}}
            <article
                class="group rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm transition
                       hover:-translate-y-1 hover:shadow-md">
                <div
                    class="mb-4 h-1 w-10 rounded-full
                           bg-[#0284C7] transition-all
                           group-hover:w-16">
                </div>

                <p class="text-xs font-bold uppercase
                           tracking-wider text-slate-400">
                    Projects
                </p>

                <p class="mt-3 text-3xl font-bold
                           text-slate-950">
                    {{ number_format($summary['project_count'] ?? 0) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    PPE project distributions
                </p>
            </article>

            {{-- Beginning inventory --}}
            <article
                class="group rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm transition
                       hover:-translate-y-1 hover:shadow-md">
                <div
                    class="mb-4 h-1 w-10 rounded-full
                           bg-[#0EA5E9] transition-all
                           group-hover:w-16">
                </div>

                <p class="text-xs font-bold uppercase
                           tracking-wider text-slate-400">
                    Beginning Inventory
                </p>

                <p class="mt-3 text-3xl font-bold
                           text-slate-950">
                    {{ number_format($summary['beginning_total'] ?? 0) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Opening balance of each Call-Off
                </p>
            </article>

            {{-- Actual distributed --}}
            <article
                class="group rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm transition
                       hover:-translate-y-1 hover:shadow-md">
                <div
                    class="mb-4 h-1 w-10 rounded-full
                           bg-[#38BDF8] transition-all
                           group-hover:w-16">
                </div>

                <p class="text-xs font-bold uppercase
                           tracking-wider text-slate-400">
                    Actual Distributed
                </p>

                <p class="mt-3 text-3xl font-bold
                           text-[#0EA5E9]">
                    {{ number_format($summary['actual_total'] ?? 0) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    PPE distributed to projects
                </p>
            </article>

            {{-- Ending inventory --}}
            <article
                class="group rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm transition
                       hover:-translate-y-1 hover:shadow-md">
                <div
                    class="mb-4 h-1 w-10 rounded-full
                           bg-[#7DD3FC] transition-all
                           group-hover:w-16">
                </div>

                <p class="text-xs font-bold uppercase
                           tracking-wider text-slate-400">
                    Ending Inventory
                </p>

                <p class="mt-3 text-3xl font-bold
                           text-[#075985]">
                    {{ number_format($summary['ending_total'] ?? 0) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Final remaining Call-Off PPE
                </p>
            </article>
        </section>

        {{-- =========================================================
            DELIVERY RECEIPT FILTER
        ========================================================== --}}
        <section class="rounded-3xl border border-slate-200
                bg-white p-5 shadow-sm sm:p-6">
            <form method="GET" action="{{ route('provincial.inventory-ledger.index') }}"
                class="grid grid-cols-1 gap-4
                    md:grid-cols-2 xl:grid-cols-12">
                {{-- Delivery Receipt --}}
                <div class="xl:col-span-7">
                    <label for="delivery_receipt_id"
                        class="mb-2 block text-xs font-bold
                            uppercase tracking-wider
                            text-slate-500">
                        Delivery Receipt
                    </label>

                    <select id="delivery_receipt_id" name="delivery_receipt_id" required
                        class="w-full rounded-xl border-slate-300
                            focus:border-[#0284C7]
                            focus:ring-[#0284C7]">
                        <option value="">
                            Select a Delivery Receipt
                        </option>

                        @foreach ($deliveryReceipts as $receipt)
                            @php
                                $filterAllocation = $receipt->provinceDistribution;

                                $filterBatch = $filterAllocation?->distributionBatch;

                                $filterCallOff = $filterBatch?->callOff;

                                $filterPurchaseOrder = $filterBatch?->purchaseOrder;

                                $filterSupplier = $filterPurchaseOrder?->supplier;
                            @endphp

                            <option value="{{ $receipt->id }}" @selected($deliveryReceiptId === (int) $receipt->id)>
                                {{ $receipt->dr_number }}
                                —
                                {{ $filterCallOff?->call_off_number ?? 'No Call-Off' }}
                                —
                                {{ $filterSupplier?->supplier_name ?? 'Supplier unavailable' }}
                                —
                                {{ $receipt->delivery_date?->format('M d, Y') ?? 'No date' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Year --}}
                <div class="xl:col-span-2">
                    <label for="year"
                        class="mb-2 block text-xs font-bold
                            uppercase tracking-wider
                            text-slate-500">
                        Year
                    </label>

                    <select id="year" name="year"
                        class="w-full rounded-xl border-slate-300
                            focus:border-[#0284C7]
                            focus:ring-[#0284C7]">
                        @foreach ($availableYears as $availableYear)
                            <option value="{{ $availableYear }}" @selected($year === (int) $availableYear)>
                                {{ $availableYear }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-wrap items-end gap-2
           xl:col-span-3">
                    <button type="submit"
                        class="flex-1 rounded-xl bg-[#0284C7]
               px-5 py-2.5 text-sm font-bold
               text-white transition
               hover:bg-[#075985]">
                        Load Ledger
                    </button>

                    @if ($deliveryReceiptId > 0)
                        <a href="{{ route('provincial.inventory-ledger.print', [
                            'delivery_receipt_id' => $deliveryReceiptId,
                        
                            'year' => $year,
                        ]) }}"
                            target="_blank" rel="noopener"
                            class="rounded-xl bg-slate-900
                   px-5 py-2.5 text-sm font-bold
                   text-white transition
                   hover:bg-slate-800">
                            Print
                        </a>
                    @endif

                    <a href="{{ route('provincial.inventory-ledger.index') }}"
                        class="rounded-xl border border-slate-300
               bg-white px-5 py-2.5 text-sm
               font-bold text-slate-700 transition
               hover:bg-slate-50">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        {{-- =========================================================
            REPORT INFORMATION
        ========================================================== --}}
        @if ($deliveryReceiptId > 0)
            <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <article class="rounded-2xl border border-slate-200
                       bg-slate-50 p-5">
                    <p class="text-xs font-bold uppercase
                           tracking-wider text-slate-500">
                        Beginning Inventory
                    </p>

                    <p class="mt-2 text-sm leading-6 text-slate-700">
                        PPE balance available under the selected Call-Off
                        before the current project distribution.
                    </p>
                </article>

                <article class="rounded-2xl border border-[#7DD3FC]
                       bg-[#7DD3FC]/10 p-5">
                    <p class="text-xs font-bold uppercase
                           tracking-wider text-[#0284C7]">
                        Actual Distribution
                    </p>

                    <p class="mt-2 text-sm leading-6 text-slate-700">
                        Actual PPE quantity distributed to the project
                        identified in the same report row.
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200
                       bg-slate-50 p-5">
                    <p class="text-xs font-bold uppercase
                           tracking-wider text-slate-500">
                        Ending Inventory
                    </p>

                    <p class="mt-2 text-sm leading-6 text-slate-700">
                        Beginning Inventory minus Actual Distribution. This
                        becomes the next project's Beginning Inventory.
                    </p>
                </article>
            </section>

            {{-- =========================================================
            INVENTORY MOVEMENT TABLE
        ========================================================== --}}
            <section class="overflow-hidden rounded-3xl border
                   border-slate-200 bg-white shadow-sm">
                <div
                    class="flex flex-col gap-3 border-b
                       border-slate-200 px-6 py-5
                       lg:flex-row lg:items-center
                       lg:justify-between">
                    <div>
                        <p
                            class="text-xs font-bold uppercase
                               tracking-[0.16em] text-[#0284C7]">
                            Call-Off Project Transactions
                        </p>

                        <h2 class="mt-1 text-xl font-bold
                               text-slate-950">
                            Inventory Movement History
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Detailed PPE distribution from the selected Delivery Receipt per project.
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-slate-100
                           px-4 py-2 text-sm
                           font-semibold text-slate-600">
                        {{ number_format($summary['row_count'] ?? 0) }}
                        report rows
                    </div>
                </div>

                @if ($rows->isEmpty())
                    <div class="px-6 py-20 text-center">
                        <div
                            class="mx-auto flex h-16 w-16
                               items-center justify-center
                               rounded-2xl bg-slate-100
                               text-2xl">
                            📋
                        </div>

                        <h3 class="mt-5 text-lg font-bold
                               text-slate-900">
                            No inventory movement records found
                        </h3>

                        <p
                            class="mx-auto mt-2 max-w-xl text-sm
                               leading-6 text-slate-500">
                            No Call-Off inventory records match the
                            selected report filters.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[3300px]
                               border-collapse text-sm">
                            <thead>
                                {{-- =====================================
                                MAIN HEADER
                            ====================================== --}}
                                <tr
                                    class="border-b border-[#075985]
                                       bg-[#075985] text-white">
                                    <th rowspan="3"
                                        class="sticky left-0 z-30
                                           w-[70px] min-w-[70px]
                                           border-r border-white/20
                                           bg-[#075985]
                                           px-3 py-4 text-center">
                                        No.
                                    </th>

                                    <th rowspan="3"
                                        class="sticky left-[70px] z-30
                                           w-[170px] min-w-[170px]
                                           border-r border-white/20
                                           bg-[#075985]
                                           px-4 py-4 text-left">
                                        Call-Off Number
                                    </th>

                                    <th rowspan="3"
                                        class="w-[230px] min-w-[230px]
                                           border-r border-white/20
                                           px-4 py-4 text-left">
                                        Name of Supplier
                                    </th>

                                    <th rowspan="3"
                                        class="w-[190px] min-w-[190px]
                                           border-r border-white/20
                                           px-4 py-4 text-left">
                                        Delivery Receipt
                                    </th>

                                    <th rowspan="3"
                                        class="w-[150px] min-w-[150px]
                                           border-r border-white/20
                                           px-4 py-4 text-left">
                                        Date of Delivery
                                    </th>

                                    <th rowspan="3"
                                        class="w-[190px] min-w-[190px]
                                           border-r border-white/20
                                           px-4 py-4 text-left">
                                        Project Code
                                    </th>

                                    <th rowspan="3"
                                        class="w-[230px] min-w-[230px]
                                           border-r border-white/20
                                           px-4 py-4 text-left">
                                        Location
                                    </th>

                                    <th rowspan="3"
                                        class="w-[150px] min-w-[150px]
                                           border-r border-white/20
                                           px-4 py-4 text-center">
                                        Number of Beneficiaries
                                    </th>

                                    <th rowspan="3"
                                        class="w-[120px] min-w-[120px]
                                           border-r border-white/20
                                           px-4 py-4 text-center">
                                        Number of Days
                                    </th>

                                    <th colspan="7"
                                        class="border-r border-white/20
                                           bg-[#0284C7]
                                           px-4 py-4 text-center
                                           text-sm font-bold uppercase
                                           tracking-wider">
                                        Beginning Inventory
                                    </th>

                                    <th colspan="7"
                                        class="border-r border-white/20
                                           bg-[#0EA5E9]
                                           px-4 py-4 text-center
                                           text-sm font-bold uppercase
                                           tracking-wider">
                                        Actual Distribution
                                    </th>

                                    <th colspan="7"
                                        class="bg-[#075985]
                                           px-4 py-4 text-center
                                           text-sm font-bold uppercase
                                           tracking-wider">
                                        Ending Inventory
                                    </th>
                                </tr>

                                {{-- =====================================
                                PPE GROUP HEADER
                            ====================================== --}}
                                <tr
                                    class="border-b border-white/20
                                       bg-[#0284C7] text-white">
                                    {{-- Beginning Inventory --}}
                                    <th colspan="2"
                                        class="border-r border-white/20
                                           px-3 py-3 text-center">
                                        Long Sleeve
                                    </th>

                                    <th rowspan="2"
                                        class="min-w-[105px]
                                           border-r border-white/20
                                           px-3 py-3 text-center">
                                        Bucket Hat
                                    </th>

                                    <th colspan="2"
                                        class="border-r border-white/20
                                           px-3 py-3 text-center">
                                        Rubber Boots
                                    </th>

                                    <th rowspan="2"
                                        class="min-w-[100px]
                                           border-r border-white/20
                                           px-3 py-3 text-center">
                                        Gloves
                                    </th>

                                    <th rowspan="2"
                                        class="min-w-[100px]
                                           border-r border-white/20
                                           px-3 py-3 text-center">
                                        Mask
                                    </th>

                                    {{-- Actual Distribution --}}
                                    <th colspan="2"
                                        class="border-r border-white/20
                                           bg-[#0EA5E9]
                                           px-3 py-3 text-center">
                                        Long Sleeve
                                    </th>

                                    <th rowspan="2"
                                        class="min-w-[105px]
                                           border-r border-white/20
                                           bg-[#0EA5E9]
                                           px-3 py-3 text-center">
                                        Bucket Hat
                                    </th>

                                    <th colspan="2"
                                        class="border-r border-white/20
                                           bg-[#0EA5E9]
                                           px-3 py-3 text-center">
                                        Rubber Boots
                                    </th>

                                    <th rowspan="2"
                                        class="min-w-[100px]
                                           border-r border-white/20
                                           bg-[#0EA5E9]
                                           px-3 py-3 text-center">
                                        Gloves
                                    </th>

                                    <th rowspan="2"
                                        class="min-w-[100px]
                                           border-r border-white/20
                                           bg-[#0EA5E9]
                                           px-3 py-3 text-center">
                                        Mask
                                    </th>

                                    {{-- Ending Inventory --}}
                                    <th colspan="2"
                                        class="border-r border-white/20
                                           bg-[#075985]
                                           px-3 py-3 text-center">
                                        Long Sleeve
                                    </th>

                                    <th rowspan="2"
                                        class="min-w-[105px]
                                           border-r border-white/20
                                           bg-[#075985]
                                           px-3 py-3 text-center">
                                        Bucket Hat
                                    </th>

                                    <th colspan="2"
                                        class="border-r border-white/20
                                           bg-[#075985]
                                           px-3 py-3 text-center">
                                        Rubber Boots
                                    </th>

                                    <th rowspan="2"
                                        class="min-w-[100px]
                                           border-r border-white/20
                                           bg-[#075985]
                                           px-3 py-3 text-center">
                                        Gloves
                                    </th>

                                    <th rowspan="2"
                                        class="min-w-[100px]
                                           border-r border-white/20
                                           bg-[#075985]
                                           px-3 py-3 text-center">
                                        Mask
                                    </th>
                                </tr>

                                {{-- =====================================
                                SIZE HEADER
                            ====================================== --}}
                                <tr class="bg-[#7DD3FC]
                                       text-[#075985]">
                                    {{-- Beginning sizes --}}
                                    <th class="px-3 py-2 text-center">
                                        Medium
                                    </th>

                                    <th
                                        class="border-r
                                           border-[#0284C7]/20
                                           px-3 py-2 text-center">
                                        Large
                                    </th>

                                    <th class="px-3 py-2 text-center">
                                        US9
                                    </th>

                                    <th
                                        class="border-r
                                           border-[#0284C7]/20
                                           px-3 py-2 text-center">
                                        US10
                                    </th>

                                    {{-- Actual sizes --}}
                                    <th class="px-3 py-2 text-center">
                                        Medium
                                    </th>

                                    <th
                                        class="border-r
                                           border-[#0284C7]/20
                                           px-3 py-2 text-center">
                                        Large
                                    </th>

                                    <th class="px-3 py-2 text-center">
                                        US9
                                    </th>

                                    <th
                                        class="border-r
                                           border-[#0284C7]/20
                                           px-3 py-2 text-center">
                                        US10
                                    </th>

                                    {{-- Ending sizes --}}
                                    <th class="px-3 py-2 text-center">
                                        Medium
                                    </th>

                                    <th
                                        class="border-r
                                           border-[#0284C7]/20
                                           px-3 py-2 text-center">
                                        Large
                                    </th>

                                    <th class="px-3 py-2 text-center">
                                        US9
                                    </th>

                                    <th class="px-3 py-2 text-center">
                                        US10
                                    </th>
                                </tr>
                            </thead>

                            {{-- =========================================
                            REPORT ROWS
                        ========================================== --}}
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($pageRows as $index => $row)
                                    @php
                                        /*
                                         * Call-Off-specific values produced by
                                         * InventoryMovementReportService.
                                         */
                                        $beginning = $row['beginning'] ?? [];

                                        $actual = $row['actual'] ?? [];

                                        $ending = $row['ending'] ?? [];

                                        /*
                                         * A row without a designation ID is an
                                         * opening row for a Call-Off that has no
                                         * project distribution yet.
                                         */
                                        $isOpeningRow = empty($row['supply_designation_id']);

                                        /*
                                         * Detect the start of a Call-Off section.
                                         */
                                        $previousRow = $index > 0 ? $pageRows->get($index - 1) : null;

                                        $currentAllocationId = (int) ($row['province_distribution_id'] ?? 0);

                                        $previousAllocationId = (int) ($previousRow['province_distribution_id'] ?? 0);

                                        $isNewCallOff = $index === 0 || $currentAllocationId !== $previousAllocationId;

                                    @endphp

                                    <tr
                                        class="
                                        transition
                                        hover:bg-[#7DD3FC]/10

                                        {{ $isNewCallOff ? 'border-t-4 border-t-[#0284C7]' : '' }}
                                    ">
                                        {{-- Number --}}
                                        <td
                                            class="sticky left-0 z-20
                                               border-r border-slate-200
                                               bg-white px-3 py-4
                                               text-center font-semibold
                                               text-slate-500">
                                            {{ $firstRowNumber + $index }}
                                        </td>

                                        {{-- Call-Off --}}
                                        <td
                                            class="sticky left-[70px] z-20
                                               border-r border-slate-200
                                               bg-white px-4 py-4">
                                            <span
                                                class="inline-flex rounded-lg
                                                   bg-[#0284C7]/10
                                                   px-3 py-1.5
                                                   font-bold text-[#0284C7]
                                                   ring-1
                                                   ring-[#0284C7]/20">
                                                {{ $row['call_off_number'] ?? '—' }}
                                            </span>
                                        </td>

                                        {{-- Supplier --}}
                                        <td
                                            class="border-r border-slate-100
                                               px-4 py-4 font-medium
                                               text-slate-800">
                                            {{ $row['supplier_name'] ?? '—' }}
                                        </td>

                                        {{-- Delivery Receipts --}}
                                        {{-- Delivery Receipt --}}
                                        {{-- Delivery Receipts --}}
                                        <td
                                            class="border-r border-slate-100
                                           px-4 py-4">
                                            <div
                                                class="rounded-lg bg-slate-100
                                               px-2.5 py-1.5 text-xs
                                               font-semibold text-slate-700">
                                                {{ $row['delivery_receipt_number'] ?? '—' }}
                                            </div>
                                        </td>

                                        <td
                                            class="border-r border-slate-100
                                        px-4 py-4">
                                            <div
                                                class="whitespace-nowrap text-xs
                                            text-slate-600">
                                                {{ isset($row['delivery_date']) && $row['delivery_date'] ? $row['delivery_date']->format('M d, Y') : '—' }}
                                            </div>
                                        </td>


                                        {{-- Project --}}
                                        <td
                                            class="border-r border-slate-100
                                               px-4 py-4">
                                            @if ($isOpeningRow)
                                                <span
                                                    class="rounded-lg
                                                       bg-amber-50
                                                       px-2.5 py-1.5
                                                       text-xs font-bold
                                                       text-amber-700
                                                       ring-1
                                                       ring-amber-200">
                                                    No Project Yet
                                                </span>
                                            @else
                                                <p
                                                    class="font-bold
                                                       text-slate-900">
                                                    {{ $row['project_code'] ?? '—' }}
                                                </p>

                                                <p
                                                    class="mt-1 max-w-[220px]
                                                       text-xs leading-5
                                                       text-slate-500">
                                                    {{ $row['project_title'] ?? '—' }}
                                                </p>
                                            @endif
                                        </td>

                                        {{-- Location --}}
                                        <td
                                            class="border-r border-slate-100
                                               px-4 py-4
                                               text-slate-700">
                                            {{ $row['location'] ?? '—' }}
                                        </td>

                                        {{-- Beneficiaries --}}
                                        <td
                                            class="border-r border-slate-100
                                               px-4 py-4 text-center
                                               font-semibold
                                               text-slate-800">
                                            {{ number_format((int) ($row['number_of_beneficiaries'] ?? 0)) }}
                                        </td>

                                        {{-- Days --}}
                                        <td
                                            class="border-r border-slate-200
                                               px-4 py-4 text-center
                                               font-semibold
                                               text-slate-800">
                                            {{ number_format((int) ($row['number_of_days'] ?? 0)) }}
                                        </td>

                                        {{-- Beginning Inventory --}}
                                        @foreach (array_keys($ppeColumns) as $itemId)
                                            @php
                                                $beginningQuantity = (int) ($beginning[$itemId] ?? 0);
                                            @endphp

                                            <td
                                                class="border-r
                                                   border-slate-100
                                                   bg-slate-50/70
                                                   px-3 py-4
                                                   text-center
                                                   font-semibold
                                                   text-slate-800">
                                                {{ number_format($beginningQuantity) }}
                                            </td>
                                        @endforeach

                                        {{-- Actual Distribution --}}
                                        @foreach (array_keys($ppeColumns) as $itemId)
                                            @php
                                                $actualQuantity = (int) ($actual[$itemId] ?? 0);
                                            @endphp

                                            <td
                                                class="border-r
                                                   border-[#7DD3FC]/30
                                                   bg-[#7DD3FC]/10
                                                   px-3 py-4
                                                   text-center font-bold
                                                   {{ $actualQuantity > 0 ? 'text-[#0EA5E9]' : 'text-slate-400' }}">
                                                {{ number_format($actualQuantity) }}
                                            </td>
                                        @endforeach

                                        {{-- Ending Inventory --}}
                                        @foreach (array_keys($ppeColumns) as $itemId)
                                            @php
                                                $endingQuantity = (int) ($ending[$itemId] ?? 0);
                                            @endphp

                                            <td
                                                class="border-r
                                                   border-slate-100
                                                   bg-slate-50
                                                   px-3 py-4
                                                   text-center font-bold
                                                   {{ $endingQuantity <= 0 ? 'text-red-700' : 'text-[#075985]' }}">
                                                {{ number_format($endingQuantity) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- =================================================
                    TABLE LEGEND
                ================================================== --}}
                    <div
                        class="flex flex-col gap-3 border-t
                           border-slate-200 bg-slate-50
                           px-6 py-4 text-xs text-slate-600
                           lg:flex-row lg:items-center
                           lg:justify-between">
                        <div class="flex flex-wrap gap-4">
                            <span>
                                <strong>Beginning</strong>
                                = Delivery Receipt balance before project
                            </span>

                            <span>
                                <strong>Actual Distribution</strong>
                                = PPE distributed to project
                            </span>

                            <span>
                                <strong>Ending</strong>
                                = Beginning − Actual Distribution
                            </span>
                        </div>

                        <p class="font-semibold">
                            Ending inventory becomes the next row's
                            beginning inventory.
                        </p>
                    </div>
                @endif
            </section>

            {{-- =========================================================
            PAGINATION
        ========================================================== --}}
            @if ($rows->hasPages())
                <section
                    class="rounded-2xl border border-slate-200
                       bg-white px-5 py-4 shadow-sm">
                    {{ $rows->links() }}
                </section>
            @endif
        @else
            <section
                class="rounded-3xl border border-slate-200
               bg-white px-6 py-20 text-center shadow-sm">
                <div
                    class="mx-auto flex h-16 w-16
                   items-center justify-center
                   rounded-2xl bg-slate-100
                   text-2xl">
                    📋
                </div>

                <h2 class="mt-5 text-xl font-bold text-slate-900">
                    Select a Delivery Receipt
                </h2>

                <p class="mx-auto mt-2 max-w-xl
                   text-sm leading-6 text-slate-500">
                    Choose one Delivery Receipt from the dropdown
                    to view its project distribution and remaining
                    PPE inventory.
                </p>
            </section>

        @endif
    </div>

</x-po_dashboard_layout>