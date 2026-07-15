<x-po_dashboard_layout title="Call-Off Details">

    @php
        $batch = $callOff->distributionBatch;
        $purchaseOrder = $batch?->purchaseOrder;
    @endphp

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- Header --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#641D21] via-[#970C13] to-[#ED1B24]"></div>
            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <div class="flex flex-wrap items-center gap-3">

                        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                            {{ $callOff->call_off_number }}
                        </h1>

                        @php
                            $statusClasses = match ($callOff->status) {
                                'Approved' => 'bg-green-100 text-green-800',
                                'Rejected' => 'bg-red-100 text-red-800',
                                'Cancelled' => 'bg-gray-200 text-slate-600',
                                'Completed' => 'bg-blue-100 text-blue-800',
                                default => 'bg-yellow-100 text-yellow-800',
                            };
                        @endphp

                        <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $statusClasses }}">
                            {{ $callOff->status }}
                        </span>

                    </div>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Distribution Batch #{{ $batch?->id ?? 'N/A' }}
                    </p>

                </div>

                <div class="flex flex-wrap gap-3">

                    <a href="{{ route('tssd.call-offs.index') }}"
                        class="rounded-xl border border-slate-300 bg-white px-5 py-3 font-semibold text-slate-600 transition hover:bg-slate-50">
                        Back to Call-Offs
                    </a>

                    @if ($callOff->status === 'Pending')
                        <form action="{{ route('tssd.call-offs.destroy', $callOff) }}" method="POST"
                            onsubmit="return confirm('Cancel this pending Call-Off?');">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="rounded-xl bg-red-600 px-5 py-3 font-semibold text-white transition hover:bg-red-700">
                                Cancel Call-Off
                            </button>

                        </form>
                    @endif

                </div>

            </div>
        </section>

        {{-- Success --}}
        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Call-Off Information --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">

                <h2 class="text-lg font-bold text-slate-950">
                    Call-Off Information
                </h2>

            </div>

            <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 sm:p-7 xl:grid-cols-4">

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Call-Off Number
                    </p>

                    <p class="mt-3 font-bold text-slate-900">
                        {{ $callOff->call_off_number }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Assigned Date
                    </p>

                    <p class="mt-3 font-bold text-slate-900">
                        {{ $callOff->assigned_at?->format('F d, Y') ?? 'Not set' }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Assigned By
                    </p>

                    <p class="mt-3 font-bold text-slate-900">
                        {{ $callOff->assignedBy?->name ?? 'Not available' }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Official Call-Off Date
                    </p>

                    <p class="mt-3 font-bold text-slate-900">
                        {{ $callOff->call_off_date?->format('F d, Y') ?? 'Pending Supply approval' }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Approved By
                    </p>

                    <p class="mt-3 font-bold text-slate-900">
                        {{ $callOff->approvedBy?->name ?? 'Pending approval' }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Approved At
                    </p>

                    <p class="mt-3 font-bold text-slate-900">
                        {{ $callOff->approved_at?->format('F d, Y h:i A') ?? 'Pending approval' }}
                    </p>

                </div>

                <div class="sm:col-span-2">

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Remarks
                    </p>

                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">
                        {{ $callOff->remarks ?: 'No remarks provided.' }}
                    </p>

                </div>

            </div>

        </section>

        {{-- Purchase Order Information --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">

                <h2 class="text-lg font-bold text-slate-950">
                    Source Purchase Order
                </h2>

            </div>

            <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 sm:p-7 xl:grid-cols-4">

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        PO Number
                    </p>

                    <p class="mt-3 font-bold text-slate-900">
                        {{ $purchaseOrder?->po_number ?? 'Not available' }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        PO Date
                    </p>

                    <p class="mt-3 font-bold text-slate-900">
                        {{ $purchaseOrder?->po_date?->format('F d, Y') ?? 'Not available' }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Supplier
                    </p>

                    <p class="mt-3 font-bold text-slate-900">
                        {{ $purchaseOrder?->supplier?->supplier_name ?? 'Not available' }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        NEFA Number
                    </p>

                    <p class="mt-3 font-bold text-slate-900">
                        {{ $purchaseOrder?->nefa_number ?? 'Not available' }}
                    </p>

                </div>

            </div>

        </section>

        {{-- Provincial Allocations --}}
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
                            <th rowspan="2" class="border border-[#7f3539] px-5 py-4 text-left">
                                Province
                            </th>

                            <th rowspan="2" class="border border-[#7f3539] px-4 py-4 text-center">
                                Delivery Date
                            </th>

                            <th rowspan="2" class="border border-[#7f3539] px-4 py-4 text-left">
                                Place of Delivery
                            </th>

                            <th colspan="3" class="border border-[#7f3539] px-4 py-3 text-center">
                                Long Sleeves
                            </th>

                            <th rowspan="2" class="border border-[#7f3539] px-4 py-4 text-center">
                                Bucket Hat
                            </th>

                            <th colspan="3" class="border border-[#7f3539] px-4 py-3 text-center">
                                Rubber Boots
                            </th>

                            <th rowspan="2" class="border border-[#7f3539] px-4 py-4 text-center">
                                Gloves
                            </th>

                            <th rowspan="2" class="border border-[#7f3539] px-4 py-4 text-center">
                                Mask
                            </th>
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
                        @foreach ($batch?->provinceDistributions ?? collect() as $allocation)
                            @php
                                $items = $allocation->items ?? collect();

                                $lsm = $items
                                    ->filter(
                                        fn($row) => $row->item &&
                                            $row->item->item_name === 'Long Sleeve' &&
                                            $row->item->label === 'Medium',
                                    )
                                    ->sum('quantity');

                                $lsl = $items
                                    ->filter(
                                        fn($row) => $row->item &&
                                            $row->item->item_name === 'Long Sleeve' &&
                                            $row->item->label === 'Large',
                                    )
                                    ->sum('quantity');

                                $bucket = $items
                                    ->filter(fn($row) => $row->item && $row->item->item_name === 'Bucket Hat')
                                    ->sum('quantity');

                                $us9 = $items
                                    ->filter(
                                        fn($row) => $row->item &&
                                            $row->item->item_name === 'Rubber Boots' &&
                                            $row->item->label === 'US9',
                                    )
                                    ->sum('quantity');

                                $us10 = $items
                                    ->filter(
                                        fn($row) => $row->item &&
                                            $row->item->item_name === 'Rubber Boots' &&
                                            $row->item->label === 'US10',
                                    )
                                    ->sum('quantity');

                                $gloves = $items
                                    ->filter(fn($row) => $row->item && $row->item->item_name === 'Hand Gloves')
                                    ->sum('quantity');

                                $mask = $items
                                    ->filter(fn($row) => $row->item && $row->item->item_name === 'Mask')
                                    ->sum('quantity');
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td class="border border-slate-200 px-5 py-4 font-bold uppercase text-[#641D21]">
                                    {{ $allocation->province?->name ?? 'Not available' }}
                                </td>

                                <td
                                    class="whitespace-nowrap border border-slate-200 px-4 py-4 text-center text-sm text-slate-600">
                                    {{ $allocation->scheduled_delivery_date?->format('M d, Y') ?? '—' }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-sm text-slate-600">
                                    {{ $allocation->place_of_delivery ?? '—' }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($lsm) }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($lsl) }}
                                </td>

                                <td
                                    class="border border-slate-200 bg-slate-50 px-4 py-4 text-center font-bold text-[#970C13]">
                                    {{ number_format($lsm + $lsl) }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($bucket) }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($us9) }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($us10) }}
                                </td>

                                <td
                                    class="border border-slate-200 bg-slate-50 px-4 py-4 text-center font-bold text-[#970C13]">
                                    {{ number_format($us9 + $us10) }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ number_format($gloves) }}
                                </td>

                                <td class="border border-slate-200 px-4 py-4 text-center font-semibold text-slate-700">
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
