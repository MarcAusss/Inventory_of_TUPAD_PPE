<x-po_dashboard_layout title="Call-Off Allocations">

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- =========================================================
            PAGE HEADER
        ========================================================== --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#641D21] via-[#970C13] to-[#ED1B24]"></div>

            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-[#DF979B]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#970C13] ring-1 ring-[#DF979B]">
                            Provincial Office
                        </span>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                            Receiving
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        Call-Off Allocations
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Review approved provincial PPE allocations, monitor quantities already received,
                        and submit one or more Delivery Receipts until every allocated item is completed.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route('provincial.receiving.history') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                    >
                        Receiving History
                    </a>

                    <a
                        href="{{ route('provincial.current-inventory.index') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-[#970C13] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#641D21]"
                    >
                        Current Inventory
                    </a>
                </div>
            </div>
        </section>

        {{-- =========================================================
            STATUS MESSAGES
        ========================================================== --}}
        @if(session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- =========================================================
            ALLOCATION TABLE
        ========================================================== --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">
                    Approved provincial allocations
                </p>

                <h2 class="mt-1 text-lg font-bold text-slate-950">
                    PPE Receiving Progress
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Partially received allocations remain open until the complete allocated quantity has been received.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1400px] w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr class="text-xs font-bold uppercase tracking-wide text-slate-600">
                            <th class="px-6 py-4 text-left">No.</th>
                            <th class="px-6 py-4 text-left">Call-Off Number</th>
                            <th class="px-6 py-4 text-left">Source PO</th>
                            <th class="px-6 py-4 text-left">Supplier</th>
                            <th class="px-6 py-4 text-center">Allocation</th>
                            <th class="px-6 py-4 text-center">Received</th>
                            <th class="px-6 py-4 text-center">Remaining</th>
                            <th class="px-6 py-4 text-center">DR Count</th>
                            <th class="px-6 py-4 text-center">Progress</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($allocations as $allocation)
                            @php
                                $batch = $allocation->distributionBatch;
                                $callOff = $batch?->callOff;
                                $purchaseOrder = $batch?->purchaseOrder;

                                $allocatedTotal = (int) $allocation
                                    ->items
                                    ->sum('quantity');

                                $receivedTotal = (int) $allocation
                                    ->deliveryReceipts
                                    ->flatMap(fn ($receipt) => $receipt->items)
                                    ->sum('received_quantity');

                                $remainingTotal = max(
                                    0,
                                    $allocatedTotal - $receivedTotal
                                );

                                $fullyReceived = $remainingTotal <= 0;

                                $canReceive = ! $fullyReceived
                                    && in_array(
                                        $allocation->status,
                                        [
                                            'Approved',
                                            'For Delivery',
                                            'Partially Received',
                                        ],
                                        true
                                    );

                                $statusClass = match($allocation->status) {
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

                                $percentage = $allocatedTotal > 0
                                    ? min(
                                        100,
                                        round(
                                            ($receivedTotal / $allocatedTotal) * 100
                                        )
                                    )
                                    : 0;
                            @endphp

                            <tr class="align-top transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-500">
                                    {{ $allocations->firstItem() + $loop->index }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-5">
                                    <div class="font-semibold text-[#641D21]">
                                        {{ $callOff?->call_off_number ?? '—' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-400">
                                        Batch #{{ $batch?->id ?? '—' }}
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 text-sm font-semibold text-slate-800">
                                    {{ $purchaseOrder?->po_number ?? '—' }}
                                </td>

                                <td class="min-w-52 px-6 py-5 text-sm text-slate-600">
                                    {{ $purchaseOrder?->supplier?->supplier_name ?? '—' }}
                                </td>

                                <td class="px-6 py-5 text-center text-lg font-bold text-slate-900">
                                    {{ number_format($allocatedTotal) }}
                                </td>

                                <td class="px-6 py-5 text-center text-lg font-bold text-[#970C13]">
                                    {{ number_format($receivedTotal) }}
                                </td>

                                <td class="px-6 py-5 text-center text-lg font-bold {{ $remainingTotal > 0 ? 'text-amber-700' : 'text-green-700' }}">
                                    {{ number_format($remainingTotal) }}
                                </td>

                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex min-w-9 justify-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 ring-1 ring-slate-200">
                                        {{ number_format($allocation->deliveryReceipts->count()) }}
                                    </span>
                                </td>

                                <td class="min-w-48 px-6 py-5">
                                    <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                                        <span>Received</span>
                                        <span>{{ $percentage }}%</span>
                                    </div>

                                    <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-200">
                                        <div
                                            class="h-full rounded-full bg-[#970C13] transition-all"
                                            style="width: {{ $percentage }}%"
                                        ></div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 text-center">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">
                                        {{ $allocation->status }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 text-center">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        <a
                                            href="{{ route('provincial.receiving.show', $allocation) }}"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                        >
                                            View
                                        </a>

                                        @if($canReceive)
                                            <a
                                                href="{{ route('provincial.receiving.create', $allocation) }}"
                                                class="inline-flex items-center justify-center rounded-lg bg-[#970C13] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#641D21]"
                                            >
                                                Receive PPE
                                            </a>
                                        @else
                                            <span class="inline-flex rounded-lg bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400">
                                                Completed
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-14 text-center">
                                    <div class="mx-auto max-w-md">
                                        <p class="font-semibold text-slate-700">
                                            No approved allocations found
                                        </p>

                                        <p class="mt-1 text-sm text-slate-500">
                                            Approved Call-Off allocations assigned to your province will appear here.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($allocations->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $allocations->links() }}
                </div>
            @endif
        </section>

    </div>

</x-po_dashboard_layout>