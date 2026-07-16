<x-po_dashboard_layout title="Call-Off Assignment">
    <div class="mx-auto max-w-[1900px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#2D94BE] to-[#339DCB]"></div>

            <div class="px-6 py-7 sm:px-8">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#143A52] ring-1 ring-[#90C4DD]">
                        Supply Unit
                    </span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                        Call-Off Assignment
                    </span>
                </div>

                <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                    Call-Off Assignment
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-[#36566E]">
                    Review submitted TSSD provincial allocations, assign the official Call-Off Number and date,
                    and upload the approved Call-Off PDF.
                </p>
            </div>
        </section>

        @if(session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#143A52]">
                    Awaiting Supply Unit
                </p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">
                    Submitted TSSD Distribution Batches
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    These batches do not have a Call-Off Number yet.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1050px] w-full divide-y divide-slate-200">
                    <thead class="bg-[#B7D6E6]/35">
                        <tr class="text-xs font-bold uppercase tracking-wide text-[#36566E]">
                            <th class="px-6 py-4 text-left">No.</th>
                            <th class="px-6 py-4 text-left">Batch</th>
                            <th class="px-6 py-4 text-left">Purchase Order</th>
                            <th class="px-6 py-4 text-left">Supplier</th>
                            <th class="px-6 py-4 text-center">Provinces</th>
                            <th class="px-6 py-4 text-left">Submitted By</th>
                            <th class="px-6 py-4 text-left">Distribution Date</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingBatches as $batch)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-5 text-sm text-slate-500">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-6 py-5 font-bold text-[#143A52]">
                                    Batch #{{ $batch->id }}
                                </td>
                                <td class="px-6 py-5 text-sm font-semibold text-slate-800">
                                    {{ $batch->purchaseOrder?->po_number ?? '—' }}
                                </td>
                                <td class="px-6 py-5 text-sm text-[#36566E]">
                                    {{ $batch->purchaseOrder?->supplier?->supplier_name ?? '—' }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex min-w-9 justify-center rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold text-[#143A52] ring-1 ring-[#90C4DD]">
                                        {{ $batch->provinceDistributions->count() }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-sm text-[#36566E]">
                                    {{ $batch->creator?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-5 text-sm text-[#36566E]">
                                    {{ $batch->distribution_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <a href="{{ route('supply.call-offs.show', $batch) }}"
                                       class="inline-flex rounded-lg bg-[#339DCB] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#2D94BE]">
                                        Assign Call-Off
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-14 text-center text-sm text-slate-500">
                                    No submitted distribution batches are waiting for Call-Off assignment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#143A52]">
                    Approved records
                </p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">
                    Assigned Call-Off Numbers
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1000px] w-full divide-y divide-slate-200">
                    <thead class="bg-[#B7D6E6]/35">
                        <tr class="text-xs font-bold uppercase tracking-wide text-[#36566E]">
                            <th class="px-6 py-4 text-left">No.</th>
                            <th class="px-6 py-4 text-left">Call-Off Number</th>
                            <th class="px-6 py-4 text-left">Purchase Order</th>
                            <th class="px-6 py-4 text-left">Supplier</th>
                            <th class="px-6 py-4 text-left">Call-Off Date</th>
                            <th class="px-6 py-4 text-left">Assigned By</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($callOffs as $callOff)
                            <tr>
                                <td class="px-6 py-5 text-sm text-slate-500">
                                    {{ $callOffs->firstItem() + $loop->index }}
                                </td>
                                <td class="px-6 py-5 font-bold text-[#143A52]">
                                    {{ $callOff->call_off_number }}
                                </td>
                                <td class="px-6 py-5 text-sm font-semibold text-slate-800">
                                    {{ $callOff->distributionBatch?->purchaseOrder?->po_number ?? '—' }}
                                </td>
                                <td class="px-6 py-5 text-sm text-[#36566E]">
                                    {{ $callOff->distributionBatch?->purchaseOrder?->supplier?->supplier_name ?? '—' }}
                                </td>
                                <td class="px-6 py-5 text-sm text-[#36566E]">
                                    {{ $callOff->call_off_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-5 text-sm text-[#36566E]">
                                    {{ $callOff->assignedBy?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800 ring-1 ring-green-200">
                                        {{ $callOff->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-14 text-center text-sm text-slate-500">
                                    No Call-Off Numbers have been assigned.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($callOffs->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $callOffs->links() }}
                </div>
            @endif
        </section>
    </div>
</x-po_dashboard_layout>
