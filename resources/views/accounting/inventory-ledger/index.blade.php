<x-po_dashboard_layout :title="$viewerLabel . ' PPE Monitoring'">

    @php
        /*
        |--------------------------------------------------------------------------
        | PPE COLUMN CONFIGURATION
        |--------------------------------------------------------------------------
        |
        | These item IDs correspond to the seven PPE variants currently
        | used by the system.
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

        $firstRowNumber =
            $rows->firstItem() ?? 1;

        $pageRows =
            $rows->getCollection();

        $selectedAllocation =
            $callOffId > 0
                ? $callOffAllocations->firstWhere(
                    'id',
                    $callOffId
                )
                : null;

        $selectedCallOff =
            $selectedAllocation
                ?->distributionBatch
                ?->callOff;

        $selectedSupplier =
            $selectedAllocation
                ?->distributionBatch
                ?->purchaseOrder
                ?->supplier;


        /*
        |--------------------------------------------------------------------------
        | CONSOLIDATED PROVINCIAL INVENTORY
        |--------------------------------------------------------------------------
        */
        $provincialInventoryByProvince = collect($provinces)
            ->mapWithKeys(function ($province) use ($provincialInventories, $ppeColumns) {
                $records = $provincialInventories->where(
                    'province_id',
                    $province->id
                );

                $quantities = [];

                foreach (array_keys($ppeColumns) as $itemId) {
                    $quantities[$itemId] = (int) $records
                        ->where('item_id', $itemId)
                        ->sum('quantity');
                }

                $longSleeveTotal =
                    ($quantities[1] ?? 0)
                    + ($quantities[2] ?? 0);

                $rubberBootsTotal =
                    ($quantities[4] ?? 0)
                    + ($quantities[5] ?? 0);

                $totalPpe =
                    $longSleeveTotal
                    + ($quantities[3] ?? 0)
                    + $rubberBootsTotal
                    + ($quantities[6] ?? 0)
                    + ($quantities[7] ?? 0);

                return [
                    $province->id => [
                        'province' => $province,
                        'quantities' => $quantities,
                        'long_sleeve_total' => $longSleeveTotal,
                        'rubber_boots_total' => $rubberBootsTotal,
                        'total_ppe' => $totalPpe,
                    ],
                ];
            });
    @endphp

    <div class="mx-auto max-w-[2200px] space-y-6">

        {{-- =========================================================
            PAGE HEADER
        ========================================================== --}}
        <section
            class="relative overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b from-[#244E70]
                       via-[#2E628D] to-[#4FA3D9]"
            ></div>

            <div
                class="flex flex-col gap-6 px-6 py-7
                       sm:px-8 lg:flex-row
                       lg:items-center lg:justify-between"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#B8D9EE]/20
                                   px-3 py-1 text-xs font-bold
                                   uppercase tracking-[0.16em]
                                   text-[#2E628D]
                                   ring-1 ring-[#B8D9EE]"
                        >
                            {{ $viewerLabel }}
                        </span>

                        <span
                            class="rounded-full bg-slate-100
                                   px-3 py-1 text-xs font-semibold
                                   text-slate-700 ring-1
                                   ring-slate-200"
                        >
                            Read-Only Access
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold
                               tracking-tight text-slate-950
                               sm:text-3xl"
                    >
                        Inventory Monitoring and Ledger
                    </h1>

                    <p
                        class="mt-2 max-w-4xl text-sm
                               leading-6 text-slate-600"
                    >
                        {{ $viewerDescription }}
                    </p>
                </div>

                <div
                    class="grid grid-cols-2 gap-3
                           sm:grid-cols-3"
                >
                    <article
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
                                   text-[#244E70]"
                        >
                            {{ $year }}
                        </p>
                    </article>

                    <article
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
                    </article>

                    <article
                        class="col-span-2 rounded-2xl
                               border border-[#B8D9EE]
                               bg-[#B8D9EE]/10 px-5 py-4
                               sm:col-span-1"
                    >
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wider text-[#2E628D]"
                        >
                            Permission
                        </p>

                        <p
                            class="mt-1 text-sm font-bold
                                   text-[#244E70]"
                        >
                            View Only
                        </p>
                    </article>
                </div>
            </div>
        </section>

        {{-- =========================================================
            SUPPLY UNIT CENTRAL INVENTORY
        ========================================================== --}}
        <section
            class="overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="flex flex-col gap-4 border-b
                       border-slate-200 px-6 py-5
                       sm:px-7 lg:flex-row
                       lg:items-center lg:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase
                               tracking-[0.16em] text-[#2E628D]"
                    >
                        Supply Unit Inventory
                    </p>

                    <h2
                        class="mt-1 text-xl font-bold
                               text-slate-950"
                    >
                        Current Central PPE Inventory
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Current PPE quantities available under the
                        Supply Unit before allocation to Provincial
                        Offices.
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-[#B8D9EE]
                           bg-[#B8D9EE]/10 px-6 py-4"
                >
                    <p
                        class="text-xs font-bold uppercase
                               tracking-wider text-[#2E628D]"
                    >
                        Total Available PPE
                    </p>

                    <p
                        class="mt-1 text-3xl font-bold
                               text-[#244E70]"
                    >
                        {{
                            number_format(
                                $supplyInventoryTotal
                            )
                        }}
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table
                    class="w-full min-w-[900px]
                           border-collapse"
                >
                    <thead class="bg-[#244E70] text-white">
                        <tr
                            class="text-xs font-bold uppercase
                                   tracking-wide"
                        >
                            <th
                                class="w-20 border border-[#4F8DB8]
                                       px-5 py-4 text-center"
                            >
                                No.
                            </th>

                            <th
                                class="border border-[#4F8DB8]
                                       px-5 py-4 text-left"
                            >
                                PPE Item
                            </th>

                            <th
                                class="border border-[#4F8DB8]
                                       px-5 py-4 text-left"
                            >
                                Size / Label
                            </th>

                            <th
                                class="border border-[#4F8DB8]
                                       px-5 py-4 text-left"
                            >
                                Unit
                            </th>

                            <th
                                class="border border-[#4F8DB8]
                                       px-5 py-4 text-center"
                            >
                                Available Quantity
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse(
                            $supplyInventory as $inventoryRow
                        )
                            <tr
                                class="transition
                                       hover:bg-slate-50"
                            >
                                <td
                                    class="border border-slate-200
                                           px-5 py-4 text-center
                                           text-slate-500"
                                >
                                    {{ $loop->iteration }}
                                </td>

                                <td
                                    class="border border-slate-200
                                           px-5 py-4 font-bold
                                           text-slate-900"
                                >
                                    {{
                                        $inventoryRow
                                            ->item_name
                                    }}
                                </td>

                                <td
                                    class="border border-slate-200
                                           px-5 py-4 text-slate-600"
                                >
                                    {{
                                        $inventoryRow->label
                                        ?: '—'
                                    }}
                                </td>

                                <td
                                    class="border border-slate-200
                                           px-5 py-4 text-slate-600"
                                >
                                    {{
                                        $inventoryRow
                                            ->unit_of_measurement
                                        ?: '—'
                                    }}
                                </td>

                                <td
                                    class="border border-slate-200
                                           bg-[#B8D9EE]/10
                                           px-5 py-4 text-center
                                           text-lg font-bold
                                           text-[#244E70]"
                                >
                                    {{
                                        number_format(
                                            (int) $inventoryRow
                                                ->quantity
                                        )
                                    }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="border border-slate-200
                                           px-6 py-14 text-center
                                           text-slate-500"
                                >
                                    No Supply Unit inventory records
                                    were found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($supplyInventory->isNotEmpty())
                        <tfoot>
                            <tr class="bg-slate-100">
                                <td
                                    colspan="4"
                                    class="border border-slate-200
                                           px-5 py-4 text-right
                                           font-bold uppercase
                                           tracking-wide
                                           text-slate-700"
                                >
                                    Total Supply Unit Inventory
                                </td>

                                <td
                                    class="border border-slate-200
                                           px-5 py-4 text-center
                                           text-lg font-bold
                                           text-[#244E70]"
                                >
                                    {{
                                        number_format(
                                            $supplyInventoryTotal
                                        )
                                    }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>

        {{-- =========================================================
            CURRENT PROVINCIAL OFFICE INVENTORIES
        ========================================================== --}}
        <section
            id="provincial-inventory-summary"
            class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:px-7 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2E628D]">
                        Provincial Office Inventory
                    </p>

                    <h2 class="mt-1 text-xl font-bold text-slate-950">
                        Provincial Distribution Summary
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Consolidated current PPE balances of every Provincial Office.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 print:hidden">
                    <div class="rounded-2xl border border-[#B8D9EE] bg-[#B8D9EE]/10 px-6 py-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-[#2E628D]">
                            Total Provincial PPE
                        </p>

                        <p class="mt-1 text-3xl font-bold text-[#244E70]">
                            {{ number_format($provincialInventoryTotal) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        onclick="window.print()"
                        class="inline-flex items-center justify-center rounded-xl bg-[#2E628D] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#244E70]"
                    >
                        Print Inventory
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1250px] border-collapse text-sm">
                    <thead class="text-white">
                        <tr class="bg-[#244E70]">
                            <th
                                rowspan="2"
                                class="min-w-[210px] border border-[#4F8DB8] px-5 py-4 text-left font-bold"
                            >
                                Province
                            </th>

                            <th
                                colspan="3"
                                class="border border-[#4F8DB8] bg-[#2E628D] px-4 py-4 text-center font-bold"
                            >
                                Long Sleeves
                            </th>

                            <th
                                rowspan="2"
                                class="min-w-[120px] border border-[#4F8DB8] px-4 py-4 text-center font-bold"
                            >
                                Bucket Hat
                            </th>

                            <th
                                colspan="3"
                                class="border border-[#4F8DB8] bg-[#2E628D] px-4 py-4 text-center font-bold"
                            >
                                Rubber Boots
                            </th>

                            <th
                                rowspan="2"
                                class="min-w-[105px] border border-[#4F8DB8] px-4 py-4 text-center font-bold"
                            >
                                Gloves
                            </th>

                            <th
                                rowspan="2"
                                class="min-w-[105px] border border-[#4F8DB8] px-4 py-4 text-center font-bold"
                            >
                                Mask
                            </th>

                            <th
                                rowspan="2"
                                class="min-w-[130px] border border-[#4F8DB8] bg-[#2E628D] px-4 py-4 text-center font-bold"
                            >
                                Total PPE
                            </th>
                        </tr>

                        <tr class="bg-[#B8D9EE] text-[#244E70]">
                            <th class="border border-[#8ECDF2] px-4 py-3 text-center font-bold">M</th>
                            <th class="border border-[#8ECDF2] px-4 py-3 text-center font-bold">L</th>
                            <th class="border border-[#8ECDF2] px-4 py-3 text-center font-bold">Total</th>
                            <th class="border border-[#8ECDF2] px-4 py-3 text-center font-bold">US9</th>
                            <th class="border border-[#8ECDF2] px-4 py-3 text-center font-bold">US10</th>
                            <th class="border border-[#8ECDF2] px-4 py-3 text-center font-bold">Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($provincialInventoryByProvince as $provinceInventory)
                            @php
                                $quantities = $provinceInventory['quantities'];
                            @endphp

                            <tr class="transition odd:bg-white even:bg-slate-50 hover:bg-[#B8D9EE]/10">
                                <td class="border border-slate-200 px-5 py-4 font-bold text-slate-900">
                                    {{ $provinceInventory['province']->name }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($quantities[1] ?? 0) }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($quantities[2] ?? 0) }}
                                </td>

                                <td class="border border-slate-200 bg-slate-100 px-4 py-4 text-center font-bold text-[#244E70]">
                                    {{ number_format($provinceInventory['long_sleeve_total']) }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($quantities[3] ?? 0) }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($quantities[4] ?? 0) }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($quantities[5] ?? 0) }}
                                </td>

                                <td class="border border-slate-200 bg-slate-100 px-4 py-4 text-center font-bold text-[#244E70]">
                                    {{ number_format($provinceInventory['rubber_boots_total']) }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($quantities[6] ?? 0) }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($quantities[7] ?? 0) }}
                                </td>

                                <td class="border border-slate-200 bg-[#B8D9EE]/10 px-4 py-4 text-center text-base font-bold text-[#2E628D]">
                                    {{ number_format($provinceInventory['total_ppe']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="bg-[#244E70] text-white">
                            <td class="border border-[#4F8DB8] px-5 py-4 text-right font-bold uppercase tracking-wide">
                                Consolidated Total
                            </td>

                            <td class="border border-[#4F8DB8] px-4 py-4 text-center font-bold">
                                {{ number_format($provincialInventoryByProvince->sum(fn ($row) => $row['quantities'][1] ?? 0)) }}
                            </td>

                            <td class="border border-[#4F8DB8] px-4 py-4 text-center font-bold">
                                {{ number_format($provincialInventoryByProvince->sum(fn ($row) => $row['quantities'][2] ?? 0)) }}
                            </td>

                            <td class="border border-[#4F8DB8] bg-[#2E628D] px-4 py-4 text-center font-bold">
                                {{ number_format($provincialInventoryByProvince->sum('long_sleeve_total')) }}
                            </td>

                            <td class="border border-[#4F8DB8] px-4 py-4 text-center font-bold">
                                {{ number_format($provincialInventoryByProvince->sum(fn ($row) => $row['quantities'][3] ?? 0)) }}
                            </td>

                            <td class="border border-[#4F8DB8] px-4 py-4 text-center font-bold">
                                {{ number_format($provincialInventoryByProvince->sum(fn ($row) => $row['quantities'][4] ?? 0)) }}
                            </td>

                            <td class="border border-[#4F8DB8] px-4 py-4 text-center font-bold">
                                {{ number_format($provincialInventoryByProvince->sum(fn ($row) => $row['quantities'][5] ?? 0)) }}
                            </td>

                            <td class="border border-[#4F8DB8] bg-[#2E628D] px-4 py-4 text-center font-bold">
                                {{ number_format($provincialInventoryByProvince->sum('rubber_boots_total')) }}
                            </td>

                            <td class="border border-[#4F8DB8] px-4 py-4 text-center font-bold">
                                {{ number_format($provincialInventoryByProvince->sum(fn ($row) => $row['quantities'][6] ?? 0)) }}
                            </td>

                            <td class="border border-[#4F8DB8] px-4 py-4 text-center font-bold">
                                {{ number_format($provincialInventoryByProvince->sum(fn ($row) => $row['quantities'][7] ?? 0)) }}
                            </td>

                            <td class="border border-[#4F8DB8] bg-[#2E628D] px-4 py-4 text-center text-base font-bold">
                                {{ number_format($provincialInventoryByProvince->sum('total_ppe')) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        {{-- =========================================================
            PROVINCIAL LEDGER SUMMARY CARDS
        ========================================================== --}}
        <section
            class="grid grid-cols-1 gap-4
                   sm:grid-cols-2 xl:grid-cols-6"
        >
            @php
                $summaryCards = [
                    [
                        'label' => 'Provincial Offices',
                        'value' =>
                            $summary['province_count'] ?? 0,
                        'description' =>
                            'Offices represented',
                        'color' => '#244E70',
                    ],

                    [
                        'label' => 'Call-Offs',
                        'value' =>
                            $summary['call_off_count'] ?? 0,
                        'description' =>
                            'Call-Off allocations',
                        'color' => '#2E628D',
                    ],

                    [
                        'label' => 'Projects',
                        'value' =>
                            $summary['project_count'] ?? 0,
                        'description' =>
                            'Projects supplied',
                        'color' => '#3B82C4',
                    ],

                    [
                        'label' => 'Beginning Inventory',
                        'value' =>
                            $summary['beginning_total'] ?? 0,
                        'description' =>
                            'Opening balances',
                        'color' => '#B8D9EE',
                    ],

                    [
                        'label' => 'Actual Distributed',
                        'value' =>
                            $summary['actual_total'] ?? 0,
                        'description' =>
                            'PPE issued to projects',
                        'color' => '#4FA3D9',
                    ],

                    [
                        'label' => 'Ending Inventory',
                        'value' =>
                            $summary['ending_total'] ?? 0,
                        'description' =>
                            'Remaining PPE balances',
                        'color' => '#244E70',
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
                action="{{ url()->current() }}"
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
                        placeholder="Province, Call-Off, supplier, DR, project..."
                        class="w-full rounded-xl
                               border-slate-300
                               focus:border-[#2E628D]
                               focus:ring-[#2E628D]"
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
                               focus:border-[#2E628D]
                               focus:ring-[#2E628D]"
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
                        Call-Off Allocation
                    </label>

                    <select
                        id="province_distribution_id"
                        name="province_distribution_id"
                        class="w-full rounded-xl
                               border-slate-300
                               focus:border-[#2E628D]
                               focus:ring-[#2E628D]"
                    >
                        <option value="">
                            All Call-Offs
                        </option>

                        @foreach(
                            $callOffAllocations as $allocation
                        )
                            @php
                                $filterCallOff =
                                    $allocation
                                        ->distributionBatch
                                        ?->callOff;

                                $filterSupplier =
                                    $allocation
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
                                {{
                                    $allocation
                                        ->province
                                        ?->name
                                    ?? 'Province unavailable'
                                }}
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
                               focus:border-[#2E628D]
                               focus:ring-[#2E628D]"
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
                               bg-[#2E628D] px-5 py-2.5
                               text-sm font-bold text-white
                               transition hover:bg-[#244E70]"
                    >
                        Apply
                    </button>

                    <a
                        href="{{ url()->current() }}"
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
            ACTIVE FILTER INFORMATION
        ========================================================== --}}
        @if(
            $selectedProvince
            || $selectedAllocation
            || $search !== ''
        )
            <section
                class="rounded-2xl border border-[#B8D9EE]
                       bg-[#B8D9EE]/10 px-5 py-4"
            >
                <div class="flex flex-wrap items-center gap-3">
                    <span
                        class="text-xs font-bold uppercase
                               tracking-wider text-[#2E628D]"
                    >
                        Active Filters:
                    </span>

                    @if($selectedProvince)
                        <span
                            class="rounded-full bg-white px-3 py-1
                                   text-xs font-semibold
                                   text-slate-700 ring-1
                                   ring-slate-200"
                        >
                            Province:
                            {{ $selectedProvince->name }}
                        </span>
                    @endif

                    @if($selectedAllocation)
                        <span
                            class="rounded-full bg-white px-3 py-1
                                   text-xs font-semibold
                                   text-slate-700 ring-1
                                   ring-slate-200"
                        >
                            Call-Off:
                            {{
                                $selectedCallOff
                                    ?->call_off_number
                                ?? 'Unavailable'
                            }}
                        </span>

                        <span
                            class="rounded-full bg-white px-3 py-1
                                   text-xs font-semibold
                                   text-slate-700 ring-1
                                   ring-slate-200"
                        >
                            Supplier:
                            {{
                                $selectedSupplier
                                    ?->supplier_name
                                ?? 'Unavailable'
                            }}
                        </span>
                    @endif

                    @if($search !== '')
                        <span
                            class="rounded-full bg-white px-3 py-1
                                   text-xs font-semibold
                                   text-slate-700 ring-1
                                   ring-slate-200"
                        >
                            Search: {{ $search }}
                        </span>
                    @endif
                </div>
            </section>
        @endif

        {{-- =========================================================
            FORMULA INFORMATION
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
                    PPE balance available before the current project
                    distribution transaction.
                </p>
            </article>

            <article
                class="rounded-2xl border border-[#B8D9EE]
                       bg-[#B8D9EE]/10 p-5"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-[#2E628D]"
                >
                    Actual Distribution
                </p>

                <p class="mt-2 text-sm leading-6 text-slate-700">
                    Actual PPE quantity distributed by the Provincial
                    Office to the identified project.
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
                    Beginning Inventory minus Actual Distribution.
                    The ending quantity becomes the next transaction’s
                    beginning balance.
                </p>
            </article>
        </section>

        {{-- =========================================================
            ACCOUNTING INVENTORY LEDGER TABLE
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
                               text-[#2E628D]"
                    >
                        {{ $viewerLabel }} Read-Only Report
                    </p>

                    <h2
                        class="mt-1 text-xl font-bold
                               text-slate-950"
                    >
                        Provincial Inventory Movement History
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Consolidated Call-Off, Delivery Receipt, and
                        project PPE distribution records.
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
                        No provincial inventory records match the
                        currently selected report filters.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[3500px]
                               border-collapse text-sm"
                    >
                        <thead>
                            {{-- Main header --}}
                            <tr
                                class="border-b border-[#244E70]
                                       bg-[#244E70] text-white"
                            >
                                <th
                                    rowspan="3"
                                    class="sticky left-0 z-30
                                           min-w-[70px]
                                           border-r border-white/20
                                           bg-[#244E70]
                                           px-3 py-4 text-center"
                                >
                                    No.
                                </th>

                                <th
                                    rowspan="3"
                                    class="sticky left-[70px] z-30
                                           min-w-[180px]
                                           border-r border-white/20
                                           bg-[#244E70]
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
                                           bg-[#2E628D]
                                           px-4 py-4 text-center
                                           font-bold uppercase
                                           tracking-wider"
                                >
                                    Beginning Inventory
                                </th>

                                <th
                                    colspan="7"
                                    class="border-r border-white/20
                                           bg-[#3B82C4]
                                           px-4 py-4 text-center
                                           font-bold uppercase
                                           tracking-wider"
                                >
                                    Actual Distribution
                                </th>

                                <th
                                    colspan="7"
                                    class="bg-[#244E70]
                                           px-4 py-4 text-center
                                           font-bold uppercase
                                           tracking-wider"
                                >
                                    Ending Inventory
                                </th>
                            </tr>

                            {{-- PPE group header --}}
                            <tr class="bg-[#2E628D] text-white">
                                @foreach([
                                    '#2E628D',
                                    '#3B82C4',
                                    '#244E70',
                                ] as $sectionColor)
                                    <th
                                        colspan="2"
                                        class="border-r
                                               border-white/20
                                               px-3 py-3 text-center"
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
                                               px-3 py-3 text-center"
                                        style="background-color:
                                            {{ $sectionColor }}"
                                    >
                                        Bucket Hat
                                    </th>

                                    <th
                                        colspan="2"
                                        class="border-r
                                               border-white/20
                                               px-3 py-3 text-center"
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
                                               px-3 py-3 text-center"
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
                                               px-3 py-3 text-center"
                                        style="background-color:
                                            {{ $sectionColor }}"
                                    >
                                        Mask
                                    </th>
                                @endforeach
                            </tr>

                            {{-- Size header --}}
                            <tr
                                class="bg-[#B8D9EE]
                                       text-[#244E70]"
                            >
                                @for(
                                    $section = 0;
                                    $section < 3;
                                    $section++
                                )
                                    <th
                                        class="px-3 py-2
                                               text-center"
                                    >
                                        Medium
                                    </th>

                                    <th
                                        class="border-r
                                               border-[#2E628D]/20
                                               px-3 py-2
                                               text-center"
                                    >
                                        Large
                                    </th>

                                    <th
                                        class="px-3 py-2
                                               text-center"
                                    >
                                        US9
                                    </th>

                                    <th
                                        class="border-r
                                               border-[#2E628D]/20
                                               px-3 py-2
                                               text-center"
                                    >
                                        US10
                                    </th>
                                @endfor
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200">
                            @foreach(
                                $pageRows as $index => $row
                            )
                                @php
                                    $beginning =
                                        $row['beginning']
                                        ?? [];

                                    $actual =
                                        $row['actual']
                                        ?? [];

                                    $ending =
                                        $row['ending']
                                        ?? [];

                                    $isOpeningRow = empty(
                                        $row[
                                            'supply_designation_id'
                                        ]
                                    );

                                    $previousRow =
                                        $index > 0
                                            ? $pageRows->get(
                                                $index - 1
                                            )
                                            : null;

                                    $currentAllocationId =
                                        (int) (
                                            $row[
                                                'province_distribution_id'
                                            ]
                                            ?? 0
                                        );

                                    $previousAllocationId =
                                        (int) (
                                            $previousRow[
                                                'province_distribution_id'
                                            ]
                                            ?? 0
                                        );

                                    $currentProvinceId =
                                        (int) (
                                            $row[
                                                'province_id'
                                            ]
                                            ?? 0
                                        );

                                    $previousProvinceId =
                                        (int) (
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
                                @endphp

                                <tr
                                    class="transition
                                           hover:bg-[#B8D9EE]/10
                                           {{
                                               $isNewCallOff
                                                   ? 'border-t-4 border-t-[#2E628D]'
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
                                                $row[
                                                    'province_name'
                                                ]
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
                                                   bg-[#2E628D]/10
                                                   px-3 py-1.5
                                                   font-bold text-[#2E628D]
                                                   ring-1
                                                   ring-[#2E628D]/20"
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

                                    {{-- Delivery Receipt --}}
                                    <td
                                        class="border-r border-slate-100
                                               px-4 py-4"
                                    >
                                        <span
                                            class="inline-flex rounded-lg
                                                   bg-slate-100
                                                   px-2.5 py-1.5
                                                   text-xs font-semibold
                                                   text-slate-700"
                                        >
                                            {{
                                                $row[
                                                    'delivery_receipt_number'
                                                ]
                                                ?? '—'
                                            }}
                                        </span>
                                    </td>

                                    {{-- Delivery Date --}}
                                    <td
                                        class="border-r border-slate-100
                                               px-4 py-4"
                                    >
                                        <span
                                            class="whitespace-nowrap
                                                   text-xs text-slate-600"
                                        >
                                            {{
                                                isset(
                                                    $row[
                                                        'delivery_date'
                                                    ]
                                                )
                                                && $row[
                                                    'delivery_date'
                                                ]
                                                    ? \Illuminate\Support\Carbon::parse(
                                                        $row['delivery_date']
                                                    )->format('M d, Y')
                                                    : '—'
                                            }}
                                        </span>
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

                                    {{-- Number of days --}}
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

                                    {{-- Beginning inventory --}}
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

                                    {{-- Actual distribution --}}
                                    @foreach(
                                        array_keys($ppeColumns)
                                        as $itemId
                                    )
                                        @php
                                            $actualQuantity =
                                                (int) (
                                                    $actual[
                                                        $itemId
                                                    ]
                                                    ?? 0
                                                );
                                        @endphp

                                        <td
                                            class="border-r
                                                   border-[#B8D9EE]/30
                                                   bg-[#B8D9EE]/10
                                                   px-3 py-4
                                                   text-center font-bold
                                                   {{
                                                       $actualQuantity > 0
                                                           ? 'text-[#3B82C4]'
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

                                    {{-- Ending inventory --}}
                                    @foreach(
                                        array_keys($ppeColumns)
                                        as $itemId
                                    )
                                        @php
                                            $endingQuantity =
                                                (int) (
                                                    $ending[
                                                        $itemId
                                                    ]
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
                                                           : 'text-[#244E70]'
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

                {{-- Table legend --}}
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
                            = balance before the current project
                        </span>

                        <span>
                            <strong>Actual Distribution</strong>
                            = PPE distributed to the project
                        </span>

                        <span>
                            <strong>Ending</strong>
                            = Beginning − Actual Distribution
                        </span>
                    </div>

                    <p class="font-semibold">
                        {{ $viewerLabel }} access is strictly read-only.
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