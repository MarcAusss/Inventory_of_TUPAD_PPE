<x-po_dashboard_layout title="Purchase Orders">

    <div class="mx-auto max-w-[1900px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#641D21] via-[#970C13] to-[#ED1B24]"></div>
            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-[#DF979B]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#970C13] ring-1 ring-[#DF979B]">Supply Unit</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">Purchase Orders</span>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Purchase Order Management</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Create, review, update, and manage Purchase Orders used as the source of PPE inventory and provincial Call-Off allocations.</p>
                </div>
                <a href="{{ route('supply.purchase-orders.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#970C13] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#641D21]">+ New Purchase Order</a>
            </div>
        </section>

        @if(session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800 shadow-sm">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm">{{ session('error') }}</div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('supply.purchase-orders.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex-1">
                    <label for="search" class="sr-only">Search purchase orders</label>
                    <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Search PO number, supplier, NEFA number, or status..." class="w-full rounded-xl border-slate-300 focus:border-[#970C13] focus:ring-[#970C13]">
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#970C13] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#641D21]">Search</button>
                @if($search !== '')
                    <a href="{{ route('supply.purchase-orders.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Reset</a>
                @endif
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">Supply procurement records</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">Purchase Order Records</h2>
                <p class="mt-1 text-sm text-slate-500">Review the supplier, order date, total amount, current status, and available actions for each Purchase Order.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1100px] w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr class="text-xs font-bold uppercase tracking-wide text-slate-600">
                            <th class="px-6 py-4 text-left">No.</th>
                            <th class="px-6 py-4 text-left">PO Number</th>
                            <th class="px-6 py-4 text-left">Supplier</th>
                            <th class="px-6 py-4 text-left">PO Date</th>
                            <th class="px-6 py-4 text-right">Total Amount</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($purchaseOrders as $po)
                            @php
                                $statusClass = match(strtolower((string) $po->status)) {
                                    'approved', 'completed' => 'bg-green-100 text-green-800 ring-green-200',
                                    'pending' => 'bg-amber-100 text-amber-800 ring-amber-200',
                                    'draft' => 'bg-slate-100 text-slate-700 ring-slate-200',
                                    'cancelled' => 'bg-red-100 text-red-800 ring-red-200',
                                    default => 'bg-blue-100 text-blue-800 ring-blue-200',
                                };
                            @endphp
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-500">{{ $purchaseOrders->firstItem() + $loop->index }}</td>
                                <td class="whitespace-nowrap px-6 py-5">
                                    <a href="{{ route('supply.purchase-orders.show', $po) }}" class="font-semibold text-[#641D21] hover:underline">{{ $po->po_number }}</a>
                                    @if($po->nefa_number)<div class="mt-1 text-xs text-slate-400">NEFA: {{ $po->nefa_number }}</div>@endif
                                </td>
                                <td class="min-w-56 px-6 py-5 text-sm text-slate-600">{{ $po->supplier?->supplier_name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600">{{ optional($po->po_date)->format('M d, Y') ?? '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-5 text-right text-base font-bold text-[#970C13]">₱{{ number_format($po->total_amount, 2) }}</td>
                                <td class="whitespace-nowrap px-6 py-5 text-center"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">{{ $po->status }}</span></td>
                                <td class="whitespace-nowrap px-6 py-5 text-center">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        <a href="{{ route('supply.purchase-orders.show', $po) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">View</a>
                                        <a href="{{ route('supply.purchase-orders.edit', $po) }}" class="inline-flex items-center justify-center rounded-lg bg-[#970C13] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#641D21]">Edit</a>
                                        <form action="{{ route('supply.purchase-orders.destroy', $po) }}" method="POST" class="inline" onsubmit="return confirm('Delete this purchase order?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-bold text-red-700 transition hover:bg-red-100">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-14 text-center"><p class="font-semibold text-slate-700">No Purchase Orders found</p><p class="mt-1 text-sm text-slate-500">Create a Purchase Order or adjust your search criteria.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchaseOrders->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">{{ $purchaseOrders->links() }}</div>
            @endif
        </section>
    </div>

</x-po_dashboard_layout>