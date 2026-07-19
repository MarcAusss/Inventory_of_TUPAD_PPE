<x-po_dashboard_layout title="Receiving History">

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- =========================================================
            PAGE HEADER
        ========================================================== --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#075985] via-[#0284C7] to-[#38BDF8]"></div>

            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-[#7DD3FC]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#0284C7] ring-1 ring-[#7DD3FC]">
                            Provincial Office
                        </span>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                            Receiving History
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        Submitted Delivery Receipts
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Review all Delivery Receipts submitted by your Provincial Office,
                        including Call-Off references, supplier information, receiving status,
                        and uploaded supporting documents.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route('provincial.receiving.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                    >
                        Back to Allocations
                    </a>

                    <a
                        href="{{ route('provincial.current-inventory.index') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-[#0284C7] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#075985]"
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
            DELIVERY RECEIPT TABLE
        ========================================================== --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#0284C7]">
                    Provincial receiving records
                </p>

                <h2 class="mt-1 text-lg font-bold text-slate-950">
                    Receiving History
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Each row represents a submitted Delivery Receipt under an approved provincial allocation.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1250px] w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr class="text-xs font-bold uppercase tracking-wide text-slate-600">
                            <th class="px-6 py-4 text-left">No.</th>
                            <th class="px-6 py-4 text-left">Delivery Receipt</th>
                            <th class="px-6 py-4 text-left">Call-Off Number</th>
                            <th class="px-6 py-4 text-left">Delivery Date</th>
                            <th class="px-6 py-4 text-left">Supplier</th>
                            <th class="px-6 py-4 text-left">Receiver</th>
                            <th class="px-6 py-4 text-center">Total Received</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($receipts as $receipt)
                            @php
                                $allocation = $receipt->provinceDistribution;
                                $batch = $allocation?->distributionBatch;
                                $callOff = $batch?->callOff;
                                $purchaseOrder = $batch?->purchaseOrder;

                                $hasDiscrepancy = $receipt->items->contains(
                                    fn ($item) =>
                                        (int) $item->assigned_quantity !==
                                        (int) $item->received_quantity
                                );

                                $totalReceived = (int) $receipt
                                    ->items
                                    ->sum('received_quantity');
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-500">
                                    {{ $receipts->firstItem() + $loop->index }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-5">
                                    <div class="font-semibold text-slate-900">
                                        {{ $receipt->dr_number }}
                                    </div>

                                    @if($receipt->document)
                                        <a
                                            href="{{ asset('storage/' . $receipt->document) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="mt-1 inline-flex text-xs font-bold text-[#0284C7] hover:underline"
                                        >
                                            View uploaded PDF
                                        </a>
                                    @else
                                        <div class="mt-1 text-xs text-slate-400">
                                            No document attached
                                        </div>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 font-semibold text-[#075985]">
                                    {{ $callOff?->call_off_number ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600">
                                    {{ $receipt->delivery_date?->format('M d, Y') ?? '—' }}
                                </td>

                                <td class="min-w-52 px-6 py-5 text-sm text-slate-600">
                                    {{ $purchaseOrder?->supplier?->supplier_name ?? '—' }}
                                </td>

                                <td class="min-w-48 px-6 py-5 text-sm text-slate-600">
                                    {{ $receipt->physical_receiver_name ?? '—' }}
                                </td>

                                <td class="px-6 py-5 text-center text-lg font-bold text-[#0284C7]">
                                    {{ number_format($totalReceived) }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 text-center">
                                    @if($hasDiscrepancy)
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 ring-1 ring-amber-200">
                                            With Discrepancy
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800 ring-1 ring-green-200">
                                            Complete
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 text-center">
                                    @if($allocation)
                                        <a
                                            href="{{ route('provincial.receiving.show', $allocation) }}"
                                            class="inline-flex items-center justify-center rounded-lg bg-[#0284C7] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#075985]"
                                        >
                                            View Details
                                        </a>
                                    @else
                                        <span class="text-sm text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-14 text-center">
                                    <div class="mx-auto max-w-md">
                                        <p class="font-semibold text-slate-700">
                                            No Delivery Receipts found
                                        </p>

                                        <p class="mt-1 text-sm text-slate-500">
                                            Submitted Delivery Receipts will appear here after a provincial allocation is received.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($receipts->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $receipts->links() }}
                </div>
            @endif
        </section>

    </div>

</x-po_dashboard_layout>