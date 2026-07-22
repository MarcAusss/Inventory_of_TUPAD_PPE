<x-po_dashboard_layout title="Current Provincial Inventory">

    @php
        $ppeLabels = [
            'long_sleeve_medium' => 'Long Sleeve Medium',
            'long_sleeve_large' => 'Long Sleeve Large',
            'bucket_hat' => 'Bucket Hat',
            'rubber_boots_us9' => 'Rubber Boots US9',
            'rubber_boots_us10' => 'Rubber Boots US10',
            'hand_gloves' => 'Hand Gloves',
            'mask' => 'Mask',
        ];

        $ppeShortLabels = [
            'long_sleeve_medium' => 'LS M',
            'long_sleeve_large' => 'LS L',
            'bucket_hat' => 'Bucket Hat',
            'rubber_boots_us9' => 'Boots US9',
            'rubber_boots_us10' => 'Boots US10',
            'hand_gloves' => 'Gloves',
            'mask' => 'Mask',
        ];

        $summary = $summary ?? [];
        $callOffAllocations = $callOffAllocations ?? collect();
        $recentReceipts = $recentReceipts ?? collect();
    @endphp

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- =========================================================
            PAGE HEADER
        ========================================================== --}}
        <section
            class="relative overflow-hidden rounded-3xl border
                   border-slate-200 bg-white shadow-sm"
        >
            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b from-[#075985]
                       via-[#0284C7] to-[#38BDF8]"
            ></div>

            <div
                class="flex flex-col gap-6 px-6 py-7 sm:px-8
                       lg:flex-row lg:items-center
                       lg:justify-between"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#7DD3FC]/20
                                   px-3 py-1 text-xs font-bold
                                   uppercase tracking-[0.16em]
                                   text-[#0284C7]
                                   ring-1 ring-[#7DD3FC]"
                        >
                            Provincial Office
                        </span>

                        <span
                            class="rounded-full bg-slate-100
                                   px-3 py-1 text-xs font-semibold
                                   text-slate-700 ring-1
                                   ring-slate-200"
                        >
                            Current Inventory
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold
                               tracking-tight text-slate-950
                               sm:text-3xl"
                    >
                        Provincial PPE Inventory
                    </h1>

                    <p
                        class="mt-2 max-w-3xl text-sm
                               leading-6 text-slate-600"
                    >
                        Review the current provincial stock, PPE received
                        per Call-Off, Delivery Receipt details, and recent
                        additions to inventory.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route(
                            'provincial.receiving.index'
                        ) }}"
                        class="inline-flex items-center justify-center
                               rounded-xl border border-slate-300
                               bg-white px-5 py-3 text-sm
                               font-bold text-slate-700 transition
                               hover:bg-slate-50"
                    >
                        View Receiving
                    </a>

                    <a
                        href="{{ route(
                            'provincial.inventory-ledger.index'
                        ) }}"
                        class="inline-flex items-center justify-center
                               rounded-xl bg-[#0284C7] px-5 py-3
                               text-sm font-bold text-white
                               transition hover:bg-[#075985]"
                    >
                        Inventory Movement History
                    </a>
                </div>
            </div>
        </section>

        {{-- =========================================================
            SEARCH
        ========================================================== --}}
        <section
            class="rounded-2xl border border-slate-200
                   bg-white p-5 shadow-sm"
        >
            <form
                method="GET"
                action="{{ route(
                    'provincial.current-inventory.index'
                ) }}"
                class="flex flex-col gap-3 sm:flex-row
                       sm:items-center"
            >
                <div class="flex-1">
                    <label
                        for="search"
                        class="sr-only"
                    >
                        Search inventory
                    </label>

                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search PPE, Call-Off, PO, supplier, or Delivery Receipt..."
                        class="w-full rounded-xl border-slate-300
                               focus:border-[#0284C7]
                               focus:ring-[#0284C7]"
                    >
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center
                           rounded-xl bg-[#0284C7] px-6 py-3
                           text-sm font-bold text-white
                           transition hover:bg-[#075985]"
                >
                    Search
                </button>

                @if($search !== '')
                    <a
                        href="{{ route(
                            'provincial.current-inventory.index'
                        ) }}"
                        class="inline-flex items-center justify-center
                               rounded-xl border border-slate-300
                               bg-white px-6 py-3 text-sm
                               font-bold text-slate-700
                               transition hover:bg-slate-50"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </section>

        {{-- =========================================================
            INVENTORY OVERVIEW
        ========================================================== --}}
        <section
            class="grid grid-cols-1 gap-4 sm:grid-cols-2
                   xl:grid-cols-4"
        >
            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-slate-400"
                >
                    Total Current PPE
                </p>

                <p
                    class="mt-3 text-3xl font-bold text-[#075985]"
                >
                    {{ number_format($totalQuantity ?? 0) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Total available provincial inventory
                </p>
            </article>

            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-slate-400"
                >
                    PPE Item Types
                </p>

                <p
                    class="mt-3 text-3xl font-bold text-[#0284C7]"
                >
                    {{ number_format($availableItemTypes ?? 0) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    PPE types with available stock
                </p>
            </article>

            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-slate-400"
                >
                    Call-Off Records
                </p>

                <p
                    class="mt-3 text-3xl font-bold text-[#0EA5E9]"
                >
                    {{ number_format(
                        $callOffAllocations->total()
                    ) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Allocations assigned to this province
                </p>
            </article>

            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-slate-400"
                >
                    Recent Deliveries
                </p>

                <p
                    class="mt-3 text-3xl font-bold text-[#38BDF8]"
                >
                    {{ number_format(
                        $recentReceipts->count()
                    ) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Latest Delivery Receipts shown below
                </p>
            </article>
        </section>

        {{-- =========================================================
            FIXED PPE SUMMARY
        ========================================================== --}}
        <section
            class="overflow-hidden rounded-3xl border
                   border-slate-200 bg-white shadow-sm"
        >
            <div
                class="border-b border-slate-200 px-6 py-5
                       sm:px-7"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.16em] text-[#0284C7]"
                >
                    Provincial stock summary
                </p>

                <h2
                    class="mt-1 text-lg font-bold text-slate-950"
                >
                    Current PPE Inventory Summary
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Current available quantities across the seven PPE
                    variants used by the system.
                </p>
            </div>

            <div
                class="grid grid-cols-1 gap-4 p-6
                       sm:grid-cols-2 lg:grid-cols-4
                       xl:grid-cols-7"
            >
                @foreach($ppeLabels as $key => $label)
                    <article
                        class="group rounded-2xl border
                               border-slate-200 bg-slate-50
                               p-4 transition
                               hover:-translate-y-0.5
                               hover:bg-white hover:shadow-md"
                    >
                        <div
                            class="mb-4 h-1 w-10 rounded-full
                                   bg-[#0284C7] transition-all
                                   group-hover:w-16"
                        ></div>

                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wide text-slate-500"
                        >
                            {{ $label }}
                        </p>

                        <p
                            class="mt-3 text-2xl font-bold
                                   text-slate-950"
                        >
                            {{ number_format(
                                $summary[$key] ?? 0
                            ) }}
                        </p>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- =========================================================
            CURRENT POOLED INVENTORY TABLE
        ========================================================== --}}
        <section
            class="overflow-hidden rounded-3xl border
                   border-slate-200 bg-white shadow-sm"
        >
            <div
                class="border-b border-slate-200 px-6 py-5
                       sm:px-7"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.16em] text-[#0284C7]"
                >
                    Available provincial stock
                </p>

                <h2
                    class="mt-1 text-lg font-bold text-slate-950"
                >
                    Current Inventory Records
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    This table remains the official pooled inventory used
                    for project PPE designation.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table
                    class="min-w-[850px] w-full
                           divide-y divide-slate-200"
                >
                    <thead class="bg-slate-100">
                        <tr
                            class="text-xs font-bold uppercase
                                   tracking-wide text-slate-600"
                        >
                            <th class="px-6 py-4 text-left">
                                No.
                            </th>

                            <th class="px-6 py-4 text-left">
                                PPE Item
                            </th>

                            <th class="px-6 py-4 text-left">
                                Size / Label
                            </th>

                            <th class="px-6 py-4 text-left">
                                Unit
                            </th>

                            <th class="px-6 py-4 text-center">
                                Current Quantity
                            </th>

                            <th class="px-6 py-4 text-center">
                                Availability
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($inventories as $inventory)
                            <tr class="transition hover:bg-slate-50">
                                <td
                                    class="px-6 py-4 text-sm
                                           text-slate-500"
                                >
                                    {{
                                        $inventories->firstItem()
                                        + $loop->index
                                    }}
                                </td>

                                <td
                                    class="px-6 py-4 font-semibold
                                           text-slate-900"
                                >
                                    {{ $inventory->item?->item_name ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $inventory->item?->label ?: '—' }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{
                                        $inventory
                                            ->item
                                            ?->unit_of_measurement
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="px-6 py-4 text-center
                                           text-lg font-bold
                                           text-[#075985]"
                                >
                                    {{ number_format(
                                        $inventory->quantity
                                    ) }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if($inventory->quantity > 0)
                                        <span
                                            class="inline-flex rounded-full
                                                   bg-green-100 px-3 py-1
                                                   text-xs font-bold
                                                   text-green-800
                                                   ring-1 ring-green-200"
                                        >
                                            Available
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full
                                                   bg-red-100 px-3 py-1
                                                   text-xs font-bold
                                                   text-red-800
                                                   ring-1 ring-red-200"
                                        >
                                            Out of Stock
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center
                                           text-sm text-slate-500"
                                >
                                    No current inventory records were found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($inventories->hasPages())
                <div
                    class="border-t border-slate-200
                           px-6 py-4"
                >
                    {{ $inventories->links() }}
                </div>
            @endif
        </section>

        {{-- =========================================================
            CALL-OFF ALLOCATION VS ACTUAL
        ========================================================== --}}
        <section
            class="overflow-hidden rounded-3xl border
                   border-slate-200 bg-white shadow-sm"
        >
            <div
                class="border-b border-slate-200 px-6 py-5
                       sm:px-7"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.16em] text-[#0284C7]"
                >
                    Call-Off receiving summary
                </p>

                <h2
                    class="mt-1 text-lg font-bold text-slate-950"
                >
                    Allocation versus Actual Received
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Multiple Delivery Receipts under the same Call-Off are
                    added together in the Actual Received columns.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table
                    class="min-w-[2300px] w-full
                           border-separate border-spacing-0"
                >
                    <thead class="sticky top-0 z-20">
                        <tr
                            class="text-xs font-bold uppercase
                                   tracking-wide"
                        >
                            <th
                                rowspan="3"
                                class="sticky left-0 z-30 min-w-16
                                       border-b border-r
                                       border-slate-300 bg-slate-900
                                       px-4 py-4 text-center text-white"
                            >
                                No.
                            </th>

                            <th
                                rowspan="3"
                                class="sticky left-16 z-30 min-w-40
                                       border-b border-r
                                       border-slate-300 bg-slate-900
                                       px-4 py-4 text-left text-white"
                            >
                                Call-Off Number
                            </th>

                            <th
                                rowspan="3"
                                class="min-w-36 border-b border-r
                                       border-slate-300 bg-slate-900
                                       px-4 py-4 text-left text-white"
                            >
                                Purchase Order
                            </th>

                            <th
                                rowspan="3"
                                class="min-w-52 border-b border-r
                                       border-slate-300 bg-slate-900
                                       px-4 py-4 text-left text-white"
                            >
                                Supplier
                            </th>

                            <th
                                rowspan="3"
                                class="min-w-24 border-b border-r
                                       border-slate-300 bg-slate-900
                                       px-4 py-4 text-center text-white"
                            >
                                DR Count
                            </th>

                            <th
                                rowspan="3"
                                class="min-w-32 border-b border-r
                                       border-slate-300 bg-slate-900
                                       px-4 py-4 text-center text-white"
                            >
                                Status
                            </th>

                            <th
                                colspan="7"
                                class="border-b border-r
                                       border-slate-300
                                       bg-[#7DD3FC] px-4 py-4
                                       text-center text-[#075985]"
                            >
                                Allocation
                            </th>

                            <th
                                colspan="7"
                                class="border-b border-r
                                       border-slate-300
                                       bg-[#38BDF8] px-4 py-4
                                       text-center text-white"
                            >
                                Actual Received
                            </th>

                            <th
                                colspan="7"
                                class="border-b border-slate-300
                                       bg-[#075985] px-4 py-4
                                       text-center text-white"
                            >
                                Remaining
                            </th>
                        </tr>

                        <tr
                            class="text-[11px] font-bold uppercase
                                   tracking-wide"
                        >
                            @foreach([
                                ['Long Sleeve', 2],
                                ['Bucket Hat', 1],
                                ['Rubber Boots', 2],
                                ['Gloves', 1],
                                ['Mask', 1],
                            ] as [$group, $span])
                                <th
                                    colspan="{{ $span }}"
                                    class="border-b border-r
                                           border-slate-300
                                           bg-[#7DD3FC]/60
                                           px-3 py-3 text-center
                                           text-[#075985]"
                                >
                                    {{ $group }}
                                </th>
                            @endforeach

                            @foreach([
                                ['Long Sleeve', 2],
                                ['Bucket Hat', 1],
                                ['Rubber Boots', 2],
                                ['Gloves', 1],
                                ['Mask', 1],
                            ] as [$group, $span])
                                <th
                                    colspan="{{ $span }}"
                                    class="border-b border-r
                                           border-slate-300
                                           bg-[#0EA5E9]
                                           px-3 py-3 text-center
                                           text-white"
                                >
                                    {{ $group }}
                                </th>
                            @endforeach

                            @foreach([
                                ['Long Sleeve', 2],
                                ['Bucket Hat', 1],
                                ['Rubber Boots', 2],
                                ['Gloves', 1],
                                ['Mask', 1],
                            ] as [$group, $span])
                                <th
                                    colspan="{{ $span }}"
                                    class="border-b border-r
                                           border-slate-300
                                           bg-[#0284C7]
                                           px-3 py-3 text-center
                                           text-white"
                                >
                                    {{ $group }}
                                </th>
                            @endforeach
                        </tr>

                        <tr
                            class="text-[10px] font-bold uppercase
                                   tracking-wide"
                        >
                            @foreach([
                                'Medium',
                                'Large',
                                '—',
                                'US9',
                                'US10',
                                '—',
                                '—',
                            ] as $subLabel)
                                <th
                                    class="min-w-20 border-b border-r
                                           border-slate-300
                                           bg-[#7DD3FC]/40
                                           px-3 py-3 text-center
                                           text-[#075985]"
                                >
                                    {{ $subLabel }}
                                </th>
                            @endforeach

                            @foreach([
                                'Medium',
                                'Large',
                                '—',
                                'US9',
                                'US10',
                                '—',
                                '—',
                            ] as $subLabel)
                                <th
                                    class="min-w-20 border-b border-r
                                           border-slate-300
                                           bg-[#38BDF8]
                                           px-3 py-3 text-center
                                           text-white"
                                >
                                    {{ $subLabel }}
                                </th>
                            @endforeach

                            @foreach([
                                'Medium',
                                'Large',
                                '—',
                                'US9',
                                'US10',
                                '—',
                                '—',
                            ] as $subLabel)
                                <th
                                    class="min-w-20 border-b border-r
                                           border-slate-300
                                           bg-[#075985]
                                           px-3 py-3 text-center
                                           text-white"
                                >
                                    {{ $subLabel }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($callOffAllocations as $allocation)
                            @php
                                $batch =
                                    $allocation
                                        ->distributionBatch;

                                $callOff =
                                    $batch?->callOff;

                                $purchaseOrder =
                                    $batch?->purchaseOrder;

                                $supplier =
                                    $purchaseOrder?->supplier;

                                $allocationData =
                                    $allocation
                                        ->allocation_breakdown
                                    ?? [];

                                $actualData =
                                    $allocation
                                        ->actual_breakdown
                                    ?? [];

                                $remainingData =
                                    $allocation
                                        ->remaining_breakdown
                                    ?? [];

                                $statusClass = match(
                                    $allocation->status
                                ) {
                                    'Received' =>
                                        'bg-green-100 text-green-800',

                                    'Partially Received' =>
                                        'bg-amber-100 text-amber-800',

                                    'Approved' =>
                                        'bg-blue-100 text-blue-800',

                                    'For Delivery' =>
                                        'bg-indigo-100 text-indigo-800',

                                    default =>
                                        'bg-slate-100 text-slate-700',
                                };
                            @endphp

                            <tr
                                class="group transition
                                       hover:bg-[#7DD3FC]/10"
                            >
                                <td
                                    class="sticky left-0 z-10
                                           border-b border-r
                                           border-slate-200 bg-white
                                           px-4 py-4 text-center
                                           text-sm text-slate-500
                                           group-hover:bg-[#F0F9FF]"
                                >
                                    {{
                                        $callOffAllocations->firstItem()
                                        + $loop->index
                                    }}
                                </td>

                                <td
                                    class="sticky left-16 z-10
                                           border-b border-r
                                           border-slate-200 bg-white
                                           px-4 py-4 font-semibold
                                           text-[#075985]
                                           group-hover:bg-[#F0F9FF]"
                                >
                                    <a
                                        href="{{ route(
                                            'provincial.receiving.show',
                                            $allocation
                                        ) }}"
                                        class="hover:underline"
                                    >
                                        {{
                                            $callOff
                                                ?->call_off_number
                                            ?? '—'
                                        }}
                                    </a>
                                </td>

                                <td
                                    class="border-b border-r
                                           border-slate-200 px-4 py-4
                                           text-sm font-semibold
                                           text-slate-800"
                                >
                                    {{
                                        $purchaseOrder?->po_number
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="border-b border-r
                                           border-slate-200 px-4 py-4
                                           text-sm text-slate-600"
                                >
                                    {{
                                        $supplier?->supplier_name
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="border-b border-r
                                           border-slate-200 px-4 py-4
                                           text-center font-bold
                                           text-slate-700"
                                >
                                    {{
                                        number_format(
                                            $allocation
                                                ->deliveryReceipts
                                                ->count()
                                        )
                                    }}
                                </td>

                                <td
                                    class="border-b border-r
                                           border-slate-200 px-4 py-4
                                           text-center"
                                >
                                    <span
                                        class="inline-flex rounded-full
                                               px-3 py-1 text-xs
                                               font-bold
                                               {{ $statusClass }}"
                                    >
                                        {{ $allocation->status }}
                                    </span>
                                </td>

                                @foreach(array_keys($ppeLabels) as $key)
                                    <td
                                        class="border-b border-r
                                               border-slate-200
                                               bg-[#7DD3FC]/5
                                               px-3 py-4 text-center
                                               font-semibold
                                               text-slate-800"
                                    >
                                        {{ number_format(
                                            $allocationData[$key] ?? 0
                                        ) }}
                                    </td>
                                @endforeach

                                @foreach(array_keys($ppeLabels) as $key)
                                    <td
                                        class="border-b border-r
                                               border-slate-200
                                               bg-[#38BDF8]/5
                                               px-3 py-4 text-center
                                               font-bold
                                               text-[#0284C7]"
                                    >
                                        {{ number_format(
                                            $actualData[$key] ?? 0
                                        ) }}
                                    </td>
                                @endforeach

                                @foreach(array_keys($ppeLabels) as $key)
                                    @php
                                        $remainingValue =
                                            (int) (
                                                $remainingData[$key]
                                                ?? 0
                                            );
                                    @endphp

                                    <td
                                        class="border-b border-r
                                               border-slate-200
                                               px-3 py-4 text-center
                                               font-bold
                                               {{
                                                   $remainingValue > 0
                                                       ? 'bg-amber-50 text-amber-700'
                                                       : 'bg-green-50 text-green-700'
                                               }}"
                                    >
                                        {{ number_format(
                                            $remainingValue
                                        ) }}
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Individual DR audit rows --}}
                            @if($allocation->deliveryReceipts->isNotEmpty())
                                <tr>
                                    <td
                                        colspan="27"
                                        class="border-b border-slate-200
                                               bg-slate-50 px-5 py-4"
                                    >
                                        <details class="group/details">
                                            <summary
                                                class="cursor-pointer
                                                       list-none font-bold
                                                       text-[#0284C7]"
                                            >
                                                View
                                                {{
                                                    number_format(
                                                        $allocation
                                                            ->deliveryReceipts
                                                            ->count()
                                                    )
                                                }}
                                                Delivery Receipt detail(s)
                                            </summary>

                                            <div
                                                class="mt-4 overflow-x-auto
                                                       rounded-xl border
                                                       border-slate-200
                                                       bg-white"
                                            >
                                                <table
                                                    class="min-w-[1050px]
                                                           w-full
                                                           divide-y
                                                           divide-slate-200"
                                                >
                                                    <thead class="bg-slate-100">
                                                        <tr
                                                            class="text-xs
                                                                   font-bold
                                                                   uppercase
                                                                   tracking-wide
                                                                   text-slate-600"
                                                        >
                                                            <th
                                                                class="px-5 py-3
                                                                       text-left"
                                                            >
                                                                Call-Off
                                                            </th>

                                                            <th
                                                                class="px-5 py-3
                                                                       text-left"
                                                            >
                                                                Delivery Receipt
                                                            </th>

                                                            <th
                                                                class="px-5 py-3
                                                                       text-left"
                                                            >
                                                                Delivery Date
                                                            </th>

                                                            <th
                                                                class="px-5 py-3
                                                                       text-left"
                                                            >
                                                                Receiver
                                                            </th>

                                                            <th
                                                                class="px-5 py-3
                                                                       text-center"
                                                            >
                                                                Allocation Reference
                                                            </th>

                                                            <th
                                                                class="px-5 py-3
                                                                       text-center"
                                                            >
                                                                Actual This Delivery
                                                            </th>

                                                            <th
                                                                class="px-5 py-3
                                                                       text-center"
                                                            >
                                                                PDF
                                                            </th>
                                                        </tr>
                                                    </thead>

                                                    <tbody
                                                        class="divide-y
                                                               divide-slate-100"
                                                    >
                                                        @foreach(
                                                            $allocation
                                                                ->deliveryReceipts
                                                            as $receipt
                                                        )
                                                            <tr
                                                                class="hover:bg-slate-50"
                                                            >
                                                                <td
                                                                    class="px-5 py-4
                                                                           font-semibold
                                                                           text-[#075985]"
                                                                >
                                                                    {{
                                                                        $callOff
                                                                            ?->call_off_number
                                                                        ?? '—'
                                                                    }}
                                                                </td>

                                                                <td
                                                                    class="px-5 py-4
                                                                           font-semibold
                                                                           text-slate-900"
                                                                >
                                                                    {{
                                                                        $receipt
                                                                            ->dr_number
                                                                    }}
                                                                </td>

                                                                <td
                                                                    class="px-5 py-4
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
                                                                </td>

                                                                <td
                                                                    class="px-5 py-4
                                                                           text-slate-600"
                                                                >
                                                                    {{
                                                                        $receipt
                                                                            ->physical_receiver_name
                                                                        ?? $receipt
                                                                            ->receivedByUser
                                                                            ?->name
                                                                        ?? '—'
                                                                    }}
                                                                </td>

                                                                <td
                                                                    class="px-5 py-4
                                                                           text-center
                                                                           font-semibold
                                                                           text-slate-700"
                                                                >
                                                                    {{
                                                                        number_format(
                                                                            $allocation
                                                                                ->allocation_total
                                                                            ?? 0
                                                                        )
                                                                    }}
                                                                </td>

                                                                <td
                                                                    class="px-5 py-4
                                                                           text-center
                                                                           font-bold
                                                                           text-blue-700"
                                                                >
                                                                    {{
                                                                        number_format(
                                                                            $receipt
                                                                                ->items
                                                                                ->sum(
                                                                                    'received_quantity'
                                                                                )
                                                                        )
                                                                    }}
                                                                </td>

                                                                <td
                                                                    class="px-5 py-4
                                                                           text-center"
                                                                >
                                                                    @if(
                                                                        $receipt
                                                                            ->document
                                                                    )
                                                                        <a
                                                                            href="{{ route('documents.receipt-legacy', $receipt) }}"
                                                                            target="_blank"
                                                                            rel="noopener"
                                                                            class="inline-flex
                                                                                   rounded-lg
                                                                                   border
                                                                                   border-slate-300
                                                                                   bg-white
                                                                                   px-4 py-2
                                                                                   text-xs
                                                                                   font-bold
                                                                                   text-slate-700
                                                                                   hover:bg-slate-50"
                                                                        >
                                                                            View
                                                                        </a>
                                                                    @else
                                                                        —
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @endif

                        @empty
                            <tr>
                                <td
                                    colspan="27"
                                    class="px-6 py-12 text-center
                                           text-sm text-slate-500"
                                >
                                    No Call-Off inventory records were found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($callOffAllocations->hasPages())
                <div
                    class="border-t border-slate-200
                           px-6 py-4"
                >
                    {{ $callOffAllocations->links() }}
                </div>
            @endif
        </section>

        {{-- =========================================================
            RECENT INVENTORY ADDITIONS
        ========================================================== --}}
        <section
            class="overflow-hidden rounded-3xl border
                   border-slate-200 bg-white shadow-sm"
        >
            <div
                class="border-b border-slate-200 px-6 py-5
                       sm:px-7"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.16em] text-[#0284C7]"
                >
                    Latest stock-in transactions
                </p>

                <h2
                    class="mt-1 text-lg font-bold text-slate-950"
                >
                    Recent Inventory Additions
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Recent Delivery Receipts that increased provincial
                    inventory.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table
                    class="min-w-[1050px] w-full
                           divide-y divide-slate-200"
                >
                    <thead class="bg-slate-100">
                        <tr
                            class="text-xs font-bold uppercase
                                   tracking-wide text-slate-600"
                        >
                            <th class="px-6 py-4 text-left">
                                No.
                            </th>

                            <th class="px-6 py-4 text-left">
                                Call-Off Number
                            </th>

                            <th class="px-6 py-4 text-left">
                                Delivery Receipt
                            </th>

                            <th class="px-6 py-4 text-left">
                                Supplier
                            </th>

                            <th class="px-6 py-4 text-left">
                                Delivery Date
                            </th>

                            <th class="px-6 py-4 text-center">
                                Total PPE Added
                            </th>

                            <th class="px-6 py-4 text-center">
                                Document
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentReceipts as $receipt)
                            @php
                                $recentAllocation =
                                    $receipt
                                        ->provinceDistribution;

                                $recentBatch =
                                    $recentAllocation
                                        ?->distributionBatch;

                                $recentCallOff =
                                    $recentBatch?->callOff;

                                $recentPurchaseOrder =
                                    $recentBatch
                                        ?->purchaseOrder;

                                $recentSupplier =
                                    $recentPurchaseOrder
                                        ?->supplier;

                                $totalReceived =
                                    (int) $receipt
                                        ->items
                                        ->sum(
                                            'received_quantity'
                                        );
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td
                                    class="px-6 py-4 text-sm
                                           text-slate-500"
                                >
                                    {{ $loop->iteration }}
                                </td>

                                <td
                                    class="px-6 py-4 font-semibold
                                           text-[#075985]"
                                >
                                    {{
                                        $recentCallOff
                                            ?->call_off_number
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="px-6 py-4 font-semibold
                                           text-slate-900"
                                >
                                    {{ $receipt->dr_number }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{
                                        $recentSupplier
                                            ?->supplier_name
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{
                                        $receipt
                                            ->delivery_date
                                            ?->format('M d, Y')
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="px-6 py-4 text-center
                                           text-lg font-bold
                                           text-blue-700"
                                >
                                    {{ number_format($totalReceived) }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if($receipt->document)
                                        <a
                                            href="{{ route('documents.receipt-legacy', $receipt) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="inline-flex rounded-lg
                                                   border border-slate-300
                                                   bg-white px-4 py-2
                                                   text-xs font-bold
                                                   text-slate-700
                                                   transition
                                                   hover:bg-slate-50"
                                        >
                                            View PDF
                                        </a>
                                    @else
                                        <span class="text-slate-400">
                                            —
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center
                                           text-sm text-slate-500"
                                >
                                    No recent Delivery Receipts were found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>

</x-po_dashboard_layout>