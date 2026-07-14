<x-po_dashboard_layout title="TSSD Distribution">

    <div class="mx-auto max-w-[1900px] space-y-6">

        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#641D21] via-[#970C13] to-[#ED1B24]"></div>

            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#DF979B]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#970C13] ring-1 ring-[#DF979B]">
                            TSSD Unit
                        </span>

                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                            Distribution
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        TSSD Distribution
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Select a Purchase Order to review purchased PPE, remaining quantities, and provincial
                        distributions.
                    </p>
                </div>

                @if (Route::has('tssd.distributions.create'))
                    <a href="{{ route('tssd.distributions.create') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-[#970C13] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#641D21]">
                        Create Distribution
                    </a>
                @endif
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">
                    Available Purchase Orders
                </p>

                <h2 class="mt-1 text-lg font-bold text-slate-950">
                    Purchase Order Distribution Records
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Open a Purchase Order to inspect its purchased, distributed, and remaining PPE quantities.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[950px] w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr class="text-xs font-bold uppercase tracking-wide text-slate-600">
                            <th class="px-6 py-4 text-left">No.</th>
                            <th class="px-6 py-4 text-left">PO Number</th>
                            <th class="px-6 py-4 text-left">PO Date</th>
                            <th class="px-6 py-4 text-left">Supplier</th>
                            <th class="px-6 py-4 text-right">Total Amount</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($purchaseOrders as $po)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-5 text-sm text-slate-500">
                                    {{ $purchaseOrders->firstItem() + $loop->index }}
                                </td>

                                <td class="px-6 py-5">
                                    <a href="{{ route('tssd.distributions.show', $po) }}"
                                        class="font-bold text-[#641D21] hover:underline">
                                        {{ $po->po_number }}
                                    </a>

                                    @if ($po->nefa_number)
                                        <div class="mt-1 text-xs text-slate-400">
                                            NEFA: {{ $po->nefa_number }}
                                        </div>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600">
                                    {{ optional($po->po_date)->format('M d, Y') ?? '—' }}
                                </td>

                                <td class="min-w-56 px-6 py-5 text-sm text-slate-600">
                                    {{ $po->supplier?->supplier_name ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 text-right text-base font-bold text-[#970C13]">
                                    ₱{{ number_format($po->total_amount, 2) }}
                                </td>

                                <td class="px-6 py-5 text-center">
                                    <a href="{{ route('tssd.distributions.show', $po) }}"
                                        class="inline-flex rounded-lg bg-[#970C13] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#641D21]">
                                        View Distribution
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">
                                    No Purchase Orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($purchaseOrders->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $purchaseOrders->links() }}
                </div>
            @endif
        </section>

    </div>

</x-po_dashboard_layout>
