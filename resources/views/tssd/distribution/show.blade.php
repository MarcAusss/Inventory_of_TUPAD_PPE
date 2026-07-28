<x-po_dashboard_layout title="TSSD Distribution Details">

    <div class="mx-auto max-w-[1900px] space-y-6">

        <section class="relative overflow-hidden rounded-3xl border border-[#E4EEF5] bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#2D94BE] to-[#339DCB]"></div>

            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#143A52] ring-1 ring-[#90C4DD]">
                            TSSD Unit
                        </span>

                        <span
                            class="rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-semibold text-[#227CA3] ring-1 ring-slate-200">
                            Distribution Details
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        Purchase Order {{ $purchaseOrder->po_number }}
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-[#36566E]">
                        Review purchased PPE, remaining stock, and quantities distributed to each provincial office.
                    </p>
                </div>

                <a href="{{ route('tssd.distributions.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-[#339DCB] transition hover:bg-[#F7FBFD]">
                    Back to Distributions
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-[#E4EEF5] bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">PO Number</p>
                <p class="mt-3 text-xl font-bold text-[#143A52]">{{ $purchaseOrder->po_number }}</p>
            </article>

            <article class="rounded-2xl border border-[#E4EEF5] bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Supplier</p>
                <p class="mt-3 text-lg font-bold text-[#143A52]">{{ $purchaseOrder->supplier?->supplier_name ?? '—' }}
                </p>
            </article>

            <article class="rounded-2xl border border-[#E4EEF5] bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">PO Date</p>
                <p class="mt-3 text-xl font-bold text-[#143A52]">
                    {{ optional($purchaseOrder->po_date)->format('M d, Y') ?? '—' }}</p>
            </article>

            <article class="rounded-2xl border border-[#E4EEF5] bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">NEFA Number</p>
                <p class="mt-3 text-xl font-bold text-[#2D94BE]">{{ $purchaseOrder->nefa_number ?? '—' }}</p>
            </article>
        </section>

        @php
            $distributionRows = collect($distributions ?? [])->filter()->values();

            $itemColumns = $purchaseOrder->items
                ->pluck('item')
                ->merge(
                    $distributionRows
                        ->flatMap(fn ($distribution) => $distribution->items ?? collect())
                        ->pluck('item')
                )
                ->filter()
                ->unique('id')
                ->sortBy(fn ($item) => strtolower($item->item_name . '|' . ($item->label ?? '')))
                ->values();

            $purchasedByItem = $purchaseOrder->items
                ->groupBy('item_id')
                ->map(fn ($rows) => (int) $rows->sum('quantity'));

            $distributedByItem = $distributionRows
                ->flatMap(fn ($distribution) => $distribution->items ?? collect())
                ->groupBy('item_id')
                ->map(fn ($rows) => (int) $rows->sum('quantity'));

            $remainingByItem = $itemColumns->mapWithKeys(fn ($item) => [
                $item->id => max(
                    0,
                    (int) ($purchasedByItem[$item->id] ?? 0)
                    - (int) ($distributedByItem[$item->id] ?? 0)
                ),
            ]);

            $tableMinimumWidth = max(1100, 560 + ($itemColumns->count() * 145));
        @endphp

        <section class="overflow-hidden rounded-3xl border border-[#E4EEF5] bg-white shadow-sm">
            <div class="border-b border-[#E4EEF5] px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">Purchase Order stock</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">Purchased and Remaining PPE</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($itemColumns as $item)
                    <article class="rounded-2xl border border-[#E4EEF5] bg-[#F7FBFD] p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-black">
                            {{ $item->item_name }}{{ $item->label ? ' (' . $item->label . ')' : '' }}
                        </p>

                        <div class="mt-4 space-y-3 text-black">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold">Purchased</span>
                                <span class="text-lg font-bold">{{ number_format((int) ($purchasedByItem[$item->id] ?? 0)) }}</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-[#E4EEF5] pt-3">
                                <span class="text-xs font-semibold">Remaining</span>
                                <span class="text-lg font-bold">{{ number_format((int) ($remainingByItem[$item->id] ?? 0)) }}</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="col-span-full py-8 text-center text-sm text-slate-500">No PPE items were found for this Purchase Order.</p>
                @endforelse
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-[#E4EEF5] bg-white shadow-sm">
            <div class="flex justify-between items-center px-2">
                <div class="border-b border-[#E4EEF5] px-6 py-5 sm:px-7">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">Provincial distribution
                        summary
                    </p>
                    <h2 class="mt-1 text-lg font-bold text-slate-950">Province Distribution</h2>
                    <p class="mt-1 text-sm text-[#70879A]">
                        Consolidated PPE quantities distributed to every provincial office.
                    </p>
                </div>
                <a href="{{ route('tssd.distributions.print', $purchaseOrder->id) }}" target="_blank"
                    class="inline-flex items-center justify-center mr-5 rounded-xl border border-[#2D94BE] bg-white px-5 py-3 text-sm font-bold text-[#2D94BE] transition hover:bg-[#339DCB] hover:text-white">
                    Print Distribution
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0" style="min-width: {{ $tableMinimumWidth }}px">
                    <thead>
                        <tr class="bg-[#2E628D] text-xs font-bold uppercase tracking-wide text-white">
                            <th class="border-b border-r border-white/20 px-5 py-4 text-left">Province</th>
                            <th class="border-b border-r border-white/20 px-5 py-4 text-center">Delivery Date</th>
                            <th class="border-b border-r border-white/20 px-5 py-4 text-left">Place of Delivery</th>
                            @foreach ($itemColumns as $item)
                                <th class="border-b border-r border-white/20 px-4 py-4 text-center">
                                    {{ $item->item_name }}
                                    @if ($item->label)
                                        <span class="block text-[10px] font-semibold normal-case opacity-90">{{ $item->label }}</span>
                                    @endif
                                </th>
                            @endforeach
                            <th class="border-b border-white/20 px-4 py-4 text-center">Total PPE</th>
                        </tr>
                    </thead>

                    <tbody class="text-black">
                        @foreach ($provinces as $province)
                            @php
                                $provinceDistribution = $distributionRows->first(
                                    fn ($distribution): bool => (int) $distribution->province_id === (int) $province->id
                                );
                                $quantities = collect($provinceDistribution?->items ?? [])
                                    ->mapWithKeys(fn ($row) => [(int) $row->item_id => (int) $row->quantity]);
                                $rowTotal = (int) $quantities->sum();
                            @endphp

                            <tr class="transition hover:bg-[#F7FBFD]">
                                <td class="border-b border-r border-[#E4EEF5] px-5 py-4 font-bold uppercase text-black">{{ $province->name }}</td>
                                <td class="border-b border-r border-[#E4EEF5] px-5 py-4 text-center text-sm text-black">
                                    {{ optional($provinceDistribution?->scheduled_delivery_date)->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="border-b border-r border-[#E4EEF5] px-5 py-4 text-left text-sm text-black">
                                    {{ $provinceDistribution?->place_of_delivery ?? '—' }}
                                </td>

                                @foreach ($itemColumns as $item)
                                    <td class="border-b border-r border-[#E4EEF5] px-4 py-4 text-center text-black">
                                        {{ number_format((int) ($quantities[$item->id] ?? 0)) }}
                                    </td>
                                @endforeach

                                <td class="border-b border-[#E4EEF5] bg-sky-50 px-4 py-4 text-center font-black text-black">
                                    {{ number_format($rowTotal) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

    </div>

</x-po_dashboard_layout>