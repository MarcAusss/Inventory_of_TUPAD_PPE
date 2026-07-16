<x-po_dashboard_layout title="Assign Call-Off Number">
    @php
        $batch = $distributionBatch;
        $purchaseOrder = $batch->purchaseOrder;
        $allocations = $batch->provinceDistributions;

        $normalizeAllocation = function ($allocation) {
            $data = [
                'ls_m' => 0,
                'ls_l' => 0,
                'bucket' => 0,
                'boots_9' => 0,
                'boots_10' => 0,
                'gloves' => 0,
                'mask' => 0,
            ];

            foreach ($allocation->items as $allocationItem) {
                $name = strtolower(trim((string) $allocationItem->item?->item_name));
                $label = strtolower(trim((string) $allocationItem->item?->label));
                $qty = (int) $allocationItem->quantity;

                if (in_array($name, ['long sleeve', 'long sleeves', 'longsleeve', 'longsleeves'], true)) {
                    if (in_array($label, ['m', 'medium'], true)) {
                        $data['ls_m'] += $qty;
                    }

                    if (in_array($label, ['l', 'large'], true)) {
                        $data['ls_l'] += $qty;
                    }
                } elseif ($name === 'bucket hat') {
                    $data['bucket'] += $qty;
                } elseif ($name === 'rubber boots') {
                    if (in_array($label, ['us9', 'us 9', '9'], true)) {
                        $data['boots_9'] += $qty;
                    }

                    if (in_array($label, ['us10', 'us 10', '10'], true)) {
                        $data['boots_10'] += $qty;
                    }
                } elseif (in_array($name, ['gloves', 'hand gloves', 'hand glove'], true)) {
                    $data['gloves'] += $qty;
                } elseif ($name === 'mask') {
                    $data['mask'] += $qty;
                }
            }

            return $data;
        };
    @endphp

    <div class="mx-auto max-w-[1900px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#2D94BE] to-[#339DCB]"></div>

            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#143A52] ring-1 ring-[#90C4DD]">
                            Supply Unit
                        </span>
                        <span
                            class="rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold text-[#143A52] ring-1 ring-[#90C4DD]">
                            Awaiting Call-Off Assignment
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        Assign Call-Off Number
                    </h1>

                    <p class="mt-2 text-sm text-[#36566E]">
                        Distribution Batch #{{ $batch->id }} submitted by
                        {{ $batch->creator?->name ?? 'TSSD Unit' }}.
                    </p>
                </div>

                <a href="{{ route('supply.call-offs.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-[#90C4DD] bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Back to Call-Offs
                </a>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
                <p class="font-bold text-red-800">Please correct the following:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['Distribution Batch', 'Batch #' . $batch->id], ['Purchase Order', $purchaseOrder?->po_number ?? '—'], ['Supplier', $purchaseOrder?->supplier?->supplier_name ?? '—'], ['Total Provinces', number_format($allocations->count())]] as [$label, $value])
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                    <p class="mt-3 text-xl font-bold text-slate-950">{{ $value }}</p>
                </article>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">
                    Provincial Allocations
                </p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">
                    Province Distribution Summary
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Review the consolidated PPE quantities assigned by TSSD to every provincial office in this
                    distribution batch.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1500px] w-full border-separate border-spacing-0">
                    <thead>
                        <tr class="text-xs font-bold uppercase tracking-wide text-white">
                            <th rowspan="2"
                                class="border-b border-r border-[#90C4DD] bg-[#339DCB] px-5 py-4 text-left">
                                Province
                            </th>
                            <th rowspan="2"
                                class="border-b border-r border-[#90C4DD] bg-[#339DCB] px-5 py-4 text-center">Delivery
                                Date</th>
                            <th rowspan="2"
                                class="border-b border-r border-[#90C4DD] bg-[#339DCB] px-5 py-4 text-left">Place of
                                Delivery</th>
                            <th colspan="3"
                                class="border-b border-r border-[#90C4DD] bg-[#339DCB] px-5 py-4 text-center">Long
                                Sleeves</th>
                            <th rowspan="2"
                                class="border-b border-r border-[#90C4DD] bg-[#339DCB] px-5 py-4 text-center">Bucket Hat
                            </th>
                            <th colspan="3"
                                class="border-b border-r border-[#90C4DD] bg-[#339DCB] px-5 py-4 text-center">Rubber
                                Boots</th>
                            <th rowspan="2"
                                class="border-b border-r border-[#90C4DD] bg-[#339DCB] px-5 py-4 text-center">Gloves
                            </th>
                            <th rowspan="2" class="border-b border-[#90C4DD] bg-[#339DCB] px-5 py-4 text-center">Mask
                            </th>
                        </tr>

                        <tr class="text-[11px] font-bold uppercase">
                            @foreach (['M', 'L', 'Total', 'US9', 'US10', 'Total'] as $label)
                                <th
                                    class="border-b border-r border-[#90C4DD] bg-[#2D94BE] px-4 py-3 text-center text-[#143A52]">
                                    {{ $label }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($allocations as $allocation)
                            @php($q = $normalizeAllocation($allocation))

                            <tr class="transition hover:bg-slate-50">
                                <td
                                    class="border-b border-r border-slate-200 px-5 py-4 font-bold uppercase text-slate-900">
                                    {{ $allocation->province?->name ?? '—' }}
                                </td>
                                <td
                                    class="border-b border-r border-slate-200 px-5 py-4 text-center text-sm text-slate-600">
                                    {{ $allocation->scheduled_delivery_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td
                                    class="min-w-56 border-b border-r border-slate-200 px-5 py-4 text-sm text-slate-600">
                                    {{ $allocation->place_of_delivery ?: '—' }}
                                </td>
                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['ls_m']) }}</td>
                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['ls_l']) }}</td>
                                <td
                                    class="border-b border-r border-slate-200 bg-[#DF979B]/10 px-4 py-4 text-center font-bold text-[#2D94BE]">
                                    {{ number_format($q['ls_m'] + $q['ls_l']) }}</td>
                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['bucket']) }}</td>
                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['boots_9']) }}</td>
                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['boots_10']) }}</td>
                                <td
                                    class="border-b border-r border-slate-200 bg-[#DF979B]/10 px-4 py-4 text-center font-bold text-[#2D94BE]">
                                    {{ number_format($q['boots_9'] + $q['boots_10']) }}</td>
                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['gloves']) }}</td>
                                <td class="border-b border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['mask']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-6 py-14 text-center text-sm text-slate-500">
                                    No provincial allocations were found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <form action="{{ route('supply.call-offs.review', $batch) }}" method="POST" enctype="multipart/form-data"
            class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            @csrf

            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">
                    Supply Unit Assignment
                </p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">
                    Official Call-Off Information
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Assigning these details will approve the distribution and release it to the Provincial Offices.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2 sm:p-7">
                <div>
                    <label for="call_off_number" class="mb-2 block text-sm font-bold text-slate-700">
                        Call-Off Number <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="call_off_number" name="call_off_number"
                        value="{{ old('call_off_number') }}" required maxlength="100"
                        placeholder="Example: CO-2026-0001"
                        class="w-full rounded-xl border-[#90C4DD] uppercase focus:border-[#339DCB] focus:ring-[#339DCB]">
                </div>

                <div>
                    <label for="call_off_date" class="mb-2 block text-sm font-bold text-slate-700">
                        Call-Off Date <span class="text-red-600">*</span>
                    </label>
                    <input type="date" id="call_off_date" name="call_off_date"
                        value="{{ old('call_off_date', now()->format('Y-m-d')) }}" required
                        class="w-full rounded-xl border-[#90C4DD] focus:border-[#339DCB] focus:ring-[#339DCB]">
                </div>

                <div class="lg:col-span-2">
                    <label for="approval_document" class="mb-2 block text-sm font-bold text-slate-700">
                        Approved Call-Off PDF <span class="text-red-600">*</span>
                    </label>
                    <input type="file" id="approval_document" name="approval_document" accept="application/pdf,.pdf"
                        required class="block w-full rounded-xl border border-[#90C4DD] bg-white px-4 py-3 text-sm">
                    <p class="mt-2 text-xs text-slate-500">PDF only. Maximum 10 MB.</p>
                </div>

                <div class="lg:col-span-2">
                    <label for="remarks" class="mb-2 block text-sm font-bold text-slate-700">
                        Supply Unit Remarks
                    </label>
                    <textarea id="remarks" name="remarks" rows="4" maxlength="5000"
                        class="w-full rounded-xl border-[#90C4DD] focus:border-[#339DCB] focus:ring-[#339DCB]">{{ old('remarks') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-6 py-5 sm:px-7">
                <button type="submit"
                    onclick="return confirm('Assign and approve this Call-Off? The allocations will become available to the Provincial Offices.');"
                    class="rounded-xl bg-[#339DCB] px-7 py-3 text-sm font-bold text-white transition hover:bg-[#641D21]">
                    Assign and Approve Call-Off
                </button>
            </div>
        </form>
    </div>
</x-po_dashboard_layout>
