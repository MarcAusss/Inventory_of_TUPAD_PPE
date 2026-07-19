<x-po_dashboard_layout title="Per Call-Off Inventory">

    @php
        $ppeColumns = [
            1 => [
                'name' => 'Long Sleeve',
                'label' => 'Medium',
            ],

            2 => [
                'name' => 'Long Sleeve',
                'label' => 'Large',
            ],

            3 => [
                'name' => 'Bucket Hat',
                'label' => null,
            ],

            4 => [
                'name' => 'Rubber Boots',
                'label' => 'US9',
            ],

            5 => [
                'name' => 'Rubber Boots',
                'label' => 'US10',
            ],

            6 => [
                'name' => 'Hand Gloves',
                'label' => null,
            ],

            7 => [
                'name' => 'Mask',
                'label' => null,
            ],
        ];

        $statusOptions = [
            'Pending',
            'Approved',
            'For Delivery',
            'Partially Received',
            'Received',
            'Cancelled',
        ];
    @endphp

    <div class="mx-auto max-w-[1900px] space-y-6">

        <section
            class="relative overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b from-[#075985]
                       via-[#0284C7] to-[#38BDF8]"
            ></div>

            <div
                class="flex flex-col gap-6 px-6 py-7
                       sm:px-8 lg:flex-row
                       lg:items-center lg:justify-between"
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
                            Call-Off Inventory
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold
                               tracking-tight text-slate-950
                               sm:text-3xl"
                    >
                        Remaining PPE Stock per Call-Off
                    </h1>

                    <p
                        class="mt-2 max-w-4xl text-sm
                               leading-6 text-slate-600"
                    >
                        View the remaining PPE inventory belonging to
                        each individual Call-Off after completed
                        project distributions have been deducted.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route(
                            'provincial.current-inventory.index'
                        ) }}"
                        class="inline-flex items-center justify-center
                               rounded-xl border border-slate-300
                               bg-white px-5 py-3 text-sm font-bold
                               text-slate-700 transition
                               hover:bg-slate-50"
                    >
                        Provincial Inventory
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
                        Movement History
                    </a>
                </div>
            </div>
        </section>

        <section
            class="grid grid-cols-1 gap-4
                   sm:grid-cols-2 xl:grid-cols-5"
        >
            @php
                $cards = [
                    [
                        'label' => 'Call-Offs',
                        'value' => $summary['call_off_count'],
                        'class' => 'text-[#075985]',
                    ],

                    [
                        'label' => 'Actual Received',
                        'value' => $summary['received_total'],
                        'class' => 'text-blue-700',
                    ],

                    [
                        'label' => 'Project Distributed',
                        'value' => $summary['distributed_total'],
                        'class' => 'text-[#0EA5E9]',
                    ],

                    [
                        'label' => 'Call-Off Remaining',
                        'value' => $summary['remaining_total'],
                        'class' => 'text-green-700',
                    ],

                    [
                        'label' => 'Safe Available Now',
                        'value' => $summary['safe_available_total'],
                        'class' => 'text-amber-700',
                    ],
                ];
            @endphp

            @foreach($cards as $card)
                <article
                    class="group rounded-2xl border
                           border-slate-200 bg-white p-5
                           shadow-sm transition
                           hover:-translate-y-1 hover:shadow-md"
                >
                    <div
                        class="mb-4 h-1 w-10 rounded-full
                               bg-[#0284C7] transition-all
                               group-hover:w-16"
                    ></div>

                    <p
                        class="text-xs font-bold uppercase
                               tracking-wider text-slate-400"
                    >
                        {{ $card['label'] }}
                    </p>

                    <p
                        class="mt-3 text-3xl font-bold
                               {{ $card['class'] }}"
                    >
                        {{ number_format($card['value']) }}
                    </p>
                </article>
            @endforeach
        </section>

        <section
            class="rounded-3xl border border-slate-200
                   bg-white p-5 shadow-sm sm:p-6"
        >
            <form
                method="GET"
                action="{{ route(
                    'provincial.call-off-inventory.index'
                ) }}"
                class="grid grid-cols-1 gap-4
                       md:grid-cols-2 xl:grid-cols-12"
            >
                <div class="xl:col-span-7">
                    <label
                        for="search"
                        class="mb-2 block text-xs font-bold
                               uppercase tracking-wider
                               text-slate-500"
                    >
                        Search
                    </label>

                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search Call-Off, Purchase Order, or supplier..."
                        class="w-full rounded-xl border-slate-300
                               focus:border-[#0284C7]
                               focus:ring-[#0284C7]"
                    >
                </div>

                <div class="xl:col-span-3">
                    <label
                        for="status"
                        class="mb-2 block text-xs font-bold
                               uppercase tracking-wider
                               text-slate-500"
                    >
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-xl border-slate-300
                               focus:border-[#0284C7]
                               focus:ring-[#0284C7]"
                    >
                        <option value="">
                            All Statuses
                        </option>

                        @foreach($statusOptions as $option)
                            <option
                                value="{{ $option }}"
                                @selected($status === $option)
                            >
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div
                    class="flex items-end gap-2
                           xl:col-span-2"
                >
                    <button
                        type="submit"
                        class="flex-1 rounded-xl
                               bg-[#0284C7] px-5 py-2.5
                               text-sm font-bold text-white
                               transition hover:bg-[#075985]"
                    >
                        Apply
                    </button>

                    <a
                        href="{{ route(
                            'provincial.call-off-inventory.index'
                        ) }}"
                        class="rounded-xl border border-slate-300
                               bg-white px-5 py-2.5 text-sm
                               font-bold text-slate-700
                               transition hover:bg-slate-50"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section
            class="overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="border-b border-slate-200
                       px-6 py-5 sm:px-7"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.16em] text-[#0284C7]"
                >
                    Remaining stock by source
                </p>

                <h2
                    class="mt-1 text-xl font-bold
                           text-slate-950"
                >
                    Per Call-Off PPE Inventory
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Remaining equals Actual Received minus completed
                    project distributions linked to the same Call-Off.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table
                    class="min-w-[2200px] w-full
                           border-separate border-spacing-0"
                >
                    <thead class="bg-slate-900 text-white">
                        <tr
                            class="text-xs font-bold uppercase
                                   tracking-wide"
                        >
                            <th
                                rowspan="3"
                                class="sticky left-0 z-30 min-w-16
                                       border-b border-r
                                       border-slate-700 bg-slate-900
                                       px-4 py-4 text-center"
                            >
                                No.
                            </th>

                            <th
                                rowspan="3"
                                class="sticky left-16 z-30 min-w-44
                                       border-b border-r
                                       border-slate-700 bg-slate-900
                                       px-4 py-4 text-left"
                            >
                                Call-Off Number
                            </th>

                            <th
                                rowspan="3"
                                class="min-w-40 border-b border-r
                                       border-slate-700
                                       px-4 py-4 text-left"
                            >
                                Purchase Order
                            </th>

                            <th
                                rowspan="3"
                                class="min-w-56 border-b border-r
                                       border-slate-700
                                       px-4 py-4 text-left"
                            >
                                Supplier
                            </th>

                            <th
                                rowspan="3"
                                class="min-w-32 border-b border-r
                                       border-slate-700
                                       px-4 py-4 text-center"
                            >
                                Status
                            </th>

                            <th
                                colspan="7"
                                class="border-b border-r
                                       border-slate-700
                                       bg-[#0284C7]
                                       px-4 py-4 text-center"
                            >
                                Remaining PPE per Call-Off
                            </th>

                            <th
                                colspan="7"
                                class="border-b border-slate-700
                                       bg-[#075985]
                                       px-4 py-4 text-center"
                            >
                                Safe Available Now
                            </th>

                            <th
                                rowspan="3"
                                class="min-w-32 border-b
                                       border-slate-700
                                       bg-slate-900
                                       px-4 py-4 text-center"
                            >
                                Remaining Total
                            </th>
                        </tr>

                        <tr
                            class="text-[11px] font-bold uppercase"
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
                                           border-[#7DD3FC]/40
                                           bg-[#0EA5E9]
                                           px-3 py-3 text-center"
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
                                           border-[#7DD3FC]/30
                                           bg-[#075985]
                                           px-3 py-3 text-center"
                                >
                                    {{ $group }}
                                </th>
                            @endforeach
                        </tr>

                        <tr
                            class="text-[10px] font-bold uppercase"
                        >
                            @foreach([
                                'Medium',
                                'Large',
                                '—',
                                'US9',
                                'US10',
                                '—',
                                '—',
                            ] as $label)
                                <th
                                    class="min-w-20 border-b border-r
                                           border-[#7DD3FC]/40
                                           bg-[#38BDF8]
                                           px-3 py-3 text-center"
                                >
                                    {{ $label }}
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
                            ] as $label)
                                <th
                                    class="min-w-20 border-b border-r
                                           border-[#7DD3FC]/30
                                           bg-[#0284C7]
                                           px-3 py-3 text-center"
                                >
                                    {{ $label }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($allocations as $allocation)
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

                                $balances =
                                    $allocation
                                        ->call_off_balances
                                    ?? [];

                                $statusClass = match(
                                    $allocation->status
                                ) {
                                    'Received' =>
                                        'bg-green-100 text-green-800',

                                    'Partially Received' =>
                                        'bg-amber-100 text-amber-800',

                                    'For Delivery' =>
                                        'bg-blue-100 text-blue-800',

                                    'Approved' =>
                                        'bg-indigo-100 text-indigo-800',

                                    'Cancelled' =>
                                        'bg-slate-200 text-slate-700',

                                    default =>
                                        'bg-slate-100 text-slate-700',
                                };
                            @endphp

                            <tr class="group hover:bg-slate-50">
                                <td
                                    class="sticky left-0 z-10
                                           border-b border-r
                                           border-slate-200 bg-white
                                           px-4 py-4 text-center
                                           text-slate-500
                                           group-hover:bg-slate-50"
                                >
                                    {{
                                        $allocations->firstItem()
                                        + $loop->index
                                    }}
                                </td>

                                <td
                                    class="sticky left-16 z-10
                                           border-b border-r
                                           border-slate-200 bg-white
                                           px-4 py-4 font-bold
                                           text-[#075985]
                                           group-hover:bg-slate-50"
                                >
                                    {{
                                        $callOff?->call_off_number
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="border-b border-r
                                           border-slate-200
                                           px-4 py-4 font-semibold
                                           text-slate-800"
                                >
                                    {{
                                        $purchaseOrder?->po_number
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="border-b border-r
                                           border-slate-200
                                           px-4 py-4 text-slate-600"
                                >
                                    {{
                                        $supplier?->supplier_name
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="border-b border-r
                                           border-slate-200
                                           px-4 py-4 text-center"
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

                                @foreach(
                                    array_keys($ppeColumns)
                                    as $itemId
                                )
                                    <td
                                        class="border-b border-r
                                               border-slate-200
                                               bg-[#7DD3FC]/10
                                               px-3 py-4 text-center
                                               text-lg font-bold
                                               text-[#0284C7]"
                                    >
                                        {{
                                            number_format(
                                                $balances[
                                                    $itemId
                                                ][
                                                    'call_off_available'
                                                ] ?? 0
                                            )
                                        }}
                                    </td>
                                @endforeach

                                @foreach(
                                    array_keys($ppeColumns)
                                    as $itemId
                                )
                                    <td
                                        class="border-b border-r
                                               border-slate-200
                                               px-3 py-4 text-center
                                               font-bold
                                               text-green-700"
                                    >
                                        {{
                                            number_format(
                                                $balances[
                                                    $itemId
                                                ][
                                                    'available_for_projects'
                                                ] ?? 0
                                            )
                                        }}
                                    </td>
                                @endforeach

                                <td
                                    class="border-b border-slate-200
                                           px-4 py-4 text-center
                                           text-lg font-bold
                                           text-[#075985]"
                                >
                                    {{
                                        number_format(
                                            $allocation
                                                ->remaining_total
                                        )
                                    }}
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td
                                    colspan="20"
                                    class="px-6 py-16 text-center
                                           text-slate-500"
                                >
                                    No Call-Off inventory records
                                    match the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($allocations->hasPages())
                <div
                    class="border-t border-slate-200
                           px-6 py-4"
                >
                    {{ $allocations->links() }}
                </div>
            @endif
        </section>

        <section
            class="grid grid-cols-1 gap-4 lg:grid-cols-2"
        >
            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-[#0284C7]"
                >
                    Remaining PPE per Call-Off
                </p>

                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Actual PPE received under the Call-Off minus
                    completed project distributions linked to that
                    same Call-Off.
                </p>
            </article>

            <article
                class="rounded-2xl border border-amber-200
                       bg-amber-50 p-5"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-amber-800"
                >
                    Safe Available Now
                </p>

                <p class="mt-2 text-sm leading-6 text-amber-800">
                    The quantity currently allowed for another project.
                    This may be lower than the Call-Off balance while
                    legacy or unassigned inventory deductions remain.
                </p>
            </article>
        </section>
    </div>

</x-po_dashboard_layout>