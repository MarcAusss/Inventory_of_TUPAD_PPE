<x-po_dashboard_layout title="TSSD PPE Summary">
    <div class="mx-auto max-w-[1900px] space-y-6" x-data="{ receiptOpen: false, selectedReceipt: null, receiptMap: @js($receiptModalData) }" @keydown.escape.window="receiptOpen = false">

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 2xl:flex-row 2xl:items-end 2xl:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#2E628D]">TSSD PPE Tracking Center</p>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">PPE Distribution
                        Summary</h1>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">
                        End-to-end stock view from Purchase Order to Call-Off and Delivery Receipt. Delivery Receipts
                        remain grouped in one column, while Purchases and Ending Balance are shown as matching PPE
                        blocks for each receipt.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-5 2xl:min-w-[820px]">
                    <div class="rounded-2xl bg-slate-50 p-4 text-center ring-1 ring-slate-200">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Allocations</p>
                        <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($allocationCount) }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#F2F8FB] p-4 text-center ring-1 ring-[#B7D6E6]">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#2E628D]">Call-Off PPE</p>
                        <p class="mt-1 text-2xl font-black text-[#2E628D]">{{ number_format($totalAllocated) }}</p>
                    </div>
                    <div class="rounded-2xl bg-cyan-50 p-4 text-center ring-1 ring-cyan-200">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-cyan-700">Received</p>
                        <p class="mt-1 text-2xl font-black text-cyan-800">{{ number_format($totalReceived) }}</p>
                    </div>
                    <div class="rounded-2xl bg-violet-50 p-4 text-center ring-1 ring-violet-200">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-violet-700">Projects</p>
                        <p class="mt-1 text-2xl font-black text-violet-800">{{ number_format($totalProjectIssued) }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-4 text-center ring-1 ring-emerald-200">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Call-Off Left</p>
                        <p class="mt-1 text-2xl font-black text-emerald-800">{{ number_format($totalCallOffRemaining) }}
                        </p>
                        <p class="mt-1 text-[10px] font-semibold text-emerald-700">Ending balance:
                            {{ number_format($totalAvailableNow) }}</p>
                    </div>
                </div>
            </div>
        </section>

        @include('tssd.tracking._navigation')

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" action="{{ route('tssd.tracking.summary') }}"
                class="grid gap-3 border-b border-slate-200 bg-slate-50/70 p-5 xl:grid-cols-[1fr_240px_220px_auto_auto]">
                <input type="search" name="search" value="{{ $search }}"
                    placeholder="PO, Call-Off, DR, supplier, province..."
                    class="rounded-xl border-slate-300 focus:border-[#2E628D] focus:ring-[#2E628D]">

                <select name="province_id"
                    class="rounded-xl border-slate-300 focus:border-[#2E628D] focus:ring-[#2E628D]">
                    <option value="">All Provincial Offices</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province->id }}" @selected((int) $provinceId === (int) $province->id)>{{ $province->name }}</option>
                    @endforeach
                </select>

                <select name="status" class="rounded-xl border-slate-300 focus:border-[#2E628D] focus:ring-[#2E628D]">
                    <option value="">All receiving status</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption }}" @selected($status === $statusOption)>{{ $statusOption }}</option>
                    @endforeach
                </select>

                <button
                    class="rounded-xl bg-[#2E628D] px-5 py-3 text-sm font-bold text-white hover:bg-[#244F73]">Filter</button>
                <a href="{{ route('tssd.tracking.summary') }}"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </form>

            <div
                class="flex flex-wrap items-center gap-x-5 gap-y-2 border-b border-slate-200 bg-white px-5 py-3 text-[11px] font-semibold text-slate-500">
                <span class="font-extrabold uppercase tracking-wider text-[#2E628D]">Delivery Receipt row guide</span>
                <span>Each horizontal section after <strong class="text-slate-800">Delivery Receipts</strong> matches
                    the DR shown at the same height.</span>
                <span><strong class="text-slate-800">Purchases</strong> = PPE physically received in that Delivery
                    Receipt.</span>
                <span><strong class="text-slate-800">Ending Balance</strong> = PPE remaining from that same Delivery
                    Receipt after project distributions.</span>
                <span><strong class="text-slate-800">Call-Off Total</strong> = per-PPE sum of all Delivery Receipts for
                    that Call-Off and Provincial Office.</span>
            </div>

            @php
                /*
                 * Standard PPE display order for the PPE Distribution Summary:
                 * 1. Longsleeves (Medium, Large, Total)
                 * 2. Bucket Hat
                 * 3. Rubber Boots (US9, US10, Total)
                 * 4. Hand Gloves
                 * 5. Mask
                 * 6. Any additional PPE items alphabetically
                 */
                $orderedItems = $items
                    ->sortBy(function ($item): string {
                        $canonicalName = \App\Models\Item::canonicalItemName((string) ($item->item_name ?? ''));
                        $name = strtolower(str_replace([' ', '-', '_'], '', $canonicalName));
                        $label = strtolower(trim((string) ($item->label ?? '')));

                        $itemOrder = match (true) {
                            in_array($name, ['longsleeve', 'longsleeves'], true) => 10,
                            in_array($name, ['buckethat', 'buckethats'], true) => 20,
                            in_array($name, ['rubberboot', 'rubberboots'], true) => 30,
                            in_array($name, ['handglove', 'handgloves', 'glove', 'gloves'], true) => 40,
                            in_array($name, ['mask', 'masks'], true) => 50,
                            default => 100,
                        };

                        $labelOrder = match (true) {
                            in_array($name, ['longsleeve', 'longsleeves'], true)
                                && in_array($label, ['medium', 'm'], true) => 10,
                            in_array($name, ['longsleeve', 'longsleeves'], true)
                                && in_array($label, ['large', 'l'], true) => 20,
                            in_array($name, ['rubberboot', 'rubberboots'], true)
                                && in_array($label, ['us9', 'us 9', '9'], true) => 10,
                            in_array($name, ['rubberboot', 'rubberboots'], true)
                                && in_array($label, ['us10', 'us 10', '10'], true) => 20,
                            default => 50,
                        };

                        // Additional PPEs share itemOrder 100, so $name sorts them alphabetically.
                        return sprintf(
                            '%03d-%03d-%s-%s-%010d',
                            $itemOrder,
                            $labelOrder,
                            $name,
                            $label,
                            (int) $item->id,
                        );
                    })
                    ->values();

                /*
                 * Longsleeves and Rubber Boots are grouped because both need
                 * their own variant columns plus a Total column.
                 */
                $ppeHeaderGroups = $orderedItems
                    ->groupBy(function ($item): string {
                        $name = \App\Models\Item::canonicalItemName((string) $item->item_name);
                        $normalized = strtolower(str_replace([' ', '-', '_'], '', $name));

                        return match (true) {
                            in_array($normalized, ['longsleeve', 'longsleeves'], true) => 'longsleeves',
                            in_array($normalized, ['rubberboot', 'rubberboots'], true) => 'rubberboots',
                            default => 'item-' . $item->id,
                        };
                    })
                    ->map(function ($group, string $key): array {
                        $group = collect($group)->values();
                        $first = $group->first();

                        return [
                            'grouped' => in_array($key, ['longsleeves', 'rubberboots'], true),
                            'name' => \App\Models\Item::canonicalItemName((string) $first->item_name),
                            'items' => $group,
                        ];
                    })
                    ->values();

                $ppeColumnCount = $ppeHeaderGroups->sum(function (array $group): int {
                    return $group['items']->count() + ($group['grouped'] ? 1 : 0);
                });
            @endphp

            {{-- Fixed clone of the table header. JavaScript keeps it horizontally synchronized with the table. --}}
            <div id="ppe-summary-sticky-header"
                class="ppe-summary-sticky-header fixed z-40 hidden overflow-x-auto border-y border-[#B8C6D1] bg-[#2E628D] shadow-lg"
                aria-hidden="true"></div>

            <div id="ppe-summary-scroll" class="overflow-x-auto">
                <table id="ppe-summary-table" class="ppe-tracking-table min-w-max w-full text-sm">
                    <thead>
                        <tr>
                            <th rowspan="3" class="min-w-44 px-4 py-4 text-left">Provincial Office</th>
                            <th rowspan="3" class="min-w-40 px-4 py-4 text-left">Purchase Order</th>
                            <th rowspan="3" class="min-w-40 px-4 py-4 text-left">Call-Off</th>
                            <th rowspan="3" class="min-w-32 px-4 py-4 text-center">Approved Date</th>
                            <th rowspan="3" class="min-w-[270px] px-4 py-4 text-left">Delivery Receipts</th>
                            <th rowspan="3" class="min-w-36 px-4 py-4 text-center">Delivery Status</th>

                            <th colspan="{{ $ppeColumnCount }}"
                                class="px-4 py-4 text-center text-sm font-black uppercase tracking-wider">
                                Purchases
                            </th>
                            <th colspan="{{ $ppeColumnCount }}"
                                class="px-4 py-4 text-center text-sm font-black uppercase tracking-wider">
                                Ending Balance
                            </th>

                            {{-- <th rowspan="3" class="min-w-28 px-4 py-4 text-center">To Receive</th> --}}
                        </tr>

                        <tr>
                            {{-- Purchases PPE headers --}}
                            @foreach ($ppeHeaderGroups as $group)
                                @if ($group['grouped'])
                                    <th colspan="{{ $group['items']->count() + 1 }}"
                                        class="min-w-28 px-4 py-3 text-center">{{ $group['name'] }}</th>
                                @else
                                    <th rowspan="2" class="min-w-28 px-4 py-3 text-center">
                                        {{ $group['name'] }}
                                        @if ($group['items']->first()->label)
                                            <span
                                                class="block text-[10px] font-semibold uppercase tracking-wide text-white/80">{{ $group['items']->first()->label }}</span>
                                        @endif
                                    </th>
                                @endif
                            @endforeach

                            {{-- Ending Balance PPE headers --}}
                            @foreach ($ppeHeaderGroups as $group)
                                @if ($group['grouped'])
                                    <th colspan="{{ $group['items']->count() + 1 }}"
                                        class="min-w-28 px-4 py-3 text-center">{{ $group['name'] }}</th>
                                @else
                                    <th rowspan="2" class="min-w-28 px-4 py-3 text-center">
                                        {{ $group['name'] }}
                                        @if ($group['items']->first()->label)
                                            <span
                                                class="block text-[10px] font-semibold uppercase tracking-wide text-white/80">{{ $group['items']->first()->label }}</span>
                                        @endif
                                    </th>
                                @endif
                            @endforeach
                        </tr>

                        <tr>
                            {{-- Purchases size / total headers --}}
                            @foreach ($ppeHeaderGroups as $group)
                                @if ($group['grouped'])
                                    @foreach ($group['items'] as $item)
                                        <th class="min-w-28 px-4 py-3 text-center">{{ $item->label ?: '—' }}</th>
                                    @endforeach
                                    <th class="min-w-28 px-4 py-3 text-center font-black">Total</th>
                                @endif
                            @endforeach

                            {{-- Ending Balance size / total headers --}}
                            @foreach ($ppeHeaderGroups as $group)
                                @if ($group['grouped'])
                                    @foreach ($group['items'] as $item)
                                        <th class="min-w-28 px-4 py-3 text-center">{{ $item->label ?: '—' }}</th>
                                    @endforeach
                                    <th class="min-w-28 px-4 py-3 text-center font-black">Total</th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($allocations as $allocation)
                            @php
                                $batch = $allocation->distributionBatch;
                                $callOff = $batch?->callOff;
                                $purchaseOrder = $batch?->purchaseOrder;
                                $receipts = collect($allocation->summary_receipts ?? []);
                                $hasReceipts = $receipts->isNotEmpty();
                                $segmentClass = 'min-h-[104px]';

                                // Per-item Call-Off totals across every Delivery Receipt for this province allocation.
                                $purchaseTotalsByItem = $orderedItems->mapWithKeys(function ($item) use (
                                    $receipts,
                                ): array {
                                    return [
                                        (int) $item->id => (int) $receipts->sum(
                                            fn(array $receipt): int => (int) (($receipt['received_by_item'] ?? [])[$item->id] ?? 0),
                                        ),
                                    ];
                                });

                                $endingTotalsByItem = $orderedItems->mapWithKeys(function ($item) use (
                                    $receipts,
                                ): array {
                                    return [
                                        (int) $item->id => (int) $receipts->sum(
                                            fn(array $receipt): int => (int) (($receipt['remaining_by_item'] ?? [])[$item->id] ?? 0),
                                        ),
                                    ];
                                });
                            @endphp

                            <tr class="align-top hover:bg-[#F7FBFD]">
                                <td class="px-4 py-4">
                                    <p class="font-extrabold text-slate-900">{{ $allocation->province?->name ?? '—' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">Scheduled:
                                        {{ $allocation->scheduled_delivery_date?->format('M d, Y') ?? '—' }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-black text-slate-900">{{ $purchaseOrder?->po_number ?? '—' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $purchaseOrder?->supplier?->supplier_name ?? 'No supplier' }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-black text-[#2E628D]">{{ $callOff?->call_off_number ?? '—' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $callOff?->status ?? '—' }}</p>
                                </td>
                                <td class="px-4 py-4 text-center font-semibold text-slate-700">
                                    {{ $callOff?->approved_at?->format('M d, Y') ?? '—' }}
                                </td>

                                {{-- Delivery Receipts stay stacked inside one table cell and freeze on horizontal scroll. --}}
                                <td class="ppe-summary-sticky-dr p-0 align-top">
                                    <div class="divide-y divide-[#B7D6E6]">
                                        @forelse ($receipts as $receipt)
                                            <div class="{{ $segmentClass }} flex items-center px-4 py-3">
                                                <button type="button"
                                                    @click="selectedReceipt = receiptMap[{{ (int) $receipt['id'] }}]; receiptOpen = true"
                                                    class="w-full rounded-xl border border-[#B7D6E6] bg-[#F7FBFD] px-3 py-2 text-left transition hover:border-[#2E628D] hover:bg-white hover:shadow-sm">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <span
                                                            class="font-black text-[#2E628D]">{{ $receipt['dr_number'] }}</span>
                                                    </div>
                                                    <div
                                                        class="mt-1 flex items-center justify-between gap-3 text-[11px] text-slate-500">
                                                        <span>{{ $receipt['delivery_date'] }}</span>
                                                        <span>Received
                                                            {{ number_format($receipt['received_total']) }}</span>
                                                    </div>
                                                </button>
                                            </div>
                                        @empty
                                            <div class="{{ $segmentClass }} flex items-center px-4 py-3">
                                                <div
                                                    class="w-full rounded-xl border border-dashed border-red-200 bg-red-50 px-3 py-3 text-xs font-semibold text-red-700">
                                                    No Delivery Receipt yet
                                                </div>
                                            </div>
                                        @endforelse
                                        @if ($hasReceipts)
                                            <div
                                                class="flex min-h-[68px] items-center border-t-2 border-[#747474] bg-[#727272] px-4 py-3">
                                                <div>
                                                    <p
                                                        class="text-[10px] font-black uppercase tracking-[0.14em] text-white">
                                                        Call-Off Total</p>
                                                    <p class="mt-1 text-xs font-semibold text-white">All
                                                        {{ number_format($receipts->count()) }} Delivery Receipts</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- From Delivery Status onward, every cell is vertically divided to align with each DR above. --}}
                                <td class="p-0 align-top text-center">
                                    <div class="divide-y divide-[#B7D6E6]">
                                        @forelse ($receipts as $receipt)
                                            <div
                                                class="{{ $segmentClass }} flex flex-col items-center justify-center px-3 py-3">
                                                <span
                                                    class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 ring-1 ring-emerald-200">Received</span>
                                            </div>
                                        @empty
                                            <div
                                                class="{{ $segmentClass }} flex flex-col items-center justify-center px-3 py-3">
                                                <span
                                                    class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-800 ring-1 ring-red-200">Pending
                                                    Delivery</span>
                                            </div>
                                        @endforelse
                                        @if ($hasReceipts)
                                            <div
                                                class="flex min-h-[68px] items-center justify-center border-t-2 border-[#747474] bg-[#727272] px-3 py-3">
                                                <span
                                                    class="text-[10px] font-black uppercase tracking-[0.14em] text-white">Total</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Purchases: PPE physically received in each Delivery Receipt. --}}
                                @foreach ($ppeHeaderGroups as $group)
                                    @if ($group['grouped'])
                                        @foreach ($group['items'] as $item)
                                            <td class="p-0 align-top text-center">
                                                <div class="divide-y divide-[#B7D6E6]">
                                                    @forelse ($receipts as $receipt)
                                                        @php
                                                            $purchaseQuantity =
                                                                (int) (($receipt['received_by_item'] ?? [])[
                                                                    $item->id
                                                                ] ?? 0);
                                                        @endphp
                                                        <div
                                                            class="{{ $segmentClass }} flex items-center justify-center px-3 py-3">
                                                            <p
                                                                class="text-base {{ $purchaseQuantity > 0 ? 'text-slate-900' : 'text-slate-400' }}">
                                                                {{ number_format($purchaseQuantity) }}</p>
                                                        </div>
                                                    @empty
                                                        <div
                                                            class="{{ $segmentClass }} flex items-center justify-center px-3 py-3 text-slate-400">
                                                            0</div>
                                                    @endforelse
                                                    @if ($hasReceipts)
                                                        <div
                                                            class="flex min-h-[68px] items-center justify-center border-t-2 border-[#747474] bg-[#727272] px-3 py-3">
                                                            <p class="text-base text-white">
                                                                {{ number_format((int) $purchaseTotalsByItem->get((int) $item->id, 0)) }}
                                                            </p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        @endforeach

                                        <td class="p-0 align-top text-center">
                                            <div class="divide-y divide-[#B7D6E6] bg-[#F2F8FB]">
                                                @forelse ($receipts as $receipt)
                                                    @php
                                                        $purchaseGroupTotal = (int) $group['items']->sum(
                                                            fn($item) => (int) (($receipt['received_by_item'] ?? [])[
                                                                $item->id
                                                            ] ?? 0),
                                                        );
                                                    @endphp
                                                    <div
                                                        class="{{ $segmentClass }} flex items-center justify-center px-3 py-3">
                                                        <p class="text-base text-black">
                                                            {{ number_format($purchaseGroupTotal) }}</p>
                                                    </div>
                                                @empty
                                                    <div
                                                        class="{{ $segmentClass }} flex items-center justify-center px-3 py-3 text-slate-400">
                                                        0</div>
                                                @endforelse
                                                @if ($hasReceipts)
                                                    @php
                                                        $purchaseCallOffGroupTotal = (int) $group['items']->sum(
                                                            fn($item) => (int) $purchaseTotalsByItem->get(
                                                                (int) $item->id,
                                                                0,
                                                            ),
                                                        );
                                                    @endphp
                                                    <div
                                                        class="flex min-h-[68px] items-center justify-center border-t-2 border-[#2E628D] bg-[#727272] px-3 py-3">
                                                        <p class="text-base text-white">
                                                            {{ number_format($purchaseCallOffGroupTotal) }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @else
                                        @php
                                            $item = $group['items']->first();
                                        @endphp
                                        <td class="p-0 align-top text-center">
                                            <div class="divide-y divide-[#B7D6E6]">
                                                @forelse ($receipts as $receipt)
                                                    @php
                                                        $purchaseQuantity =
                                                            (int) (($receipt['received_by_item'] ?? [])[$item->id] ??
                                                                0);
                                                    @endphp
                                                    <div
                                                        class="{{ $segmentClass }} flex items-center justify-center px-3 py-3">
                                                        <p
                                                            class="text-base {{ $purchaseQuantity > 0 ? 'text-slate-900' : 'text-slate-400' }}">
                                                            {{ number_format($purchaseQuantity) }}</p>
                                                    </div>
                                                @empty
                                                    <div
                                                        class="{{ $segmentClass }} flex items-center justify-center px-3 py-3 text-slate-400">
                                                        0</div>
                                                @endforelse
                                                @if ($hasReceipts)
                                                    <div
                                                        class="flex min-h-[68px] items-center justify-center border-t-2 border-[#747474] bg-[#727272] px-3 py-3">
                                                        <p class="text-base text-white">
                                                            {{ number_format((int) $purchaseTotalsByItem->get((int) $item->id, 0)) }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                @endforeach

                                {{-- Ending Balance: remaining PPE from the matching Delivery Receipt. --}}
                                @foreach ($ppeHeaderGroups as $group)
                                    @if ($group['grouped'])
                                        @foreach ($group['items'] as $item)
                                            <td class="p-0 align-top text-center">
                                                <div class="divide-y divide-[#B7D6E6]">
                                                    @forelse ($receipts as $receipt)
                                                        @php
                                                            $endingQuantity =
                                                                (int) (($receipt['remaining_by_item'] ?? [])[
                                                                    $item->id
                                                                ] ?? 0);
                                                        @endphp
                                                        <div
                                                            class="{{ $segmentClass }} flex items-center justify-center px-3 py-3">
                                                            <p
                                                                class="text-base {{ $endingQuantity > 0 ? 'text-[#2E628D]' : 'text-slate-400' }}">
                                                                {{ number_format($endingQuantity) }}</p>
                                                        </div>
                                                    @empty
                                                        <div
                                                            class="{{ $segmentClass }} flex items-center justify-center px-3 py-3 text-slate-400">
                                                            0</div>
                                                    @endforelse
                                                    @if ($hasReceipts)
                                                        <div
                                                            class="flex min-h-[68px] items-center justify-center border-t-2 border-[#747474] bg-[#727272] px-3 py-3">
                                                            <p class="text-base text-white">
                                                                {{ number_format((int) $endingTotalsByItem->get((int) $item->id, 0)) }}
                                                            </p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        @endforeach

                                        <td class="p-0 align-top text-center">
                                            <div class="divide-y divide-[#B7D6E6] bg-[#F2F8FB]">
                                                @forelse ($receipts as $receipt)
                                                    @php
                                                        $endingGroupTotal = (int) $group['items']->sum(
                                                            fn($item) => (int) (($receipt['remaining_by_item'] ?? [])[
                                                                $item->id
                                                            ] ?? 0),
                                                        );
                                                    @endphp
                                                    <div
                                                        class="{{ $segmentClass }} flex items-center justify-center px-3 py-3">
                                                        <p
                                                            class="text-base {{ $endingGroupTotal > 0 ? 'text-[#2E628D]' : 'text-slate-400' }}">
                                                            {{ number_format($endingGroupTotal) }}</p>
                                                    </div>
                                                @empty
                                                    <div
                                                        class="{{ $segmentClass }} flex items-center justify-center px-3 py-3 text-slate-400">
                                                        0</div>
                                                @endforelse
                                                @if ($hasReceipts)
                                                    @php
                                                        $endingCallOffGroupTotal = (int) $group['items']->sum(
                                                            fn($item) => (int) $endingTotalsByItem->get(
                                                                (int) $item->id,
                                                                0,
                                                            ),
                                                        );
                                                    @endphp
                                                    <div
                                                        class="flex min-h-[68px] items-center justify-center border-t-2 border-[#2E628D] bg-[#727272] px-3 py-3">
                                                        <p class="text-base text-white">
                                                            {{ number_format($endingCallOffGroupTotal) }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @else
                                        @php
                                            $item = $group['items']->first();
                                        @endphp
                                        <td class="p-0 align-top text-center">
                                            <div class="divide-y divide-[#B7D6E6]">
                                                @forelse ($receipts as $receipt)
                                                    @php
                                                        $endingQuantity =
                                                            (int) (($receipt['remaining_by_item'] ?? [])[$item->id] ??
                                                                0);
                                                    @endphp
                                                    <div
                                                        class="{{ $segmentClass }} flex items-center justify-center px-3 py-3">
                                                        <p
                                                            class="text-base {{ $endingQuantity > 0 ? 'text-[#2E628D]' : 'text-slate-400' }}">
                                                            {{ number_format($endingQuantity) }}</p>
                                                    </div>
                                                @empty
                                                    <div
                                                        class="{{ $segmentClass }} flex items-center justify-center px-3 py-3 text-slate-400">
                                                        0</div>
                                                @endforelse
                                                @if ($hasReceipts)
                                                    <div
                                                        class="flex min-h-[68px] items-center justify-center border-t-2 border-[#747474] bg-[#727272] px-3 py-3">
                                                        <p class="text-base text-white">
                                                            {{ number_format((int) $endingTotalsByItem->get((int) $item->id, 0)) }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                @endforeach

                                {{-- To Receive remains a Call-Off/province-level value, so it is not duplicated for every DR. --}}
                                {{-- <td class="px-4 py-4 text-center align-middle">
                                    <span class="text-base font-black {{ (int) $allocation->summary_to_receive_total > 0 ? 'text-red-700' : 'text-emerald-700' }}">
                                        {{ number_format((int) $allocation->summary_to_receive_total) }}
                                    </span>
                                    <p class="mt-1 text-[9px] font-bold uppercase tracking-wide text-slate-500">Call-Off remaining to receive</p>
                                </td> --}}
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 2 * $ppeColumnCount + 7 }}"
                                        class="px-6 py-14 text-center text-slate-500">
                                        No Call-Off provincial allocation matched the current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($allocations->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $allocations->links() }}</div>
                @endif
            </section>

            <section class="rounded-2xl border border-[#B7D6E6] bg-[#F7FBFD] p-5 text-xs leading-5 text-slate-600">
                <p class="font-extrabold text-[#2E628D]">How Delivery Receipt remaining stock is calculated</p>
                <p class="mt-1">
                    If a Provincial project record is directly linked to a Delivery Receipt, that project quantity is
                    deducted from that DR. For newer project records stored at Call-Off level, usage is attributed to the
                    oldest received DR first (FIFO) so TSSD can still identify which Delivery Receipt has stock remaining.
                </p>
            </section>

            {{-- Delivery Receipt details modal --}}
            <div x-cloak x-show="receiptOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                role="dialog" aria-modal="true" aria-label="Delivery Receipt details">
                <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" @click="receiptOpen = false"></div>

                <div x-show="receiptOpen" x-transition
                    class="relative z-10 max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-[#F7FBFD] px-6 py-5">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#2E628D]">Delivery Receipt
                                Details</p>
                            <h2 class="mt-1 text-2xl font-black text-slate-950"
                                x-text="selectedReceipt?.dr_number || 'Delivery Receipt'"></h2>
                            <p class="mt-1 text-sm text-slate-500">
                                <span x-text="selectedReceipt?.call_off_number || '—'"></span>
                                <span class="mx-1">•</span>
                                <span x-text="selectedReceipt?.province || '—'"></span>
                            </p>
                        </div>
                        <button type="button" @click="receiptOpen = false"
                            class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-black text-slate-600 hover:bg-slate-50">Close</button>
                    </div>

                    <div class="max-h-[calc(90vh-105px)] overflow-y-auto p-6" x-show="selectedReceipt">
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Purchase Order</p>
                                <p class="mt-1 font-black text-slate-900" x-text="selectedReceipt?.po_number || '—'"></p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Delivery Date</p>
                                <p class="mt-1 font-black text-slate-900" x-text="selectedReceipt?.delivery_date || '—'">
                                </p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Received By</p>
                                <p class="mt-1 font-black text-slate-900" x-text="selectedReceipt?.receiver || '—'"></p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">DR Stock Remaining
                                </p>
                                <p class="mt-1 text-xl font-black text-[#2E628D]"
                                    x-text="Number(selectedReceipt?.remaining_total || 0).toLocaleString()"></p>
                            </div>
                        </div>

                        <template x-if="(selectedReceipt?.documents || []).length > 0">
                            <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="mr-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Documents
                                    </p>
                                    <template x-for="document in (selectedReceipt?.documents || [])"
                                        :key="document.url">
                                        <a :href="document.url" target="_blank" rel="noopener"
                                            class="inline-flex rounded-lg border border-[#B7D6E6] bg-[#F7FBFD] px-3 py-2 text-xs font-bold text-[#2E628D] hover:border-[#2E628D] hover:bg-white"
                                            x-text="document.name || 'View document'"></a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div class="mt-5 overflow-x-auto rounded-2xl border border-slate-200">
                            <table class="ppe-tracking-table min-w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left">PPE Item</th>
                                        <th class="px-4 py-3 text-center">Received</th>
                                        <th class="px-4 py-3 text-center">Project Used</th>
                                        <th class="px-4 py-3 text-center">Remaining</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="item in (selectedReceipt?.items || [])"
                                        :key="`${item.name}-${item.label}`">
                                        <tr>
                                            <td class="px-4 py-3">
                                                <p class="font-extrabold text-slate-900" x-text="item.name"></p>
                                                <p class="text-xs text-slate-500" x-text="item.label || '—'"></p>
                                            </td>
                                            <td class="px-4 py-3 text-center font-bold"
                                                x-text="Number(item.received || 0).toLocaleString()"></td>
                                            <td class="px-4 py-3 text-center font-bold text-violet-800"
                                                x-text="Number(item.project_used || 0).toLocaleString()"></td>
                                            <td class="px-4 py-3 text-center font-black"
                                                :class="Number(item.remaining || 0) > 0 ? 'text-amber-700' : 'text-emerald-700'"
                                                x-text="Number(item.remaining || 0).toLocaleString()"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-cyan-700">DR Received</p>
                                <p class="mt-1 text-2xl font-black text-cyan-800"
                                    x-text="Number(selectedReceipt?.received_total || 0).toLocaleString()"></p>
                            </div>
                            <div class="rounded-2xl border border-violet-200 bg-violet-50 p-4 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-violet-700">Used by Projects
                                </p>
                                <p class="mt-1 text-2xl font-black text-violet-800"
                                    x-text="Number(selectedReceipt?.project_used_total || 0).toLocaleString()"></p>
                            </div>
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Remaining</p>
                                <p class="mt-1 text-2xl font-black text-emerald-800"
                                    x-text="Number(selectedReceipt?.remaining_total || 0).toLocaleString()"></p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Submitted</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-800"
                                        x-text="selectedReceipt?.submitted_at || '—'"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Balance Status
                                    </p>
                                    <p class="mt-1 text-sm font-bold text-slate-800"
                                        x-text="selectedReceipt?.status || '—'"></p>
                                </div>
                            </div>
                            <div class="mt-4 border-t border-slate-200 pt-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Remarks</p>
                                <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-700"
                                    x-text="selectedReceipt?.remarks || 'No remarks.'"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @push('scripts')
                <script>
                    (() => {
                        const initPpeSummaryStickyTable = () => {
                            const scrollViewport = document.getElementById('ppe-summary-scroll');
                            const sourceTable = document.getElementById('ppe-summary-table');
                            const stickyViewport = document.getElementById('ppe-summary-sticky-header');

                            if (!scrollViewport || !sourceTable || !stickyViewport || stickyViewport.dataset.ready === '1') {
                                return;
                            }

                            const sourceHead = sourceTable.querySelector('thead');
                            if (!sourceHead) {
                                return;
                            }

                            stickyViewport.dataset.ready = '1';

                            const stickyTable = document.createElement('table');
                            stickyTable.className = sourceTable.className;
                            stickyTable.setAttribute('aria-hidden', 'true');
                            stickyTable.appendChild(sourceHead.cloneNode(true));
                            stickyViewport.replaceChildren(stickyTable);

                            const appHeader = document.querySelector('header.sticky');
                            let ticking = false;

                            const syncColumnWidths = () => {
                                const firstBodyRow = sourceTable.querySelector('tbody tr');
                                if (!firstBodyRow) {
                                    return;
                                }

                                const cells = Array.from(firstBodyRow.children);
                                if (!cells.length) {
                                    return;
                                }

                                let colgroup = stickyTable.querySelector('colgroup');
                                if (!colgroup) {
                                    colgroup = document.createElement('colgroup');
                                    stickyTable.insertBefore(colgroup, stickyTable.firstChild);
                                }

                                colgroup.replaceChildren();

                                cells.forEach((cell) => {
                                    const col = document.createElement('col');
                                    const width = cell.getBoundingClientRect().width;
                                    col.style.width = `${width}px`;
                                    col.style.minWidth = `${width}px`;
                                    colgroup.appendChild(col);
                                });

                                stickyTable.style.width = `${sourceTable.getBoundingClientRect().width}px`;
                                stickyTable.style.minWidth = `${sourceTable.scrollWidth}px`;
                            };

                            const updateStickyHeader = () => {
                                ticking = false;

                                const tableRect = sourceTable.getBoundingClientRect();
                                const viewportRect = scrollViewport.getBoundingClientRect();
                                const topOffset = appHeader ? appHeader.getBoundingClientRect().bottom : 0;
                                const stickyHeight = stickyViewport.offsetHeight || sourceHead.getBoundingClientRect()
                                    .height;

                                const shouldShow = tableRect.top < topOffset && tableRect.bottom > (topOffset +
                                    stickyHeight);

                                if (!shouldShow) {
                                    stickyViewport.classList.add('hidden');
                                    return;
                                }

                                stickyViewport.classList.remove('hidden');
                                stickyViewport.style.top = `${topOffset}px`;
                                stickyViewport.style.left = `${viewportRect.left}px`;
                                stickyViewport.style.width = `${viewportRect.width}px`;
                                stickyViewport.scrollLeft = scrollViewport.scrollLeft;
                            };

                            const requestUpdate = () => {
                                if (ticking) {
                                    return;
                                }

                                ticking = true;
                                window.requestAnimationFrame(updateStickyHeader);
                            };

                            const refresh = () => {
                                syncColumnWidths();
                                requestUpdate();
                            };

                            scrollViewport.addEventListener('scroll', () => {
                                stickyViewport.scrollLeft = scrollViewport.scrollLeft;
                                requestUpdate();
                            }, {
                                passive: true
                            });

                            window.addEventListener('scroll', requestUpdate, {
                                passive: true
                            });
                            window.addEventListener('resize', refresh, {
                                passive: true
                            });

                            if ('ResizeObserver' in window) {
                                const resizeObserver = new ResizeObserver(refresh);
                                resizeObserver.observe(sourceTable);
                                resizeObserver.observe(scrollViewport);
                            }

                            refresh();
                        };

                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', initPpeSummaryStickyTable, {
                                once: true
                            });
                        } else {
                            initPpeSummaryStickyTable();
                        }
                    })();
                </script>
            @endpush

        </div>
    </x-po_dashboard_layout>