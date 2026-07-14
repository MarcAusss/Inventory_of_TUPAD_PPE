<x-po_dashboard_layout title="Call-Off Approval">
<div class="mx-auto max-w-[1900px] space-y-6">
    <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#641D21] via-[#970C13] to-[#ED1B24]"></div>

        <div class="px-6 py-7 sm:px-8">
            <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-full bg-[#DF979B]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#970C13] ring-1 ring-[#DF979B]">
                    Supply Unit
                </span>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                    Call-Off Approval
                </span>
            </div>

            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                Call-Off Approval
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                Review Call-Off Numbers assigned by the TSSD Unit and verify the provincial allocations before approval.
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
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">TSSD-assigned records</p>
            <h2 class="mt-1 text-lg font-bold text-slate-950">Call-Off Records</h2>
            <p class="mt-1 text-sm text-slate-500">
                Open a record to review its Purchase Order and consolidated provincial allocation table.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[1200px] w-full divide-y divide-slate-200">
                <thead class="bg-slate-100">
                    <tr class="text-xs font-bold uppercase tracking-wide text-slate-600">
                        <th class="px-6 py-4 text-left">No.</th>
                        <th class="px-6 py-4 text-left">Call-Off Number</th>
                        <th class="px-6 py-4 text-left">Purchase Order</th>
                        <th class="px-6 py-4 text-left">Supplier</th>
                        <th class="px-6 py-4 text-center">Provinces</th>
                        <th class="px-6 py-4 text-left">Assigned By</th>
                        <th class="px-6 py-4 text-left">Assigned Date</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($callOffs as $callOff)
                        @php
                            $batch = $callOff->distributionBatch;
                            $purchaseOrder = $batch?->purchaseOrder;
                            $statusClass = match($callOff->status) {
                                'Approved' => 'bg-green-100 text-green-800 ring-green-200',
                                'Rejected' => 'bg-red-100 text-red-800 ring-red-200',
                                'Cancelled' => 'bg-slate-200 text-slate-700 ring-slate-300',
                                default => 'bg-amber-100 text-amber-800 ring-amber-200',
                            };
                        @endphp

                        <tr class="transition hover:bg-slate-50">
                            <td class="px-6 py-5 text-sm text-slate-500">
                                {{ $callOffs->firstItem() + $loop->index }}
                            </td>

                            <td class="px-6 py-5 font-bold text-[#641D21]">
                                {{ $callOff->call_off_number }}
                            </td>

                            <td class="px-6 py-5 text-sm font-semibold text-slate-800">
                                {{ $purchaseOrder?->po_number ?? '—' }}
                            </td>

                            <td class="min-w-56 px-6 py-5 text-sm text-slate-600">
                                {{ $purchaseOrder?->supplier?->supplier_name ?? '—' }}
                            </td>

                            <td class="px-6 py-5 text-center">
                                <span class="inline-flex min-w-9 justify-center rounded-full bg-[#DF979B]/20 px-3 py-1 text-xs font-bold text-[#970C13] ring-1 ring-[#DF979B]">
                                    {{ $batch?->provinceDistributions?->count() ?? 0 }}
                                </span>
                            </td>

                            <td class="px-6 py-5 text-sm text-slate-600">
                                {{ $callOff->assignedBy?->name ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600">
                                {{ $callOff->assigned_at?->format('M d, Y') ?? '—' }}
                            </td>

                            <td class="px-6 py-5 text-center">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">
                                    {{ $callOff->status }}
                                </span>
                            </td>

                            <td class="px-6 py-5 text-center">
                                <a href="{{ route('supply.call-offs.show', $callOff) }}"
                                   class="inline-flex rounded-lg bg-[#970C13] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#641D21]">
                                    Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-14 text-center text-sm text-slate-500">
                                No Call-Off records found.
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