<x-po_dashboard_layout title="Provincial Inventory Details">

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
                            Provincial Inventory
                        </span>
                        <span
                            class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                            Available
                        </span>
                    </div>
                    <h1 class="mt-4 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                        Delivery Receipt {{ $receipt->dr_number }}
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        Review remaining PPE quantities and designate supplies to projects.
                    </p>
                </div>

                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                    <a href="{{ route('provincial.inventory.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-[#7DD3FC] hover:bg-sky-50 hover:text-[#075985]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                        </svg>
                        Back
                    </a>
                    <a href="{{ route('provincial.inventory.designate', $receipt->id) }}"
                        class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#0EA5E9] px-5 py-3 text-sm font-bold text-white shadow-sm shadow-sky-600/20 transition hover:-translate-y-0.5 hover:shadow-md">
                        Designate Supplies
                    </a>
                </div>
            </div>
        </section>

        {{-- Receipt Summary --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5 sm:px-7">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-[#0284C7] ring-1 ring-inset ring-sky-200">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75Zm2.25 4.5h7.5m-7.5 4h7.5m-7.5 4h4.5" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900">Receipt Information</h2>
                    <p class="mt-0.5 text-sm text-slate-500">Source details for this inventory record.</p>
                </div>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                <div class="border-b border-slate-200 p-6 sm:border-r lg:border-b-0">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Delivery Receipt</dt>
                    <dd class="mt-2 font-black text-[#075985]">{{ $receipt->dr_number }}</dd>
                </div>
                <div class="border-b border-slate-200 p-6 lg:border-b-0 lg:border-r">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Purchase Order</dt>
                    <dd class="mt-2 font-bold text-slate-900">{{ $receipt->purchaseOrder->po_number }}</dd>
                </div>
                <div class="border-b border-slate-200 p-6 sm:border-b-0 sm:border-r">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Supplier</dt>
                    <dd class="mt-2 font-bold text-slate-900">{{ $receipt->purchaseOrder->supplier->supplier_name }}</dd>
                </div>
                <div class="p-6">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Province</dt>
                    <dd class="mt-2 font-bold text-slate-900">{{ $receipt->province->province_name }}</dd>
                </div>
            </dl>
        </section>

        {{-- Remaining Inventory --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Remaining PPE Inventory</h2>
                    <p class="mt-1 text-sm text-slate-500">Available quantities that can be assigned to a project.</p>
                </div>
                <span
                    class="inline-flex w-fit rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-[#075985] ring-1 ring-inset ring-sky-200">
                    {{ $receipt->items->count() }} item(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#0EA5E9] text-xs font-bold uppercase tracking-[0.08em] text-white">
                            <th scope="col" class="px-6 py-4 text-left">Item</th>
                            <th scope="col" class="px-6 py-4 text-center">Remaining Quantity</th>
                            <th scope="col" class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($receipt->items as $item)
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
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <a href="{{ route('provincial.inventory.designate', $receipt->id) }}"
                                        class="inline-flex items-center justify-center rounded-lg bg-[#0284C7] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#075985]">
                                        Designate
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

</x-po_dashboard_layout>