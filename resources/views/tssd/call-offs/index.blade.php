<x-po_dashboard_layout title="Call-Off Status">
    <div class="mx-auto max-w-[1900px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#2D94BE] to-[#339DCB]"></div>

            <div class="px-6 py-7 sm:px-8">
                <div class="flex flex-wrap items-center gap-3">
                    <span
                        class="rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#143A52] ring-1 ring-[#90C4DD]">
                        TSSD Unit
                    </span>
                    <span
                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                        View Only
                    </span>
                </div>

                <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                    Call-Off Status
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-[#36566E]">
                    Monitor Call-Off Numbers assigned and approved by the Supply Unit for submitted TSSD distribution
                    batches.
                </p>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#143A52]">
                    Supply-approved records
                </p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">
                    Call-Off Records
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1200px] w-full divide-y divide-slate-200">
                    <thead class="bg-[#B7D6E6]/35">
                        <tr class="text-xs font-bold uppercase tracking-wide text-[#36566E]">
                            <th class="px-6 py-4 text-left">No.</th>
                            <th class="px-6 py-4 text-left">Call-Off Number</th>
                            <th class="px-6 py-4 text-left">Purchase Order</th>
                            <th class="px-6 py-4 text-left">Supplier</th>
                            {{-- <th class="px-6 py-4 text-center">Provinces</th> --}}
                            <th class="px-6 py-4 text-left">Call-Off Date</th>
                            <th class="px-6 py-4 text-left">Assigned By</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($callOffs as $callOff)
                            @php
                                $batch = $callOff->distributionBatch;
                                $purchaseOrder = $batch?->purchaseOrder;

                                $statusClasses = match ($callOff->status) {
                                    'Approved' => 'bg-green-100 text-green-800 ring-green-200',
                                    'Completed' => 'bg-[#B7D6E6]/35 text-[#143A52] ring-[#90C4DD]',
                                    default => 'bg-red-100 text-red-700 ring-red-200',
                                };
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-5 text-sm text-slate-500">
                                    {{ $callOffs->firstItem() + $loop->index }}
                                </td>
                                <td class="px-6 py-5 font-bold text-[#143A52]">
                                    {{ $callOff->call_off_number }}
                                </td>
                                <td class="px-6 py-5 text-sm font-semibold text-slate-800">
                                    {{ $purchaseOrder?->po_number ?? '—' }}
                                </td>
                                <td class="px-6 py-5 text-sm text-[#36566E]">
                                    {{ $purchaseOrder?->supplier?->supplier_name ?? '—' }}
                                </td>
                                <td class="px-6 py-5 text-
                                    {{ $batch?->provinceDistributions?->count() ?? 0 }}
                                </td>
                                <td class="px-6 py-5 text-sm text-[#36566E]">
                                    {{ $callOff->call_off_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-5 text-sm text-[#36566E]">
                                    {{ $callOff->assignedBy?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClasses }}">
                                        {{ $callOff->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <a href="{{ route('tssd.call-offs.show', $callOff) }}"
                                        class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-14 text-center text-sm text-slate-500">
                                    No Supply-approved Call-Off records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($callOffs->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $callOffs->links() }}
                </div>
            @endif
        </section>
    </div>
</x-po_dashboard_layout>
