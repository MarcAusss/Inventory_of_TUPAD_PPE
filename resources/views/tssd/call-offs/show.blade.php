<x-po_dashboard_layout title="Call-Off Details">
    @php
        $batch = $callOff->distributionBatch;
        $purchaseOrder = $batch?->purchaseOrder;
    @endphp

    <div class="mx-auto max-w-[1900px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#641D21] via-[#970C13] to-[#ED1B24]"></div>

            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                            {{ $callOff->call_off_number }}
                        </h1>

                        <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">
                            {{ $callOff->status }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Distribution Batch #{{ $batch?->id ?? 'N/A' }} · Assigned and approved by the Supply Unit.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('tssd.call-offs.print', $callOff) }}"
                       target="_blank"
                       class="rounded-xl bg-[#970C13] px-5 py-3 font-semibold text-white transition hover:bg-[#641D21]">
                        Print Distribution Summary
                    </a>

                    <a href="{{ route('tssd.call-offs.index') }}"
                       class="rounded-xl border border-slate-300 bg-white px-5 py-3 font-semibold text-slate-600 transition hover:bg-slate-50">
                        Back to Call-Offs
                    </a>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <h2 class="text-lg font-bold text-slate-950">Call-Off Information</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 sm:p-7 xl:grid-cols-4">
                @foreach([
                    ['Call-Off Number', $callOff->call_off_number],
                    ['Call-Off Date', $callOff->call_off_date?->format('F d, Y') ?? '—'],
                    ['Assigned By', $callOff->assignedBy?->name ?? '—'],
                    ['Assigned At', $callOff->assigned_at?->format('F d, Y h:i A') ?? '—'],
                    ['Approved By', $callOff->approvedBy?->name ?? '—'],
                    ['Approved At', $callOff->approved_at?->format('F d, Y h:i A') ?? '—'],
                    ['Purchase Order', $purchaseOrder?->po_number ?? '—'],
                    ['Supplier', $purchaseOrder?->supplier?->supplier_name ?? '—'],
                ] as [$label, $value])
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $label }}</p>
                        <p class="mt-2 font-semibold text-slate-900">{{ $value }}</p>
                    </div>
                @endforeach

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:col-span-2 xl:col-span-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Supply Unit Remarks</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                        {{ $callOff->remarks ?: 'No remarks provided.' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">
                    Provincial Allocations
                </p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">
                    Province Distribution Summary
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Consolidated PPE quantities distributed to every provincial office under this Call-Off Number.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1450px] w-full border-collapse">
                    <thead class="bg-[#641D21] text-white">
                        <tr class="text-xs font-bold uppercase tracking-wide">
                            <th rowspan="2" class="border border-[#7f3539] px-5 py-4 text-left">Province</th>
                            <th rowspan="2" class="border border-[#7f3539] px-4 py-4 text-center">Delivery Date</th>
                            <th rowspan="2" class="border border-[#7f3539] px-4 py-4 text-left">Place of Delivery</th>
                            <th colspan="3" class="border border-[#7f3539] px-4 py-3 text-center">Long Sleeves</th>
                            <th rowspan="2" class="border border-[#7f3539] px-4 py-4 text-center">Bucket Hat</th>
                            <th colspan="3" class="border border-[#7f3539] px-4 py-3 text-center">Rubber Boots</th>
                            <th rowspan="2" class="border border-[#7f3539] px-4 py-4 text-center">Gloves</th>
                            <th rowspan="2" class="border border-[#7f3539] px-4 py-4 text-center">Mask</th>
                        </tr>
                        <tr class="text-xs font-bold uppercase tracking-wide">
                            <th class="border border-[#7f3539] px-4 py-3 text-center">M</th>
                            <th class="border border-[#7f3539] px-4 py-3 text-center">L</th>
                            <th class="border border-[#7f3539] px-4 py-3 text-center">Total</th>
                            <th class="border border-[#7f3539] px-4 py-3 text-center">US9</th>
                            <th class="border border-[#7f3539] px-4 py-3 text-center">US10</th>
                            <th class="border border-[#7f3539] px-4 py-3 text-center">Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($batch?->provinceDistributions ?? collect() as $allocation)
                            @php
                                $items = $allocation->items ?? collect();

                                $lsm = $items->filter(fn ($row) =>
                                    $row->item
                                    && $row->item->item_name === 'Long Sleeve'
                                    && $row->item->label === 'Medium'
                                )->sum('quantity');

                                $lsl = $items->filter(fn ($row) =>
                                    $row->item
                                    && $row->item->item_name === 'Long Sleeve'
                                    && $row->item->label === 'Large'
                                )->sum('quantity');

                                $bucket = $items->filter(fn ($row) =>
                                    $row->item
                                    && $row->item->item_name === 'Bucket Hat'
                                )->sum('quantity');

                                $us9 = $items->filter(fn ($row) =>
                                    $row->item
                                    && $row->item->item_name === 'Rubber Boots'
                                    && $row->item->label === 'US9'
                                )->sum('quantity');

                                $us10 = $items->filter(fn ($row) =>
                                    $row->item
                                    && $row->item->item_name === 'Rubber Boots'
                                    && $row->item->label === 'US10'
                                )->sum('quantity');

                                $gloves = $items->filter(fn ($row) =>
                                    $row->item
                                    && $row->item->item_name === 'Hand Gloves'
                                )->sum('quantity');

                                $mask = $items->filter(fn ($row) =>
                                    $row->item
                                    && $row->item->item_name === 'Mask'
                                )->sum('quantity');
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td class="border border-slate-200 px-5 py-4 font-bold uppercase text-[#641D21]">
                                    {{ $allocation->province?->name ?? '—' }}
                                </td>
                                <td class="border border-slate-200 px-4 py-4 text-center text-sm text-slate-600">
                                    {{ $allocation->scheduled_delivery_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="border border-slate-200 px-4 py-4 text-sm text-slate-600">
                                    {{ $allocation->place_of_delivery ?? '—' }}
                                </td>
                                <td class="border border-slate-200 px-4 py-4 text-center">{{ number_format($lsm) }}</td>
                                <td class="border border-slate-200 px-4 py-4 text-center">{{ number_format($lsl) }}</td>
                                <td class="border border-slate-200 bg-slate-50 px-4 py-4 text-center font-bold text-[#970C13]">{{ number_format($lsm + $lsl) }}</td>
                                <td class="border border-slate-200 px-4 py-4 text-center">{{ number_format($bucket) }}</td>
                                <td class="border border-slate-200 px-4 py-4 text-center">{{ number_format($us9) }}</td>
                                <td class="border border-slate-200 px-4 py-4 text-center">{{ number_format($us10) }}</td>
                                <td class="border border-slate-200 bg-slate-50 px-4 py-4 text-center font-bold text-[#970C13]">{{ number_format($us9 + $us10) }}</td>
                                <td class="border border-slate-200 px-4 py-4 text-center">{{ number_format($gloves) }}</td>
                                <td class="border border-slate-200 px-4 py-4 text-center">{{ number_format($mask) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-po_dashboard_layout>
