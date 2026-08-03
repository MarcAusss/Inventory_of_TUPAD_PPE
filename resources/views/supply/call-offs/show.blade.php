<x-po_dashboard_layout title="Assign Call-Off Number">
    @php
        $batch = $distributionBatch;
        $purchaseOrder = $batch->purchaseOrder;
        $allocations = $batch->provinceDistributions;

        $itemColumns = $allocations
            ->flatMap(fn ($allocation) => $allocation->items)
            ->pluck('item')
            ->filter()
            ->unique('id')
            ->sortBy(fn ($item) => \App\Models\Item::displaySortKey($item->item_name, $item->label))
            ->values();

        $tableMinimumWidth = max(1100, 560 + ($itemColumns->count() * 145));
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

                <div class="flex flex-col gap-2 sm:flex-row">
                    <a href="{{ route('supply.call-offs.request-letter', $batch) }}" target="_blank"
                        class="inline-flex items-center justify-center rounded-xl bg-[#339DCB] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#247BA0]">
                        View TSSD Request Letter
                    </a>

                    <a href="{{ route('supply.call-offs.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-[#90C4DD] bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        Back to Call-Offs
                    </a>
                </div>
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
                        @forelse($allocations as $allocation)
                            @php
                                $quantities = $allocation->items
                                    ->mapWithKeys(fn ($row) => [(int) $row->item_id => (int) $row->quantity]);
                                $rowTotal = (int) $quantities->sum();
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td class="border-b border-r border-slate-200 px-5 py-4 font-bold uppercase text-black">
                                    {{ $allocation->province?->name ?? '—' }}
                                </td>
                                <td class="border-b border-r border-slate-200 px-5 py-4 text-center text-sm text-black">
                                    {{ $allocation->scheduled_delivery_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="min-w-56 border-b border-r border-slate-200 px-5 py-4 text-sm text-black">
                                    {{ $allocation->place_of_delivery ?: '—' }}
                                </td>

                                @foreach ($itemColumns as $item)
                                    <td class="border-b border-r border-slate-200 px-4 py-4 text-center text-black">
                                        {{ number_format((int) ($quantities[$item->id] ?? 0)) }}
                                    </td>
                                @endforeach

                                <td class="border-b border-slate-200 bg-sky-50 px-4 py-4 text-center font-black text-black">
                                    {{ number_format($rowTotal) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + $itemColumns->count() }}" class="px-6 py-14 text-center text-sm text-slate-500">
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
