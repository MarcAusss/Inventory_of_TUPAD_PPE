<x-po_dashboard_layout title="TSSD Distribution Summary">

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- Header --}}
        <section class="relative overflow-hidden rounded-3xl border border-[#B7D6E6] bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#247BA0] to-[#55B7D9]"></div>

            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span
                        class="inline-flex rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#247BA0] ring-1 ring-[#B7D6E6]">
                        Accounting · Read Only
                    </span>

                    <h1 class="mt-4 text-2xl font-bold text-slate-950 sm:text-3xl">
                        TSSD Distribution Summary
                    </h1>

                    <p class="mt-2 text-sm text-slate-600">
                        Read-only monitoring of provincial allocations created by the TSSD Unit.
                    </p>
                </div>

                <button type="button" onclick="window.print()"
                    class="inline-flex items-center justify-center rounded-xl border border-[#B7D6E6] bg-white px-5 py-3 text-sm font-bold text-[#247BA0] shadow-sm transition hover:bg-[#F7FBFD]">
                    Print Summary
                </button>
            </div>
        </section>

        {{-- Summary cards --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Total Distributions</p>
                <p class="mt-3 text-3xl font-extrabold text-[#143A52]">{{ number_format($totalDistributions) }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Total PPE Allocated</p>
                <p class="mt-3 text-3xl font-extrabold text-[#247BA0]">{{ number_format($totalPpe) }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Received</p>
                <p class="mt-3 text-3xl font-extrabold text-green-700">{{ number_format($receivedCount) }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">In Progress</p>
                <p class="mt-3 text-3xl font-extrabold text-amber-600">{{ number_format($pendingCount) }}</p>
            </div>
        </section>

        {{-- Filters and table --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" action="{{ route('accounting.distributions.index') }}"
                class="grid gap-3 border-b border-slate-200 p-5 md:grid-cols-4">

                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Search Call-Off, PO, supplier..."
                    class="rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]">

                <select name="province_id"
                    class="rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]">
                    <option value="">All provinces</option>

                    @foreach ($provinces as $province)
                        <option value="{{ $province->id }}" @selected((int) $provinceId === (int) $province->id)>
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]">
                    <option value="">All statuses</option>

                    @foreach (['Pending', 'Approved', 'For Delivery', 'Partially Received', 'Received', 'Cancelled'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 rounded-xl bg-[#339DCB] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#247BA0]">
                        Apply
                    </button>

                    <a href="{{ route('accounting.distributions.index') }}"
                        class="rounded-xl border border-[#B7D6E6] px-4 py-2.5 text-sm font-bold text-[#247BA0] transition hover:bg-[#F7FBFD]">
                        Reset
                    </a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1250px] divide-y divide-slate-200">
                    <thead class="bg-[#247BA0] text-xs font-bold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-5 py-4 text-left">Batch</th>
                            <th class="px-5 py-4 text-left">PO</th>
                            <th class="px-5 py-4 text-left">Call-Off</th>
                            <th class="px-5 py-4 text-left">Province</th>
                            <th class="px-5 py-4 text-left">Supplier</th>
                            <th class="px-5 py-4 text-left">Scheduled Delivery</th>
                            <th class="px-5 py-4 text-center">Total PPE</th>
                            <th class="px-5 py-4 text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse ($distributions as $distribution)
                            @php
                                $batch = $distribution->distributionBatch;

                                $statusClasses = match ($distribution->status) {
                                    'Received' => 'bg-green-100 text-green-800 ring-green-200',
                                    'Partially Received',
                                    'For Delivery',
                                    'Approved',
                                    'Pending'
                                        => 'bg-amber-100 text-amber-800 ring-amber-200',
                                    'Cancelled' => 'bg-red-100 text-red-800 ring-red-200',
                                    default => 'bg-slate-100 text-slate-700 ring-slate-200',
                                };
                            @endphp

                            <tr class="transition hover:bg-[#F7FBFD]">
                                <td class="px-5 py-4 font-bold text-slate-900">#{{ $batch?->id ?? '—' }}</td>
                                <td class="px-5 py-4">{{ $batch?->purchaseOrder?->po_number ?? '—' }}</td>
                                <td class="px-5 py-4 font-semibold text-[#247BA0]">
                                    {{ $batch?->callOff?->call_off_number ?? 'Pending' }}</td>
                                <td class="px-5 py-4">{{ $distribution->province?->name ?? '—' }}</td>
                                <td class="px-5 py-4">{{ $batch?->purchaseOrder?->supplier?->supplier_name ?? '—' }}
                                </td>
                                <td class="px-5 py-4">
                                    {{ $distribution->scheduled_delivery_date?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-5 py-4 text-center text-lg font-bold text-[#247BA0]">
                                    {{ number_format($distribution->items->sum('quantity')) }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClasses }}">
                                        {{ $distribution->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">
                                    No distribution records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($distributions->hasPages())
                <div class="border-t border-slate-200 p-5">
                    {{ $distributions->links() }}
                </div>
            @endif
        </section>
    </div>

    <style>
        @media print {

            aside,
            nav,
            form,
            button {
                display: none !important;
            }

            body {
                background: white !important;
            }

            main {
                margin: 0 !important;
                padding: 0 !important;
            }

            section {
                box-shadow: none !important;
            }
        }
    </style>

</x-po_dashboard_layout>
