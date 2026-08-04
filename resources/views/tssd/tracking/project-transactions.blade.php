<x-po_dashboard_layout title="Provincial Project PPE Transactions">
    <div class="mx-auto max-w-[1800px] space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#2E628D]">TSSD PPE Tracking Center</p>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Provincial Office Distribution per Project</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Read-only transaction trail of PPE issued by Provincial Offices to projects, including source Call-Off, Purchase Order, location, beneficiaries, and item quantities.
                    </p>
                </div>
                <div class="grid grid-cols-3 gap-2 sm:min-w-[500px]">
                    <div class="rounded-2xl bg-slate-50 p-4 text-center ring-1 ring-slate-200">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Transactions</p>
                        <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($transactionCount) }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#F2F8FB] p-4 text-center ring-1 ring-[#B7D6E6]">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">PPE Issued*</p>
                        <p class="mt-1 text-2xl font-black text-[#2E628D]">{{ number_format($pagePpeTotal) }}</p>
                    </div>
                    <div class="rounded-2xl bg-violet-50 p-4 text-center ring-1 ring-violet-200">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-violet-700">Beneficiaries*</p>
                        <p class="mt-1 text-2xl font-black text-violet-800">{{ number_format($pageBeneficiaryTotal) }}</p>
                    </div>
                </div>
            </div>
            <p class="mt-3 text-right text-[11px] text-slate-400">*Totals are for the current page.</p>
        </section>

        @include('tssd.tracking._navigation')

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" action="{{ route('tssd.tracking.project-transactions') }}"
                class="grid gap-3 border-b border-slate-200 bg-slate-50/70 p-5 lg:grid-cols-[1fr_230px_210px_auto_auto]">
                <input type="search" name="search" value="{{ $search }}" placeholder="Project, Call-Off, PO, location..."
                    class="rounded-xl border-slate-300 focus:border-[#2E628D] focus:ring-[#2E628D]">
                <select name="province_id" class="rounded-xl border-slate-300 focus:border-[#2E628D] focus:ring-[#2E628D]">
                    <option value="">All Provincial Offices</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province->id }}" @selected((int) $provinceId === (int) $province->id)>{{ $province->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-xl border-slate-300 focus:border-[#2E628D] focus:ring-[#2E628D]">
                    <option value="">All status</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption }}" @selected($status === $statusOption)>{{ $statusOption }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-[#2E628D] px-5 py-3 text-sm font-bold text-white hover:bg-[#244F73]">Filter</button>
                <a href="{{ route('tssd.tracking.project-transactions') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
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
                <table class="ppe-tracking-table min-w-max w-full text-sm">
                    <thead>
                        <tr>
                            <th rowspan="2" class="px-5 py-4 text-left">Delivery / Project Date</th>
                            <th rowspan="2" class="px-5 py-4 text-left">Provincial Office</th>
                            <th rowspan="2" class="px-5 py-4 text-left">Project</th>
                            <th rowspan="2" class="px-5 py-4 text-left">Source</th>
                            <th rowspan="2" class="px-5 py-4 text-left">Location</th>
                            <th rowspan="2" class="px-5 py-4 text-center">Beneficiaries</th>
                            <th rowspan="2" class="px-5 py-4 text-center">Status</th>
                            @foreach ($ppeHeaderGroups as $group)
                                @if ($group['grouped'])
                                    <th colspan="{{ $group['items']->count() + 1 }}" class="min-w-28 px-4 py-4 text-center">{{ $group['name'] }}</th>
                                @else
                                    <th rowspan="2" class="min-w-28 px-4 py-4 text-center">
                                        {{ $group['name'] }}
                                        @if ($group['items']->first()->label)
                                            <span class="block text-[10px] font-semibold uppercase tracking-wide text-white/80">{{ $group['items']->first()->label }}</span>
                                        @endif
                                    </th>
                                @endif
                            @endforeach
                            {{-- <th rowspan="2" class="px-5 py-4 text-center">Total PPE</th> --}}
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
                        @forelse ($transactions as $transaction)
                            @php
                                $allocation = $transaction->provinceDistribution;
                                $callOff = $allocation?->distributionBatch?->callOff;
                                $purchaseOrder = $allocation?->distributionBatch?->purchaseOrder;
                                $province = $transaction->province ?? $allocation?->province;
                                $normalizedStatus = strtolower(trim((string) ($transaction->status ?: 'Pending')));
                                $statusClass = str_contains($normalizedStatus, 'pending')
                                    ? 'bg-red-100 text-red-800 ring-red-200'
                                    : match ($normalizedStatus) {
                                        'completed', 'approved', 'designated' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
                                        'cancelled', 'rejected' => 'bg-slate-200 text-slate-700 ring-slate-300',
                                        'draft' => 'bg-slate-100 text-slate-700 ring-slate-200',
                                        default => 'bg-sky-100 text-sky-800 ring-sky-200',
                                    };
                            @endphp
                            <tr class="align-top hover:bg-[#F7FBFD]">
                                <td class="px-5 py-4 whitespace-nowrap font-semibold text-slate-700">{{ $transaction->designation_date?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-5 py-4 font-bold text-slate-900">{{ $province?->name ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-extrabold text-slate-900">{{ $transaction->project_code ?: $transaction->designation_number ?: '—' }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $transaction->project_title ?: $transaction->project_name ?: 'Untitled project' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-extrabold text-[#2E628D]">{{ $callOff?->call_off_number ?? '—' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">PO {{ $purchaseOrder?->po_number ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $transaction->location ?: '—' }}</td>
                                <td class="px-5 py-4 text-center font-bold">{{ number_format((int) $transaction->number_of_beneficiaries) }}</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">{{ $transaction->status ?: 'Pending' }}</span>
                                </td>
                                @foreach ($ppeHeaderGroups as $group)
                                    @if ($group['grouped'])
                                        @php
                                            $groupTotal = 0;
                                        @endphp
                                        @foreach ($group['items'] as $item)
                                            @php
                                                $quantity = (int) ($transaction->tracking_quantities[$item->id] ?? 0);
                                            @endphp
                                            @php
                                                $groupTotal += $quantity;
                                            @endphp
                                            <td class="px-4 py-4 text-center font-bold {{ $quantity <= 0 ? 'text-slate-400' : 'text-slate-900' }}">
                                                {{ number_format($quantity) }}
                                            </td>
                                        @endforeach
                                        <td class="bg-[#F2F8FB] px-4 py-4 text-center font-black text-[#2E628D]">{{ number_format($groupTotal) }}</td>
                                    @else
                                        @php
                                            $item = $group['items']->first();
                                        @endphp
                                        @php
                                            $quantity = (int) ($transaction->tracking_quantities[$item->id] ?? 0);
                                        @endphp
                                        <td class="px-4 py-4 text-center font-bold {{ $quantity <= 0 ? 'text-slate-400' : 'text-slate-900' }}">
                                            {{ number_format($quantity) }}
                                        </td>
                                    @endif
                                @endforeach
                                {{-- <td class="px-5 py-4 text-center text-base font-black text-[#2E628D]">{{ number_format((int) $transaction->tracking_total) }}</td> --}}
                            </tr>
                        @empty
                            <tr><td colspan="{{ $items->count() + $ppeHeaderGroups->where('grouped', true)->count() + 8 }}" class="px-6 py-14 text-center text-slate-500">No project distribution transactions matched the current filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($transactions->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">{{ $transactions->links() }}</div>
            @endif
        </section>
    </div>
</x-po_dashboard_layout>
