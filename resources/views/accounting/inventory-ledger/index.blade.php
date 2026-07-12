<x-po_dashboard_layout title="Inventory Movement History">

    @php
        /*
        |--------------------------------------------------------------------------
        | PPE COLUMN CONFIGURATION
        |--------------------------------------------------------------------------
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

        $firstRowNumber = $rows->firstItem() ?? 1;

        $pageRows = $rows->getCollection();
    @endphp

    <div class="mx-auto max-w-[2000px] space-y-6">

        {{-- =========================================================
            HEADER
        ========================================================== --}}
        <section
            class="relative overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b from-[#641D21]
                       via-[#970C13] to-[#ED1B24]"
            ></div>

            <div
                class="flex flex-col gap-6 px-6 py-7
                       sm:px-8 lg:flex-row
                       lg:items-center lg:justify-between"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#DF979B]/20
                                   px-3 py-1 text-xs font-bold
                                   uppercase tracking-[0.16em]
                                   text-[#970C13]
                                   ring-1 ring-[#DF979B]"
                        >
                            Accounting Unit
                        </span>

                        <span
                            class="rounded-full bg-slate-100
                                   px-3 py-1 text-xs font-semibold
                                   text-slate-700
                                   ring-1 ring-slate-200"
                        >
                            Read Only
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold
                               tracking-tight text-slate-950
                               sm:text-3xl"
                    >
                        Inventory Movement History
                    </h1>

                    <p
                        class="mt-2 max-w-4xl text-sm
                               leading-6 text-slate-600"
                    >
                        Consolidated Call-Off inventory movement report
                        across Provincial Offices showing beginning
                        inventory, actual PPE distributed to projects,
                        and ending inventory.
                    </p>
                </div>

                <div
                    class="grid grid-cols-2 gap-3
                           sm:grid-cols-3"
                >
                    <div
                        class="rounded-2xl border border-slate-200
                               bg-slate-50 px-5 py-4"
                    >
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wider text-slate-400"
                        >
                            Reporting Year
                        </p>

                        <p
                            class="mt-1 text-2xl font-bold
                                   text-[#641D21]"
                        >
                            {{ $year }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200
                               bg-slate-50 px-5 py-4"
                    >
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wider text-slate-400"
                        >
                            Province
                        </p>

                        <p
                            class="mt-1 text-sm font-bold
                                   text-slate-900"
                        >
                            {{
                                $selectedProvince?->name
                                ?? 'All Offices'
                            }}
                        </p>
                    </div>

                    <div
                        class="col-span-2 rounded-2xl
                               border border-[#DF979B]
                               bg-[#DF979B]/10
                               px-5 py-4 sm:col-span-1"
                    >
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wider text-[#970C13]"
                        >
                            Access
                        </p>

                        <p
                            class="mt-1 text-sm font-bold
                                   text-[#641D21]"
                        >
                            View Only
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
            SUMMARY CARDS
        ========================================================== --}}
        <section
            class="grid grid-cols-1 gap-4
                   sm:grid-cols-2 xl:grid-cols-6"
        >
            @php
                $summaryCards = [
                    [
                        'label' => 'Provincial Offices',
                        'value' => $summary['province_count'] ?? 0,
                        'description' => 'Offices represented',
                        'color' => '#641D21',
                    ],

                    [
                        'label' => 'Call-Offs',
                        'value' => $summary['call_off_count'] ?? 0,
                        'description' => 'Call-Off allocations',
                        'color' => '#970C13',
                    ],

                    [
                        'label' => 'Projects',
                        'value' => $summary['project_count'] ?? 0,
                        'description' => 'Project distributions',
                        'color' => '#C51017',
                    ],

                    [
                        'label' => 'Beginning Inventory',
                        'value' => $summary['beginning_total'] ?? 0,
                        'description' => 'Opening Call-Off balances',
                        'color' => '#DF979B',
                    ],

                    [
                        'label' => 'Actual Distributed',
                        'value' => $summary['actual_total'] ?? 0,
                        'description' => 'PPE issued to projects',
                        'color' => '#ED1B24',
                    ],

                    [
                        'label' => 'Ending Inventory',
                        'value' => $summary['ending_total'] ?? 0,
                        'description' => 'Remaining Call-Off PPE',
                        'color' => '#641D21',
                    ],
                ];
            @endphp

            @foreach($summaryCards as $card)
                <article
                    class="group rounded-2xl
                           border border-slate-200
                           bg-white p-5 shadow-sm
                           transition hover:-translate-y-1
                           hover:shadow-md"
                >
                    <div
                        class="mb-4 h-1 w-10 rounded-full
                               transition-all group-hover:w-16"
                        style="background-color:
                            {{ $card['color'] }}"
                    ></div>

                    <p
                        class="text-xs font-bold uppercase
                               tracking-wider text-slate-400"
                    >
                        {{ $card['label'] }}
                    </p>

                    <p
                        class="mt-3 text-3xl font-bold
                               text-slate-950"
                    >
                        {{
                            number_format(
                                $card['value']
                            )
                        }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ $card['description'] }}
                    </p>
                </article>
            @endforeach
        </section>

        {{-- =========================================================
            FILTERS
        ========================================================== --}}
        <section
            class="rounded-3xl border border-slate-200
                   bg-white p-5 shadow-sm sm:p-6"
        >
            <form
                method="GET"
                action="{{ route(
                    'accounting.inventory-ledger.index'
                ) }}"
                class="grid grid-cols-1 gap-4
                       md:grid-cols-2 xl:grid-cols-12"
            >
                {{-- Search --}}
                <div class="xl:col-span-4">
                    <label
                        for="search"
                        class="mb-2 block text-xs font-bold
                               uppercase tracking-wider
                               text-slate-500"
                    >
                        Search Report
                    </label>

                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Call-Off, supplier, project..."
                        class="w-full rounded-xl
                               border-slate-300
                               focus:border-[#970C13]
                               focus:ring-[#970C13]"
                    >
                </div>

                {{-- Province --}}
                <div class="xl:col-span-2">
                    <label
                        for="province_id"
                        class="mb-2 block text-xs font-bold
                               uppercase tracking-wider
                               text-slate-500"
                    >
                        Provincial Office
                    </label>

                    <select
                        id="province_id"
                        name="province_id"
                        class="w-full rounded-xl
                               border-slate-300
                               focus:border-[#970C13]
                               focus:ring-[#970C13]"
                    >
                        <option value="">
                            All Provincial Offices
                        </option>

                        @foreach($provinces as $province)
                            <option
                                value="{{ $province->id }}"
                                @selected(
                                    $provinceId
                                    === (int) $province->id
                                )
                            >
                                {{ $province->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Call-Off --}}
                <div class="xl:col-span-3">
                    <label
                        for="province_distribution_id"
                        class="mb-2 block text-xs font-bold
                               uppercase tracking-wider
                               text-slate-500"
                    >
                        Call-Off
                    </label>

                    <select
                        id="province_distribution_id"
                        name="province_distribution_id"
                        class="w-full rounded-xl
                               border-slate-300
                               focus:border-[#970C13]
                               focus:ring-[#970C13]"
                    >
                        <option value="">
                            All Call-Offs
                        </option>

                        @foreach(
                            $callOffAllocations as $allocation
                        )
                            @php
                                $filterCallOff = $allocation
                                    ->distributionBatch
                                    ?->callOff;

                                $filterSupplier = $allocation
                                    ->distributionBatch
                                    ?->purchaseOrder
                                    ?->supplier;
                            @endphp

                            <option
                                value="{{ $allocation->id }}"
                                @selected(
                                    $callOffId
                                    === (int) $allocation->id
                                )
                            >
                                {{ $allocation->province?->name }}
                                —
                                {{
                                    $filterCallOff
                                        ?->call_off_number
                                    ?? 'No Call-Off'
                                }}

                                @if(
                                    $filterSupplier
                                        ?->supplier_name
                                )
                                    —
                                    {{
                                        $filterSupplier
                                            ->supplier_name
                                    }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Year --}}
                <div class="xl:col-span-1">
                    <label
                        for="year"
                        class="mb-2 block text-xs font-bold
                               uppercase tracking-wider
                               text-slate-500"
                    >
                        Year
                    </label>

                    <select
                        id="year"
                        name="year"
                        class="w-full rounded-xl
                               border-slate-300
                               focus:border-[#970C13]
                               focus:ring-[#970C13]"
                    >
                        @foreach(
                            $availableYears as $availableYear
                        )
                            <option
                                value="{{ $availableYear }}"
                                @selected(
                                    $year
                                    === (int) $availableYear
                                )
                            >
                                {{ $availableYear }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div
                    class="flex items-end gap-2
                           xl:col-span-2"
                >
                    <button
                        type="submit"
                        class="flex-1 rounded-xl
                               bg-[#970C13]
                               px-5 py-2.5
                               text-sm font-bold text-white
                               transition hover:bg-[#641D21]"
                    >
                        Apply
                    </button>

                    <a
                        href="{{ route(
                            'accounting.inventory-ledger.index'
                        ) }}"
                        class="rounded-xl border
                               border-slate-300 bg-white
                               px-5 py-2.5 text-sm
                               font-bold text-slate-700
                               transition hover:bg-slate-50"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </section>

        {{-- =========================================================
            REPORT EXPLANATION
        ========================================================== --}}
        <section
            class="grid grid-cols-1 gap-4 lg:grid-cols-3"
        >
            <article
                class="rounded-2xl border border-slate-200
                       bg-slate-50 p-5"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-slate-500"
                >
                    Beginning Inventory
                </p>

                <p class="mt-2 text-sm leading-6 text-slate-700">
                    Remaining PPE balance of the Call-Off before
                    the project distribution.
                </p>
            </article>

            <article
                class="rounded-2xl border border-[#DF979B]
                       bg-[#DF979B]/10 p-5"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-[#970C13]"
                >
                    Actual Inventory
                </p>

                <p class="mt-2 text-sm leading-6 text-slate-700">
                    Actual PPE quantities distributed by the
                    Provincial Office to the identified project.
                </p>
            </article>

            <article
                class="rounded-2xl border border-slate-200
                       bg-slate-50 p-5"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-slate-500"
                >
                    Ending Inventory
                </p>

                <p class="mt-2 text-sm leading-6 text-slate-700">
                    Beginning Inventory minus Actual Inventory.
                    The result becomes the next project's
                    beginning balance.
                </p>
            </article>
        </section>

        {{-- =========================================================
            TABLE
        ========================================================== --}}
        <section
            class="overflow-hidden rounded-3xl
                   border border-slate-200
                   bg-white shadow-sm"
        >
            <div
                class="flex flex-col gap-3
                       border-b border-slate-200
                       px-6 py-5 lg:flex-row
                       lg:items-center
                       lg:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase
                               tracking-[0.16em]
                               text-[#970C13]"
                    >
                        Accounting Read-Only Report
                    </p>

                    <h2
                        class="mt-1 text-xl font-bold
                               text-slate-950"
                    >
                        Inventory Movement History
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Consolidated Call-Off and project PPE
                        movement records.
                    </p>
                </div>

                <div
                    class="rounded-xl bg-slate-100
                           px-4 py-2 text-sm
                           font-semibold text-slate-600"
                >
                    {{
                        number_format(
                            $summary['row_count'] ?? 0
                        )
                    }}
                    report rows
                </div>
            </div>

            @if($rows->isEmpty())
                <div class="px-6 py-20 text-center">
                    <div
                        class="mx-auto flex h-16 w-16
                               items-center justify-center
                               rounded-2xl bg-slate-100
                               text-2xl"
                    >
                        📋
                    </div>

                    <h3
                        class="mt-5 text-lg font-bold
                               text-slate-900"
                    >
                        No inventory movement records found
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-xl
                               text-sm leading-6
                               text-slate-500"
                    >
                        No Call-Off inventory records match
                        the selected Accounting report filters.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[3500px]
                               border-collapse text-sm"
                    >
                        <thead>
                            {{-- MAIN HEADER --}}
                            <tr
                                class="border-b border-[#641D21]
                                       bg-[#641D21] text-white"
                            >
                                <th
                                    rowspan="3"
                                    class="sticky left-0 z-30
                                           min-w-[70px]
                                           border-r border-white/20
                                           bg-[#641D21]
                                           px-3 py-4 text-center"
                                >
                                    No.
                                </th>

                                <th
                                    rowspan="3"
                                    class="sticky left-[70px] z-30
                                           min-w-[180px]
                                           border-r border-white/20
                                           bg-[#641D21]
                                           px-4 py-4 text-left"
                                >
                                    Provincial Office
                                </th>

                                <th
                                    rowspan="3"
                                    class="min-w-[170px]
                                           border-r border-white/20
                                           px-4 py-4 text-left"
                                >
                                    Call-Off Number
                                </th>

                                <th
                                    rowspan="3"
                                    class="min-w-[230px]
                                           border-r border-white/20
                                           px-4 py-4 text-left"
                                >
                                    Name of Supplier
                                </th>

                                <th
                                    rowspan="3"
                                    class="min-w-[180px]
                                           border-r border-white/20
                                           px-4 py-4 text-left"
                                >
                                    Delivery Receipt
                                </th>

                                <th
                                    rowspan="3"
                                    class="min-w-[150px]
                                           border-r border-white/20
                                           px-4 py-4 text-left"
                                >
                                    Date of Delivery
                                </th>

                                <th
                                    rowspan="3"
                                    class="min-w-[190px]
                                           border-r border-white/20
                                           px-4 py-4 text-left"
                                >
                                    Project Code
                                </th>

                                <th
                                    rowspan="3"
                                    class="min-w-[220px]
                                           border-r border-white/20
                                           px-4 py-4 text-left"
                                >
                                    Location
                                </th>

                                <th
                                    rowspan="3"
                                    class="min-w-[150px]
                                           border-r border-white/20
                                           px-4 py-4 text-center"
                                >
                                    Number of Beneficiaries
                                </th>

                                <th
                                    rowspan="3"
                                    class="min-w-[120px]
                                           border-r border-white/20
                                           px-4 py-4 text-center"
                                >
                                    Number of Days
                                </th>

                                <th
                                    colspan="7"
                                    class="border-r border-white/20
                                           bg-[#970C13]
                                           px-4 py-4 text-center
                                           font-bold uppercase
                                           tracking-wider"
                                >
                                    Beginning Inventory
                                </th>

                                <th
                                    colspan="7"
                                    class="border-r border-white/20
                                           bg-[#C51017]
                                           px-4 py-4 text-center
                                           font-bold uppercase
                                           tracking-wider"
                                >
                                    Actual Inventory
                                </th>

                                <th
                                    colspan="7"
                                    class="bg-[#641D21]
                                           px-4 py-4 text-center
                                           font-bold uppercase
                                           tracking-wider"
                                >
                                    Ending Inventory
                                </th>
                            </tr>

                            {{-- PPE GROUP HEADER --}}
                            <tr
                                class="bg-[#970C13]
                                       text-white"
                            >
                                @foreach([
                                    '#970C13',
                                    '#C51017',
                                    '#641D21',
                                ] as $sectionColor)
                                    <th
                                        colspan="2"
                                        class="border-r
                                               border-white/20
                                               px-3 py-3
                                               text-center"
                                        style="background-color:
                                            {{ $sectionColor }}"
                                    >
                                        Long Sleeve
                                    </th>

                                    <th
                                        rowspan="2"
                                        class="min-w-[105px]
                                               border-r
                                               border-white/20
                                               px-3 py-3
                                               text-center"
                                        style="background-color:
                                            {{ $sectionColor }}"
                                    >
                                        Bucket Hat
                                    </th>

                                    <th
                                        colspan="2"
                                        class="border-r
                                               border-white/20
                                               px-3 py-3
                                               text-center"
                                        style="background-color:
                                            {{ $sectionColor }}"
                                    >
                                        Rubber Boots
                                    </th>

                                    <th
                                        rowspan="2"
                                        class="min-w-[100px]
                                               border-r
                                               border-white/20
                                               px-3 py-3
                                               text-center"
                                        style="background-color:
                                            {{ $sectionColor }}"
                                    >
                                        Gloves
                                    </th>

                                    <th
                                        rowspan="2"
                                        class="min-w-[100px]
                                               border-r
                                               border-white/20
                                               px-3 py-3
                                               text-center"
                                        style="background-color:
                                            {{ $sectionColor }}"
                                    >
                                        Mask
                                    </th>
                                @endforeach
                            </tr>

                            {{-- SIZE HEADER --}}
                            <tr
                                class="bg-[#DF979B]
                                       text-[#641D21]"
                            >
                                @for($section = 0; $section < 3; $section++)
                                    <th class="px-3 py-2 text-center">
                                        Medium
                                    </th>

                                    <th
                                        class="border-r
                                               border-[#970C13]/20
                                               px-3 py-2
                                               text-center"
                                    >
                                        Large
                                    </th>

                                    <th class="px-3 py-2 text-center">
                                        US9
                                    </th>

                                    <th
                                        class="border-r
                                               border-[#970C13]/20
                                               px-3 py-2
                                               text-center"
                                    >
                                        US10
                                    </th>
                                @endfor
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200">
                            @foreach($pageRows as $index => $row)
                                @php
                                    $beginning =
                                        $row['beginning'] ?? [];

                                    $actual =
                                        $row['actual'] ?? [];

                                    $ending =
                                        $row['ending'] ?? [];

                                    $isOpeningRow = empty(
                                        $row[
                                            'supply_designation_id'
                                        ]
                                    );

                                    $previousRow = $index > 0
                                        ? $pageRows->get(
                                            $index - 1
                                        )
                                        : null;

                                    $currentAllocationId = (int) (
                                        $row[
                                            'province_distribution_id'
                                        ]
                                        ?? 0
                                    );

                                    $previousAllocationId = (int) (
                                        $previousRow[
                                            'province_distribution_id'
                                        ]
                                        ?? 0
                                    );

                                    $currentProvinceId = (int) (
                                        $row['province_id']
                                        ?? 0
                                    );

                                    $previousProvinceId = (int) (
                                        $previousRow[
                                            'province_id'
                                        ]
                                        ?? 0
                                    );

                                    $isNewCallOff =
                                        $index === 0
                                        || $currentProvinceId
                                            !== $previousProvinceId
                                        || $currentAllocationId
                                            !== $previousAllocationId;

                                    $deliveryReceipts =
                                        $row[
                                            'delivery_receipts'
                                        ]
                                        ?? collect();
                                @endphp

                                <tr
                                    class="transition
                                           hover:bg-[#DF979B]/10
                                           {{
                                               $isNewCallOff
                                                   ? 'border-t-4 border-t-[#970C13]'
                                                   : ''
                                           }}"
                                >
                                    {{-- Number --}}
                                    <td
                                        class="sticky left-0 z-20
                                               border-r border-slate-200
                                               bg-white px-3 py-4
                                               text-center font-semibold
                                               text-slate-500"
                                    >
                                        {{
                                            $firstRowNumber
                                            + $index
                                        }}
                                    </td>

                                    {{-- Province --}}
                                    <td
                                        class="sticky left-[70px] z-20
                                               border-r border-slate-200
                                               bg-white px-4 py-4"
                                    >
                                        <span
                                            class="inline-flex rounded-lg
                                                   bg-slate-100
                                                   px-3 py-1.5
                                                   text-xs font-bold
                                                   text-slate-700
                                                   ring-1 ring-slate-200"
                                        >
                                            {{
                                                $row['province_name']
                                                ?? '—'
                                            }}
                                        </span>
                                    </td>

                                    {{-- Call-Off --}}
                                    <td
                                        class="border-r border-slate-100
                                               px-4 py-4"
                                    >
                                        <span
                                            class="inline-flex rounded-lg
                                                   bg-[#970C13]/10
                                                   px-3 py-1.5
                                                   font-bold text-[#970C13]
                                                   ring-1
                                                   ring-[#970C13]/20"
                                        >
                                            {{
                                                $row[
                                                    'call_off_number'
                                                ]
                                                ?? '—'
                                            }}
                                        </span>
                                    </td>

                                    {{-- Supplier --}}
                                    <td
                                        class="border-r border-slate-100
                                               px-4 py-4 font-medium
                                               text-slate-800"
                                    >
                                        {{
                                            $row[
                                                'supplier_name'
                                            ]
                                            ?? '—'
                                        }}
                                    </td>

                                    {{-- DR --}}
                                    <td
                                        class="border-r border-slate-100
                                               px-4 py-4"
                                    >
                                        @if(
                                            $deliveryReceipts
                                                ->isNotEmpty()
                                        )
                                            <div class="space-y-1.5">
                                                @foreach(
                                                    $deliveryReceipts
                                                    as $receipt
                                                )
                                                    <div
                                                        class="rounded-lg
                                                               bg-slate-100
                                                               px-2.5 py-1.5
                                                               text-xs
                                                               font-semibold
                                                               text-slate-700"
                                                    >
                                                        {{
                                                            $receipt
                                                                ->dr_number
                                                            ?? '—'
                                                        }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Delivery Date --}}
                                    <td
                                        class="border-r border-slate-100
                                               px-4 py-4"
                                    >
                                        @if(
                                            $deliveryReceipts
                                                ->isNotEmpty()
                                        )
                                            <div class="space-y-1.5">
                                                @foreach(
                                                    $deliveryReceipts
                                                    as $receipt
                                                )
                                                    <div
                                                        class="whitespace-nowrap
                                                               text-xs
                                                               text-slate-600"
                                                    >
                                                        {{
                                                            $receipt
                                                                ->delivery_date
                                                                ?->format(
                                                                    'M d, Y'
                                                                )
                                                            ?? '—'
                                                        }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Project --}}
                                    <td
                                        class="border-r border-slate-100
                                               px-4 py-4"
                                    >
                                        @if($isOpeningRow)
                                            <span
                                                class="rounded-lg
                                                       bg-amber-50
                                                       px-2.5 py-1.5
                                                       text-xs font-bold
                                                       text-amber-700
                                                       ring-1
                                                       ring-amber-200"
                                            >
                                                No Project Yet
                                            </span>
                                        @else
                                            <p
                                                class="font-bold
                                                       text-slate-900"
                                            >
                                                {{
                                                    $row[
                                                        'project_code'
                                                    ]
                                                    ?? '—'
                                                }}
                                            </p>

                                            <p
                                                class="mt-1 text-xs
                                                       text-slate-500"
                                            >
                                                {{
                                                    $row[
                                                        'project_title'
                                                    ]
                                                    ?? '—'
                                                }}
                                            </p>
                                        @endif
                                    </td>

                                    {{-- Location --}}
                                    <td
                                        class="border-r border-slate-100
                                               px-4 py-4
                                               text-slate-700"
                                    >
                                        {{
                                            $row['location']
                                            ?? '—'
                                        }}
                                    </td>

                                    {{-- Beneficiaries --}}
                                    <td
                                        class="border-r border-slate-100
                                               px-4 py-4 text-center
                                               font-semibold"
                                    >
                                        {{
                                            number_format(
                                                (int) (
                                                    $row[
                                                        'number_of_beneficiaries'
                                                    ]
                                                    ?? 0
                                                )
                                            )
                                        }}
                                    </td>

                                    {{-- Days --}}
                                    <td
                                        class="border-r border-slate-200
                                               px-4 py-4 text-center
                                               font-semibold"
                                    >
                                        {{
                                            number_format(
                                                (int) (
                                                    $row[
                                                        'number_of_days'
                                                    ]
                                                    ?? 0
                                                )
                                            )
                                        }}
                                    </td>

                                    {{-- Beginning --}}
                                    @foreach(
                                        array_keys($ppeColumns)
                                        as $itemId
                                    )
                                        <td
                                            class="border-r
                                                   border-slate-100
                                                   bg-slate-50/70
                                                   px-3 py-4
                                                   text-center
                                                   font-semibold
                                                   text-slate-800"
                                        >
                                            {{
                                                number_format(
                                                    (int) (
                                                        $beginning[
                                                            $itemId
                                                        ]
                                                        ?? 0
                                                    )
                                                )
                                            }}
                                        </td>
                                    @endforeach

                                    {{-- Actual --}}
                                    @foreach(
                                        array_keys($ppeColumns)
                                        as $itemId
                                    )
                                        @php
                                            $actualQuantity = (int) (
                                                $actual[$itemId]
                                                ?? 0
                                            );
                                        @endphp

                                        <td
                                            class="border-r
                                                   border-[#DF979B]/30
                                                   bg-[#DF979B]/10
                                                   px-3 py-4
                                                   text-center font-bold
                                                   {{
                                                       $actualQuantity > 0
                                                           ? 'text-[#C51017]'
                                                           : 'text-slate-400'
                                                   }}"
                                        >
                                            {{
                                                number_format(
                                                    $actualQuantity
                                                )
                                            }}
                                        </td>
                                    @endforeach

                                    {{-- Ending --}}
                                    @foreach(
                                        array_keys($ppeColumns)
                                        as $itemId
                                    )
                                        @php
                                            $endingQuantity = (int) (
                                                $ending[$itemId]
                                                ?? 0
                                            );
                                        @endphp

                                        <td
                                            class="border-r
                                                   border-slate-100
                                                   bg-slate-50
                                                   px-3 py-4
                                                   text-center font-bold
                                                   {{
                                                       $endingQuantity <= 0
                                                           ? 'text-red-700'
                                                           : 'text-[#641D21]'
                                                   }}"
                                        >
                                            {{
                                                number_format(
                                                    $endingQuantity
                                                )
                                            }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- TABLE LEGEND --}}
                <div
                    class="flex flex-col gap-3
                           border-t border-slate-200
                           bg-slate-50 px-6 py-4
                           text-xs text-slate-600
                           lg:flex-row lg:items-center
                           lg:justify-between"
                >
                    <div class="flex flex-wrap gap-4">
                        <span>
                            <strong>Beginning</strong>
                            = Call-Off balance before project
                        </span>

                        <span>
                            <strong>Actual</strong>
                            = PPE distributed to project
                        </span>

                        <span>
                            <strong>Ending</strong>
                            = Beginning − Actual
                        </span>
                    </div>

                    <p class="font-semibold">
                        Accounting access is strictly read-only.
                    </p>
                </div>
            @endif
        </section>

        {{-- =========================================================
            PAGINATION
        ========================================================== --}}
        @if($rows->hasPages())
            <section
                class="rounded-2xl border border-slate-200
                       bg-white px-5 py-4 shadow-sm"
            >
                {{ $rows->links() }}
            </section>
        @endif

    </div>

</x-po_dashboard_layout>