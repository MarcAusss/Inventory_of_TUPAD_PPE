<x-po_dashboard_layout title="Provincial PPE Stock Tracking">
    <div class="mx-auto max-w-[1800px] space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#2E628D]">TSSD PPE Tracking Center</p>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Provincial Office
                        Remaining PPE Stock</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Consolidated live balance of PPE currently held by every Provincial Office. TSSD can use this
                        page to identify low stock, remaining inventory, and stock concentration by province.
                    </p>
                </div>
                <div class="rounded-2xl bg-[#F2F8FB] px-5 py-4 text-right ring-1 ring-[#B7D6E6]">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">System-wide remaining</p>
                    <p class="mt-1 text-3xl font-black text-[#2E628D]">{{ number_format($totalAvailable) }}</p>
                    <p class="text-xs text-slate-500">PPE units across {{ number_format($trackedProvinceCount) }}
                        province(s)</p>
                </div>
            </div>
        </section>

        @include('tssd.tracking._navigation')

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" action="{{ route('tssd.tracking.provincial-stock') }}"
                class="grid gap-3 border-b border-slate-200 bg-slate-50/70 p-5 md:grid-cols-[1fr_260px_auto_auto]">
                <input type="search" name="search" value="{{ $search }}" placeholder="Search province..."
                    class="rounded-xl border-slate-300 focus:border-[#2E628D] focus:ring-[#2E628D]">
                <select name="province_id"
                    class="rounded-xl border-slate-300 focus:border-[#2E628D] focus:ring-[#2E628D]">
                    <option value="">All Provincial Offices</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province->id }}" @selected((int) $provinceId === (int) $province->id)>{{ $province->name }}</option>
                    @endforeach
                </select>
                <button
                    class="rounded-xl bg-[#2E628D] px-5 py-3 text-sm font-bold text-white hover:bg-[#244F73]">Filter</button>
                <a href="{{ route('tssd.tracking.provincial-stock') }}"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </form>

            @php
                $ppeHeaderGroups = $items
                    ->groupBy(function ($item): string {
                        $name = \App\Models\Item::canonicalItemName((string) $item->item_name);
                        $normalized = strtolower(str_replace([' ', '-', '_'], '', $name));

                        return match ($normalized) {
                            'longsleeves' => 'longsleeves',
                            'rubberboots' => 'rubberboots',
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
            @endphp
            <div class="overflow-x-auto">
                <table class="ppe-tracking-table min-w-full text-sm">
                    <thead>
                        <tr>
                            <th rowspan="2" class="min-w-52 px-5 py-4 text-left">Provincial Office</th>
                            @foreach ($ppeHeaderGroups as $group)
                                @if ($group['grouped'])
                                    <th colspan="{{ $group['items']->count() + 1 }}"
                                        class="min-w-28 px-4 py-4 text-center">{{ $group['name'] }}</th>
                                @else
                                    <th rowspan="2" class="min-w-28 px-4 py-4 text-center">
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
                            @foreach ($ppeHeaderGroups as $group)
                                @if ($group['grouped'])
                                    @foreach ($group['items'] as $item)
                                        <th class="min-w-24 px-4 py-3 text-center">{{ $item->label ?: '—' }}</th>
                                    @endforeach
                                    <th class="min-w-24 px-4 py-3 text-center font-black">Total</th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($visibleProvinces as $province)
                            <tr class="hover:bg-[#F7FBFD]">
                                <td class="px-5 py-4 font-bold text-slate-900">{{ $province->name }}</td>
                                @foreach ($ppeHeaderGroups as $group)
                                    @if ($group['grouped'])
                                        @php
                                            $groupTotal = 0;
                                        @endphp
                                        @foreach ($group['items'] as $item)
                                            @php
                                                $quantity = (int) ($province->tracking_quantities[$item->id] ?? 0);
                                            @endphp
                                            @php
                                                $groupTotal += $quantity;
                                            @endphp
                                            <td
                                                class="px-4 py-4 text-center font-semibold {{ $quantity <= 0 ? 'text-red-700' : 'text-slate-900' }}">
                                                {{ number_format($quantity) }}
                                            </td>
                                        @endforeach
                                        <td class="bg-[#F2F8FB] px-4 py-4 text-center font-black text-[#2E628D]">
                                            {{ number_format($groupTotal) }}</td>
                                    @else
                                        @php
                                            $item = $group['items']->first();
                                        @endphp
                                        @php
                                            $quantity = (int) ($province->tracking_quantities[$item->id] ?? 0);
                                        @endphp
                                        <td
                                            class="px-4 py-4 text-center font-semibold {{ $quantity <= 0 ? 'text-red-700' : 'text-slate-900' }}">
                                            {{ number_format($quantity) }}
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $items->count() + $ppeHeaderGroups->where('grouped', true)->count() + 1 }}"
                                    class="px-6 py-14 text-center text-slate-500">No Provincial Office stock matched the
                                    current filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($visibleProvinces->total() > 0)
                        <tfoot>
                            <tr class="bg-[#EAF4F9]">
                                <td
                                    class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.14em] text-[#2E628D]">
                                    Overall Total</td>
                                @foreach ($ppeHeaderGroups as $group)
                                    @if ($group['grouped'])
                                        @php($overallGroupTotal = 0)
                                        @foreach ($group['items'] as $item)
                                            @php($overallQuantity = (int) ($itemTotals[$item->id] ?? 0))
                                            @php($overallGroupTotal += $overallQuantity)
                                            <td class="px-4 py-4 text-center text-base font-black text-slate-950">
                                                {{ number_format($overallQuantity) }}</td>
                                        @endforeach
                                        <td
                                            class="bg-[#DDEEF6] px-4 py-4 text-center text-base font-black text-[#2E628D]">
                                            {{ number_format($overallGroupTotal) }}</td>
                                    @else
                                        @php($item = $group['items']->first())
                                        @php($overallQuantity = (int) ($itemTotals[$item->id] ?? 0))
                                        <td class="px-4 py-4 text-center text-base font-black text-slate-950">
                                            {{ number_format($overallQuantity) }}</td>
                                    @endif
                                @endforeach
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if ($visibleProvinces->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">{{ $visibleProvinces->links() }}</div>
            @endif
        </section>
    </div>
</x-po_dashboard_layout>
