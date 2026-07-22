<x-po_dashboard_layout title="Purchase Order Details">
    <div class="mx-auto max-w-[1900px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#2D94BE] to-[#339DCB]"></div>
            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#143A52] ring-1 ring-[#90C4DD]">Supply
                            Unit</span>
                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">Purchase
                            Order Details</span>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        {{ $purchaseOrder->po_number }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Review the Purchase Order information,
                        supplier details, ordered PPE quantities, pricing, and supporting document.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('supply.purchase-orders.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Back
                        to Purchase Orders</a>
                    <a href="{{ route('supply.purchase-orders.edit', $purchaseOrder) }}"
                        class="inline-flex items-center justify-center rounded-xl bg-[#339DCB] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#2D94BE]">Edit
                        Purchase Order</a>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">PO Number</p>
                <p class="mt-3 text-xl font-bold text-[#143A52]">{{ $purchaseOrder->po_number }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">PO Date</p>
                <p class="mt-3 text-xl font-bold text-slate-900">
                    {{ optional($purchaseOrder->po_date)->format('M d, Y') ?? '—' }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Ordered PPE Quantity</p>
                <p class="mt-3 text-3xl font-bold text-[#2D94BE]">
                    {{ number_format($purchaseOrder->items->sum('quantity')) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Grand Total</p>
                <p class="mt-3 text-2xl font-bold text-[#339DCB]">₱{{ number_format($purchaseOrder->total_amount, 2) }}
                </p>
            </article>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">Procurement information</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">Purchase Order Information</h2>
                <p class="mt-1 text-sm text-slate-500">Main supplier and reference information recorded for this order.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3 sm:p-7">
                @foreach ([['PO Number', $purchaseOrder->po_number], ['PO Date', optional($purchaseOrder->po_date)->format('F d, Y') ?? '—'], ['NEFA Number', $purchaseOrder->nefa_number ?: '—'], ['Supplier', $purchaseOrder->supplier?->supplier_name ?? '—']] as [$label, $value])
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $label }}</p>
                        <p class="mt-2 font-semibold text-slate-900">{{ $value }}</p>
                    </div>
                @endforeach
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Grand Total</p>
                    <p class="mt-2 text-2xl font-bold text-[#2D94BE]">
                        ₱{{ number_format($purchaseOrder->total_amount, 2) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Remarks</p>
                    <p class="mt-2 text-sm leading-6 text-slate-700">
                        {{ $purchaseOrder->remarks ?: 'No remarks provided.' }}</p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">Ordered inventory</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">PPE Items</h2>
                <p class="mt-1 text-sm text-slate-500">Complete quantity and pricing breakdown for every PPE item in
                    this Purchase Order.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[900px] w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr class="text-xs font-bold uppercase tracking-wide text-slate-600">
                            <th class="px-6 py-4 text-left">No.</th>
                            <th class="px-6 py-4 text-left">Description</th>
                            <th class="px-6 py-4 text-center">Size / Label</th>
                            <th class="px-6 py-4 text-center">Quantity</th>
                            <th class="px-6 py-4 text-right">Unit Cost</th>
                            <th class="px-6 py-4 text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($purchaseOrder->items as $poItem)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-5 text-sm text-slate-500">{{ $loop->iteration }}</td>
                                <td class="px-6 py-5 font-semibold text-slate-900">
                                    {{ $poItem->item?->item_name ?? '—' }}</td>
                                <td class="px-6 py-5 text-center">
                                    @if ($poItem->item?->label)
                                        <span
                                        class="inline-flex rounded-full bg-[#2D94BE]/20 px-3 py-1 text-xs font-bold text-[#2D94BE] ring-1 ring-[#2D94BE]">{{ $poItem->item->label }}</span>@else<span
                                            class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-center text-lg font-bold text-slate-900">
                                    {{ number_format($poItem->quantity) }}</td>
                                <td class="px-6 py-5 text-right text-sm font-semibold text-slate-700">
                                    ₱{{ number_format($poItem->unit_cost, 2) }}</td>
                                <td class="px-6 py-5 text-right text-base font-bold text-[#2D94BE]">
                                    ₱{{ number_format($poItem->quantity * $poItem->unit_cost, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">No PPE items
                                    were added to this Purchase Order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-100">
                        <tr>
                            <td colspan="5"
                                class="px-6 py-5 text-right text-sm font-bold uppercase tracking-wide text-slate-700">
                                Grand Total</td>
                            <td class="px-6 py-5 text-right text-2xl font-bold text-[#2D94BE]">
                                ₱{{ number_format($purchaseOrder->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">File attachment</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">Supporting Document</h2>
                <p class="mt-1 text-sm text-slate-500">Open the uploaded Purchase Order document for verification and
                    reference.</p>
            </div>
            <div class="p-6 sm:p-7">
                @if ($purchaseOrder->document)
                    <div
                        class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-bold text-slate-900">Purchase Order Document</p>
                            <p class="mt-1 text-sm text-slate-500">The supporting document is available for viewing.</p>
                        </div><a href="{{ route('documents.purchase-orders', $purchaseOrder) }}" target="_blank" rel="noopener"
                            class="inline-flex items-center justify-center rounded-xl bg-[#339DCB] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#2D94BE]">View
                            Document</a>
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                        <p class="font-semibold text-slate-700">No supporting document uploaded</p>
                        <p class="mt-1 text-sm text-slate-500">Edit this Purchase Order to attach its supporting
                            document.</p>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-po_dashboard_layout>
