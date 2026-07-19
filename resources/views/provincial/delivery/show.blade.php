<x-po_dashboard_layout title="Delivery Details">

    <div class="mx-auto max-w-6xl space-y-6">

        {{-- Page Header --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#075985] via-[#0284C7] to-[#38BDF8]"></div>
            <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-[#7DD3FC]/20 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#7DD3FC]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#075985] ring-1 ring-inset ring-[#7DD3FC]">
                            Provincial Office
                        </span>
                        @if ($receipt)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Received
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                Pending
                            </span>
                        @endif
                    </div>
                    <h1 class="mt-4 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Delivery Details</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        Review the purchase order information and allocated PPE quantities.
                    </p>
                </div>

                <a href="{{ route('provincial.deliveries.index') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-[#7DD3FC] hover:bg-sky-50 hover:text-[#075985] sm:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                    </svg>
                    Back to Deliveries
                </a>
            </div>
        </section>

        {{-- Delivery Summary --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5 sm:px-7">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-[#0284C7] ring-1 ring-inset ring-sky-200">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 7.5 12 3l9 4.5M3 7.5l9 4.5m-9-4.5V17l9 4.5m0-9.5 9-4.5M12 12v9.5m9-14V17l-9 4.5" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900">Delivery Information</h2>
                    <p class="mt-0.5 text-sm text-slate-500">Reference details for this provincial allocation.</p>
                </div>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-3">
                <div class="border-b border-slate-200 p-6 sm:border-b-0 sm:border-r">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Purchase Order</dt>
                    <dd class="mt-2 font-black text-[#075985]">{{ $distribution->purchaseOrder->po_number }}</dd>
                </div>
                <div class="border-b border-slate-200 p-6 sm:border-b-0 sm:border-r">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Supplier</dt>
                    <dd class="mt-2 font-bold text-slate-900">{{ $distribution->purchaseOrder->supplier->supplier_name }}</dd>
                </div>
                <div class="p-6">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Province</dt>
                    <dd class="mt-2 font-bold text-slate-900">{{ $distribution->province->province_name }}</dd>
                </div>
            </dl>
        </section>

        {{-- Allocated Items --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Allocated PPE Items</h2>
                    <p class="mt-1 text-sm text-slate-500">Items included in this delivery.</p>
                </div>
                <span
                    class="inline-flex w-fit rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-[#075985] ring-1 ring-inset ring-sky-200">
                    {{ $items->count() }} item(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#0EA5E9] text-xs font-bold uppercase tracking-[0.08em] text-white">
                            <th scope="col" class="px-6 py-4 text-left">Item</th>
                            <th scope="col" class="px-6 py-4 text-center">Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($items as $item)
                            <tr class="transition-colors hover:bg-sky-50/60">
                                <td class="min-w-[260px] px-6 py-4">
                                    <p class="font-bold text-slate-900">{{ $item->item->item_name }}</p>
                                    @if ($item->item->label)
                                        <p class="mt-1 text-xs text-slate-500">{{ $item->item->label }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <span
                                        class="inline-flex min-w-14 justify-center rounded-lg bg-sky-50 px-3 py-1.5 text-sm font-black text-[#075985] ring-1 ring-inset ring-sky-200">
                                        {{ number_format($item->quantity) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Actions --}}
        <section class="rounded-3xl border border-slate-200 bg-white px-6 py-5 shadow-sm sm:px-7">
            @if (!$receipt)
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-black text-slate-900">Delivery awaiting confirmation</p>
                        <p class="mt-1 text-sm text-slate-500">Record the delivery receipt and actual quantities received.</p>
                    </div>
                    <a href="{{ route('provincial.deliveries.receive', $distribution->purchase_order_id) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#0EA5E9] px-6 py-3 text-sm font-bold text-white shadow-sm shadow-sky-600/20 transition hover:-translate-y-0.5 hover:shadow-md">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        </svg>
                        Receive Delivery
                    </a>
                </div>
            @else
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-black text-slate-900">Delivery Received</p>
                            <p class="mt-0.5 text-sm text-slate-500">The received PPE is available for inventory management.</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('provincial.inventory.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-5 py-3 text-sm font-bold text-[#075985] transition hover:bg-sky-100">
                            View Inventory
                        </a>
                        <a href="{{ route('provincial.inventory.designate', $receipt->id) }}"
                            class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#0EA5E9] px-5 py-3 text-sm font-bold text-white shadow-sm shadow-sky-600/20 transition hover:-translate-y-0.5 hover:shadow-md">
                            Designate Supplies
                        </a>
                    </div>
                </div>
            @endif
        </section>
    </div>

</x-po_dashboard_layout>