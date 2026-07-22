<x-po_dashboard_layout title="Call-Off Receiving Details">

    @php
        $batch =
            $provinceDistribution
                ->distributionBatch;

        $callOff =
            $batch?->callOff;

        $purchaseOrder =
            $batch?->purchaseOrder;

        $supplier =
            $purchaseOrder?->supplier;

        $receipts =
            $provinceDistribution
                ->deliveryReceipts;

        $allocatedTotal =
            (int) $provinceDistribution
                ->items
                ->sum('quantity');

        $receivedTotal =
            collect($previouslyReceivedByItem)
                ->sum();

        $remainingTotal =
            collect($remainingByItem)
                ->sum();

        $receiptCount =
            $receipts->count();

        $receivingPercentage =
            $allocatedTotal > 0
                ? min(
                    100,
                    round(
                        (
                            $receivedTotal
                            / $allocatedTotal
                        ) * 100
                    )
                )
                : 0;

        $canReceiveAnotherDelivery =
            $remainingTotal > 0
            && in_array(
                $provinceDistribution->status,
                [
                    'Approved',
                    'For Delivery',
                    'Partially Received',
                ],
                true
            )
            && $callOff?->status === 'Approved';

        $statusClass = match(
            $provinceDistribution->status
        ) {
            'Received' =>
                'bg-green-100 text-green-800 ring-green-200',

            'Partially Received' =>
                'bg-amber-100 text-amber-800 ring-amber-200',

            'For Delivery' =>
                'bg-blue-100 text-blue-800 ring-blue-200',

            'Approved' =>
                'bg-indigo-100 text-indigo-800 ring-indigo-200',

            'Cancelled' =>
                'bg-slate-200 text-slate-700 ring-slate-300',

            default =>
                'bg-slate-100 text-slate-700 ring-slate-200',
        };
    @endphp

    <div class="mx-auto max-w-[1700px] space-y-6">

        {{-- =========================================================
            PAGE HEADER
        ========================================================== --}}
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
                class="flex flex-col gap-6 px-6 py-7 sm:px-8
                       lg:flex-row lg:items-center
                       lg:justify-between"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-3">

                        <span
                            class="rounded-full bg-[#7DD3FC]/20
                                   px-3 py-1 text-xs font-bold
                                   uppercase tracking-wider
                                   text-[#0284C7]
                                   ring-1 ring-[#7DD3FC]"
                        >
                            Provincial Receiving
                        </span>

                        <span
                            class="inline-flex rounded-full px-3 py-1
                                   text-xs font-bold ring-1
                                   {{ $statusClass }}"
                        >
                            {{ $provinceDistribution->status }}
                        </span>

                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold tracking-tight
                               text-slate-950 sm:text-3xl"
                    >
                        Call-Off Receiving Details
                    </h1>

                    <p
                        class="mt-2 max-w-3xl text-sm leading-6
                               text-slate-600"
                    >
                        Review the allocation, cumulative quantities
                        received, remaining receivable PPE, and every
                        Delivery Receipt recorded for this Call-Off.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">

                    <a
                        href="{{ route(
                            'provincial.receiving.index'
                        ) }}"
                        class="inline-flex items-center justify-center
                               rounded-xl border border-slate-300
                               bg-white px-5 py-3 text-sm font-bold
                               text-slate-700 transition
                               hover:bg-slate-50"
                    >
                        Back to Allocations
                    </a>

                    @if($canReceiveAnotherDelivery)
                        <a
                            href="{{ route(
                                'provincial.receiving.create',
                                $provinceDistribution
                            ) }}"
                            class="inline-flex items-center justify-center
                                   rounded-xl bg-[#0284C7] px-5 py-3
                                   text-sm font-bold text-white
                                   transition hover:bg-[#075985]"
                        >
                            Receive Another Delivery
                        </a>
                    @endif

                </div>
            </div>
        </section>

        {{-- =========================================================
            FLASH MESSAGES
        ========================================================== --}}
        @if(session('success'))
            <section
                class="rounded-2xl border border-green-200
                       bg-green-50 px-6 py-4 text-green-800"
            >
                {{ session('success') }}
            </section>
        @endif

        @if(session('error'))
            <section
                class="rounded-2xl border border-red-200
                       bg-red-50 px-6 py-4 text-red-800"
            >
                {{ session('error') }}
            </section>
        @endif

        {{-- =========================================================
            CALL-OFF INFORMATION
        ========================================================== --}}
        <section
            class="grid grid-cols-1 gap-4 sm:grid-cols-2
                   xl:grid-cols-5"
        >
            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                           text-slate-400"
                >
                    Call-Off Number
                </p>

                <p
                    class="mt-3 text-xl font-bold text-[#075985]"
                >
                    {{ $callOff?->call_off_number ?? 'Not available' }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Batch #{{ $batch?->id ?? 'N/A' }}
                </p>
            </article>

            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                           text-slate-400"
                >
                    Purchase Order
                </p>

                <p
                    class="mt-3 text-xl font-bold text-slate-950"
                >
                    {{ $purchaseOrder?->po_number ?? 'Not available' }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    {{ $supplier?->supplier_name ?? 'Supplier unavailable' }}
                </p>
            </article>

            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                           text-slate-400"
                >
                    Allocated PPE
                </p>

                <p
                    class="mt-3 text-2xl font-bold text-slate-950"
                >
                    {{ number_format($allocatedTotal) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Original Call-Off allocation
                </p>
            </article>

            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                           text-slate-400"
                >
                    Actual Received
                </p>

                <p
                    class="mt-3 text-2xl font-bold text-blue-700"
                >
                    {{ number_format($receivedTotal) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Across {{ number_format($receiptCount) }}
                    Delivery Receipt(s)
                </p>
            </article>

            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                           text-slate-400"
                >
                    Remaining Receivable
                </p>

                <p
                    class="mt-3 text-2xl font-bold
                           {{
                               $remainingTotal > 0
                                   ? 'text-amber-700'
                                   : 'text-green-700'
                           }}"
                >
                    {{ number_format($remainingTotal) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Balance not yet physically received
                </p>
            </article>
        </section>

        {{-- =========================================================
            RECEIVING PROGRESS
        ========================================================== --}}
        <section
            class="rounded-3xl border border-slate-200
                   bg-white p-6 shadow-sm"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row
                       sm:items-center sm:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase
                               tracking-[0.16em] text-[#0284C7]"
                    >
                        Receiving progress
                    </p>

                    <h2
                        class="mt-1 text-lg font-bold text-slate-950"
                    >
                        Cumulative Call-Off Delivery
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ number_format($receivedTotal) }}
                        of
                        {{ number_format($allocatedTotal) }}
                        allocated PPE has been received.
                    </p>
                </div>

                <p
                    class="text-3xl font-bold text-[#0284C7]"
                >
                    {{ $receivingPercentage }}%
                </p>
            </div>

            <div
                class="mt-5 h-4 overflow-hidden rounded-full
                       bg-slate-200"
            >
                <div
                    class="h-full rounded-full bg-gradient-to-r
                           from-[#38BDF8] to-[#075985]
                           transition-all duration-500"
                    style="width: {{ $receivingPercentage }}%"
                ></div>
            </div>
        </section>

        {{-- =========================================================
            ALLOCATION VERSUS CUMULATIVE ACTUAL
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
                    Call-Off quantity summary
                </p>

                <h2
                    class="mt-1 text-lg font-bold text-slate-950"
                >
                    Allocation versus Cumulative Actual Received
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Actual quantities include every Delivery Receipt
                    recorded under this allocation.
                </p>
            </div>

            <div class="overflow-x-auto">

                <table
                    class="min-w-[950px] w-full
                           divide-y divide-slate-200"
                >
                    <thead class="bg-slate-100">
                        <tr
                            class="text-xs font-bold uppercase
                                   tracking-wide text-slate-600"
                        >
                            <th class="px-6 py-4 text-left">
                                PPE Item
                            </th>

                            <th class="px-6 py-4 text-left">
                                Size / Label
                            </th>

                            <th class="px-6 py-4 text-center">
                                Allocation
                            </th>

                            <th class="px-6 py-4 text-center">
                                Actual Received
                            </th>

                            <th class="px-6 py-4 text-center">
                                Shortage / Remaining
                            </th>

                            <th class="min-w-56 px-6 py-4 text-left">
                                Progress
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @foreach(
                            $provinceDistribution->items
                            as $allocationItem
                        )
                            @php
                                $allocated =
                                    (int) $allocationItem->quantity;

                                $received =
                                    (int) (
                                        $previouslyReceivedByItem[
                                            $allocationItem->id
                                        ] ?? 0
                                    );

                                $remaining =
                                    (int) (
                                        $remainingByItem[
                                            $allocationItem->id
                                        ] ?? 0
                                    );

                                $itemPercentage =
                                    $allocated > 0
                                        ? min(
                                            100,
                                            round(
                                                (
                                                    $received
                                                    / $allocated
                                                ) * 100
                                            )
                                        )
                                        : 0;
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td
                                    class="px-6 py-4 font-semibold
                                           text-slate-900"
                                >
                                    {{ $allocationItem->item->item_name }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $allocationItem->item->label ?: '—' }}
                                </td>

                                <td
                                    class="px-6 py-4 text-center
                                           font-semibold text-slate-900"
                                >
                                    {{ number_format($allocated) }}
                                </td>

                                <td
                                    class="px-6 py-4 text-center
                                           font-semibold text-blue-700"
                                >
                                    {{ number_format($received) }}
                                </td>

                                <td
                                    class="px-6 py-4 text-center font-bold
                                           {{
                                               $remaining > 0
                                                   ? 'text-amber-700'
                                                   : 'text-green-700'
                                           }}"
                                >
                                    {{ number_format($remaining) }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">

                                        <div
                                            class="h-2.5 flex-1
                                                   overflow-hidden
                                                   rounded-full
                                                   bg-slate-200"
                                        >
                                            <div
                                                class="h-full rounded-full
                                                       bg-[#0284C7]"
                                                style="width: {{ $itemPercentage }}%"
                                            ></div>
                                        </div>

                                        <span
                                            class="w-12 text-right
                                                   text-xs font-bold
                                                   text-slate-600"
                                        >
                                            {{ $itemPercentage }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </section>

        {{-- =========================================================
            ONE ROW PER DELIVERY RECEIPT
        ========================================================== --}}
        <section
            class="overflow-hidden rounded-3xl border
                   border-slate-200 bg-white shadow-sm"
        >
            <div
                class="flex flex-col gap-3 border-b
                       border-slate-200 px-6 py-5 sm:px-7
                       lg:flex-row lg:items-center
                       lg:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase
                               tracking-[0.16em] text-[#0284C7]"
                    >
                        Delivery audit
                    </p>

                    <h2
                        class="mt-1 text-lg font-bold text-slate-950"
                    >
                        Delivery Receipt History
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Each row represents one physical delivery.
                        Allocation values are reference values and must
                        not be totaled across DR rows.
                    </p>
                </div>

                @if($canReceiveAnotherDelivery)
                    <a
                        href="{{ route(
                            'provincial.receiving.create',
                            $provinceDistribution
                        ) }}"
                        class="inline-flex items-center justify-center
                               rounded-xl bg-[#0284C7] px-5 py-3
                               text-sm font-bold text-white
                               transition hover:bg-[#075985]"
                    >
                        Add Delivery Receipt
                    </a>
                @endif
            </div>

            <div class="overflow-x-auto">

                <table
                    class="min-w-[1000px] w-full
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
                                Delivery Date
                            </th>

                            <th class="px-6 py-4 text-left">
                                Receiver
                            </th>

                            <th class="px-6 py-4 text-center">
                                Allocation Reference
                            </th>

                            <th class="px-6 py-4 text-center">
                                Actual This Delivery
                            </th>

                            <th class="px-6 py-4 text-center">
                                Document
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($receipts as $receipt)

                            @php
                                $actualThisDelivery =
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
                                           text-slate-900"
                                >
                                    {{ $callOff?->call_off_number ?? '—' }}
                                </td>

                                <td class="px-6 py-4">
                                    <p
                                        class="font-semibold
                                               text-[#075985]"
                                    >
                                        {{ $receipt->dr_number }}
                                    </p>

                                    @if($receipt->remarks)
                                        <p
                                            class="mt-1 max-w-xs truncate
                                                   text-xs text-slate-500"
                                            title="{{ $receipt->remarks }}"
                                        >
                                            {{ $receipt->remarks }}
                                        </p>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $receipt->delivery_date?->format('M d, Y') ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{
                                        $receipt->physical_receiver_name
                                        ?? $receipt->receivedByUser?->name
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="px-6 py-4 text-center
                                           font-semibold text-slate-700"
                                >
                                    {{ number_format($allocatedTotal) }}
                                </td>

                                <td
                                    class="px-6 py-4 text-center
                                           font-bold text-blue-700"
                                >
                                    {{ number_format($actualThisDelivery) }}
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
                                        <span
                                            class="text-sm text-slate-400"
                                        >
                                            —
                                        </span>
                                    @endif
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="8"
                                    class="px-6 py-12 text-center
                                           text-sm text-slate-500"
                                >
                                    No Delivery Receipts have been
                                    recorded for this allocation.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>
            </div>
        </section>

        {{-- =========================================================
            PER-DR PPE COMPARISON
        ========================================================== --}}
        @foreach($receipts as $receipt)

            <section
                class="overflow-hidden rounded-3xl border
                       border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 border-b
                           border-slate-200 px-6 py-5 sm:px-7
                           lg:flex-row lg:items-center
                           lg:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-[0.16em]
                                   text-[#0284C7]"
                        >
                            Allocation versus actual
                        </p>

                        <h2
                            class="mt-1 text-lg font-bold
                                   text-slate-950"
                        >
                            {{ $receipt->dr_number }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Delivered on
                            {{ $receipt->delivery_date?->format('F d, Y') ?? '—' }}
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-slate-100
                               px-4 py-3 text-right"
                    >
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wider text-slate-400"
                        >
                            Actual this delivery
                        </p>

                        <p
                            class="mt-1 text-xl font-bold
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
                        </p>
                    </div>
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
                                    PPE Item
                                </th>

                                <th class="px-6 py-4 text-left">
                                    Size / Label
                                </th>

                                <th class="px-6 py-4 text-center">
                                    Allocation
                                </th>

                                <th class="px-6 py-4 text-center">
                                    Actual Received
                                </th>

                                <th class="px-6 py-4 text-center">
                                    Difference
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @foreach(
                                $provinceDistribution->items
                                as $allocationItem
                            )
                                @php
                                    $receiptItem =
                                        $receipt
                                            ->items
                                            ->firstWhere(
                                                'province_distribution_item_id',
                                                $allocationItem->id
                                            );

                                    $actual =
                                        (int) (
                                            $receiptItem
                                                ?->received_quantity
                                            ?? 0
                                        );

                                    $difference = max(
                                        0,
                                        (int) $allocationItem->quantity
                                            - $actual
                                    );
                                @endphp

                                <tr class="hover:bg-slate-50">
                                    <td
                                        class="px-6 py-4 font-semibold
                                               text-slate-900"
                                    >
                                        {{ $allocationItem->item->item_name }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-600">
                                        {{ $allocationItem->item->label ?: '—' }}
                                    </td>

                                    <td
                                        class="px-6 py-4 text-center
                                               font-semibold
                                               text-slate-900"
                                    >
                                        {{
                                            number_format(
                                                $allocationItem
                                                    ->quantity
                                            )
                                        }}
                                    </td>

                                    <td
                                        class="px-6 py-4 text-center
                                               font-bold text-blue-700"
                                    >
                                        {{ number_format($actual) }}
                                    </td>

                                    <td
                                        class="px-6 py-4 text-center
                                               font-semibold
                                               {{
                                                   $difference > 0
                                                       ? 'text-amber-700'
                                                       : 'text-green-700'
                                               }}"
                                    >
                                        {{ number_format($difference) }}
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </section>

        @endforeach

    </div>

</x-po_dashboard_layout>