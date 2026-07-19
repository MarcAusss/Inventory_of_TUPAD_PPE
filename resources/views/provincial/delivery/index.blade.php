<x-po_dashboard_layout title="PPE Deliveries">

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- Page Header --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#075985] via-[#0284C7] to-[#38BDF8]"></div>
            <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-[#7DD3FC]/20 blur-3xl"></div>

            <div class="relative flex flex-col gap-5 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#7DD3FC]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#075985] ring-1 ring-inset ring-[#7DD3FC]">
                            Provincial Office
                        </span>
                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                            Receiving
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">PPE Deliveries</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        Review incoming PPE allocations and record completed deliveries for your provincial office.
                    </p>
                </div>

                <div
                    class="flex w-full items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50/80 px-5 py-4 sm:w-auto sm:min-w-[230px]">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#075985] to-[#38BDF8] text-white shadow-sm shadow-sky-600/20">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 7.5 12 3l9 4.5M3 7.5l9 4.5m-9-4.5V17l9 4.5m0-9.5 9-4.5M12 12v9.5m9-14V17l-9 4.5" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Deliveries</p>
                        <p class="mt-1 text-2xl font-black text-[#0284C7]">{{ number_format($deliveries->count()) }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Feedback Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800"
                role="status">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>
                </div>
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800"
                role="alert">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-100">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="font-semibold">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Delivery Table --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-5 sm:px-7">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Delivery Records</h2>
                    <p class="mt-1 text-sm text-slate-500">Incoming allocations assigned to your province.</p>
                </div>
                <span
                    class="hidden rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-[#075985] ring-1 ring-inset ring-sky-200 sm:inline-flex">
                    {{ number_format($deliveries->count()) }} record(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#0EA5E9] text-xs font-bold uppercase tracking-[0.08em] text-white">
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-left">PO Number</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-left">Supplier</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-center">Delivery Date</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-center">Status</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($deliveries as $delivery)
                            <tr class="transition-colors hover:bg-sky-50/60">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="font-black text-[#075985]">{{ $delivery->purchaseOrder->po_number }}</span>
                                </td>

                                <td class="min-w-[240px] px-6 py-4 text-sm font-semibold text-slate-800">
                                    {{ $delivery->purchaseOrder->supplier->supplier_name }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-slate-600">
                                    {{ $delivery->items->first()?->delivery_date ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    @if ($delivery->receipt)
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
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <a href="{{ route('provincial.deliveries.show', $delivery->purchase_order_id) }}"
                                        class="inline-flex items-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-3.5 py-2 text-xs font-bold text-[#075985] transition hover:border-[#7DD3FC] hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-[#0284C7] focus:ring-offset-2">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.04 12.32a1 1 0 0 1 0-.64C3.42 7.51 7.35 4.5 12 4.5s8.58 3.01 9.96 7.18a1 1 0 0 1 0 .64C20.58 16.49 16.65 19.5 12 19.5S3.42 16.49 2.04 12.32Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        View Delivery
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 7.5 12 3l9 4.5M3 7.5l9 4.5m-9-4.5V17l9 4.5m0-9.5 9-4.5M12 12v9.5m9-14V17l-9 4.5" />
                                        </svg>
                                    </div>
                                    <p class="mt-4 font-bold text-slate-700">No deliveries available</p>
                                    <p class="mt-1 text-sm text-slate-500">New provincial allocations will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

</x-po_dashboard_layout>