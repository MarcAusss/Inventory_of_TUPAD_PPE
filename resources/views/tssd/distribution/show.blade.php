<x-po_dashboard_layout title="TSSD Distribution Details">

    <div class="mx-auto max-w-[1900px] space-y-6">

        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#641D21] via-[#970C13] to-[#ED1B24]"></div>

            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#DF979B]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#970C13] ring-1 ring-[#DF979B]">
                            TSSD Unit
                        </span>

                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                            Distribution Details
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        Purchase Order {{ $purchaseOrder->po_number }}
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Review purchased PPE, remaining stock, and quantities distributed to each provincial office.
                    </p>
                </div>

                <a href="{{ route('tssd.distributions.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Back to Distributions
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">PO Number</p>
                <p class="mt-3 text-xl font-bold text-[#641D21]">{{ $purchaseOrder->po_number }}</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Supplier</p>
                <p class="mt-3 text-lg font-bold text-slate-900">{{ $purchaseOrder->supplier?->supplier_name ?? '—' }}
                </p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">PO Date</p>
                <p class="mt-3 text-xl font-bold text-slate-900">
                    {{ optional($purchaseOrder->po_date)->format('M d, Y') ?? '—' }}</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">NEFA Number</p>
                <p class="mt-3 text-xl font-bold text-[#970C13]">{{ $purchaseOrder->nefa_number ?? '—' }}</p>
            </article>
        </section>

        @php
            $ppeLabels = [
                'lsm' => 'Long Sleeve M',
                'lsl' => 'Long Sleeve L',
                'bucket' => 'Bucket Hat',
                'us9' => 'Boots US9',
                'us10' => 'Boots US10',
                'gloves' => 'Hand Gloves',
                'mask' => 'Mask',
            ];

            /*
             * Support either:
             * 1. A flat collection of distribution rows, or
             * 2. A collection already grouped by province ID.
             */
            $distributionRows = collect($distributions ?? []);

            $distributionsByProvince = $distributionRows
                ->flatten(1)
                ->filter()
                ->groupBy(function ($row) {
                    return $row->province_id ??
                        ($row->tssd_province_distribution_id ??
                            ($row->provinceDistribution?->province_id ?? $row->province?->id));
                });
        @endphp

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">Purchase Order stock</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">Purchased and Remaining PPE</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
                @foreach ($ppeLabels as $key => $label)
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $label }}</p>

                        <div class="mt-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500">Purchased</span>
                                <span
                                    class="text-lg font-bold text-slate-900">{{ number_format($purchased[$key] ?? 0) }}</span>
                            </div>

                            <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                                <span class="text-xs font-semibold text-slate-500">Remaining</span>
                                <span
                                    class="text-lg font-bold {{ ($remaining[$key] ?? 0) > 0 ? 'text-green-700' : 'text-slate-400' }}">
                                    {{ number_format($remaining[$key] ?? 0) }}
                                </span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">Provincial distribution summary
                </p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">Province Distribution</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Consolidated PPE quantities distributed to every provincial office.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1250px] w-full border-separate border-spacing-0">
                    <thead>
                        <tr class="text-xs font-bold uppercase tracking-wide text-white">
                            <th rowspan="2"
                                class="border-b border-r border-slate-300 bg-slate-700 px-5 py-4 text-left">Province
                            </th>
                            <th colspan="3"
                                class="border-b border-r border-slate-300 bg-[#970C13] px-5 py-4 text-center">Long
                                Sleeves</th>
                            <th rowspan="2"
                                class="border-b border-r border-slate-300 bg-slate-700 px-5 py-4 text-center">Bucket Hat
                            </th>
                            <th colspan="3"
                                class="border-b border-r border-slate-300 bg-[#970C13] px-5 py-4 text-center">Rubber
                                Boots</th>
                            <th rowspan="2"
                                class="border-b border-r border-slate-300 bg-slate-700 px-5 py-4 text-center">Gloves
                            </th>
                            <th rowspan="2" class="border-b border-slate-300 bg-slate-700 px-5 py-4 text-center">Mask
                            </th>
                        </tr>

                        <tr class="text-[11px] font-bold uppercase">
                            <th
                                class="border-b border-r border-slate-300 bg-[#DF979B] px-4 py-3 text-center text-[#641D21]">
                                M</th>
                            <th
                                class="border-b border-r border-slate-300 bg-[#DF979B] px-4 py-3 text-center text-[#641D21]">
                                L</th>
                            <th
                                class="border-b border-r border-slate-300 bg-[#DF979B] px-4 py-3 text-center text-[#641D21]">
                                Total</th>
                            <th
                                class="border-b border-r border-slate-300 bg-[#DF979B] px-4 py-3 text-center text-[#641D21]">
                                US9</th>
                            <th
                                class="border-b border-r border-slate-300 bg-[#DF979B] px-4 py-3 text-center text-[#641D21]">
                                US10</th>
                            <th
                                class="border-b border-r border-slate-300 bg-[#DF979B] px-4 py-3 text-center text-[#641D21]">
                                Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($provinces as $province)
                            @php
                                /*
                                 * Find the saved provincial distribution for this province.
                                 *
                                 * Supports:
                                 * - Collection of ProvinceDistribution models
                                 * - Collection grouped by province ID
                                 */
                                $provinceDistribution = collect($distributions)
                                    ->flatten(1)
                                    ->first(function ($distribution) use ($province) {
                                        return (int) ($distribution->province_id ??
                                            ($distribution->province?->id ?? 0)) === (int) $province->id;
                                    });

                                /*
                                 * In the current structure, quantities are stored in
                                 * $provinceDistribution->items.
                                 */
                                $distributedItems = collect($provinceDistribution?->items ?? []);

                                $lsm = $distributedItems
                                    ->filter(function ($row) {
                                        $name = strtolower(trim((string) $row->item?->item_name));

                                        $label = strtolower(trim((string) $row->item?->label));

                                        return in_array(
                                            $name,
                                            ['long sleeve', 'long sleeves', 'longsleeve', 'longsleeves'],
                                            true,
                                        ) && in_array($label, ['m', 'medium'], true);
                                    })
                                    ->sum('quantity');

                                $lsl = $distributedItems
                                    ->filter(function ($row) {
                                        $name = strtolower(trim((string) $row->item?->item_name));

                                        $label = strtolower(trim((string) $row->item?->label));

                                        return in_array(
                                            $name,
                                            ['long sleeve', 'long sleeves', 'longsleeve', 'longsleeves'],
                                            true,
                                        ) && in_array($label, ['l', 'large'], true);
                                    })
                                    ->sum('quantity');

                                $bucket = $distributedItems
                                    ->filter(function ($row) {
                                        $name = strtolower(trim((string) $row->item?->item_name));

                                        return in_array($name, ['bucket hat', 'bucket hats'], true);
                                    })
                                    ->sum('quantity');

                                $us9 = $distributedItems
                                    ->filter(function ($row) {
                                        $name = strtolower(trim((string) $row->item?->item_name));

                                        $label = strtolower(trim((string) $row->item?->label));

                                        return in_array($name, ['rubber boot', 'rubber boots'], true) &&
                                            in_array($label, ['us9', 'us 9', '9'], true);
                                    })
                                    ->sum('quantity');

                                $us10 = $distributedItems
                                    ->filter(function ($row) {
                                        $name = strtolower(trim((string) $row->item?->item_name));

                                        $label = strtolower(trim((string) $row->item?->label));

                                        return in_array($name, ['rubber boot', 'rubber boots'], true) &&
                                            in_array($label, ['us10', 'us 10', '10'], true);
                                    })
                                    ->sum('quantity');

                                $gloves = $distributedItems
                                    ->filter(function ($row) {
                                        $name = strtolower(trim((string) $row->item?->item_name));

                                        return in_array($name, ['hand glove', 'hand gloves', 'glove', 'gloves'], true);
                                    })
                                    ->sum('quantity');

                                $mask = $distributedItems
                                    ->filter(function ($row) {
                                        $name = strtolower(trim((string) $row->item?->item_name));

                                        return in_array($name, ['mask', 'masks'], true);
                                    })
                                    ->sum('quantity');
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td
                                    class="border-b border-r border-slate-200 px-5 py-4 font-bold uppercase text-slate-900">
                                    {{ $province->name }}
                                </td>

                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($lsm) }}
                                </td>

                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($lsl) }}
                                </td>

                                <td
                                    class="border-b border-r border-slate-200 bg-[#DF979B]/10 px-4 py-4 text-center font-bold text-[#970C13]">
                                    {{ number_format($lsm + $lsl) }}
                                </td>

                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($bucket) }}
                                </td>

                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($us9) }}
                                </td>

                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($us10) }}
                                </td>

                                <td
                                    class="border-b border-r border-slate-200 bg-[#DF979B]/10 px-4 py-4 text-center font-bold text-[#970C13]">
                                    {{ number_format($us9 + $us10) }}
                                </td>

                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($gloves) }}
                                </td>

                                <td class="border-b border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($mask) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

    </div>

</x-po_dashboard_layout>
