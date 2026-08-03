<x-po_dashboard_layout title="Call-Off Request Letters">
    <div class="mx-auto max-w-[1900px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#247BA0] to-[#55B7D9]"></div>

            <div class="flex flex-col gap-5 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#247BA0] ring-1 ring-[#B7D6E6]">
                            TSSD Unit
                        </span>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                            Automatically Generated
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-[#143A52] sm:text-3xl">
                        Call-Off Request Letters
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Every letter listed here was generated when TSSD saved a distribution. A pending record has already been submitted to the Supply Unit but does not yet have an official Call-Off Number.
                    </p>
                </div>

                <a href="{{ route('tssd.distributions.create') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-[#339DCB] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#247BA0]">
                    Create Distribution
                </a>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" action="{{ route('tssd.call-off-letters.index') }}"
                class="grid gap-3 border-b border-slate-200 p-5 md:grid-cols-[1fr_240px_auto]">
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Search Call-Off, NEFA, PO or supplier..."
                    class="rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]">

                <select name="status"
                    class="rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]">
                    <option value="">All status</option>
                    @foreach (['Pending Call-Off Number', 'Approved', 'Rejected'] as $statusOption)
                        <option value="{{ $statusOption }}" @selected($status === $statusOption)>
                            {{ $statusOption }}
                        </option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit"
                        class="rounded-xl bg-[#339DCB] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#247BA0]">
                        Apply
                    </button>

                    <a href="{{ route('tssd.call-off-letters.index') }}"
                        class="rounded-xl border border-[#B7D6E6] px-5 py-2.5 text-sm font-bold text-[#247BA0] transition hover:bg-[#F7FBFD]">
                        Reset
                    </a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1450px] divide-y divide-slate-200">
                    <thead class="bg-[#247BA0] text-xs font-bold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-5 py-4 text-left">Call-Off Number</th>
                            <th class="px-5 py-4 text-left">NEFA Number</th>
                            <th class="px-5 py-4 text-left">NEFA Project Title</th>
                            <th class="px-5 py-4 text-left">Distribution</th>
                            <th class="px-5 py-4 text-left">Purchase Order</th>
                            <th class="px-5 py-4 text-left">Supplier</th>
                            <th class="px-5 py-4 text-left">Submitted</th>
                            <th class="px-5 py-4 text-center">Status</th>
                            <th class="px-5 py-4 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse ($letterBatches as $batch)
                            @php
                                $callOff = $batch->callOff;
                                $purchaseOrder = $batch->purchaseOrder;
                                $supplier = $purchaseOrder?->supplier;
                                $currentTitle = $batch->call_off_letter_nefa_title
                                    ?: $callOff?->nefa_title
                                    ?: $defaultNefaTitle;

                                $statusLabel = $callOff?->status ?: 'Pending Call-Off Number';

                                $statusClasses = match (strtolower($statusLabel)) {
                                    'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                    'rejected' => 'bg-red-50 text-red-700 ring-red-200',
                                    default => 'bg-red-50 text-red-700 ring-red-200',
                                };
                            @endphp

                            <tr class="transition hover:bg-[#F7FBFD]">
                                <td class="px-5 py-4 font-bold text-slate-900">
                                    {{ $callOff?->call_off_number ?: 'Pending assignment' }}
                                </td>

                                <td class="px-5 py-4 font-semibold text-[#247BA0]">
                                    {{ $purchaseOrder?->nefa_number ?: '—' }}
                                </td>

                                <td class="max-w-[430px] px-5 py-4">
                                    <p class="line-clamp-3">{{ $currentTitle }}</p>
                                </td>

                                <td class="px-5 py-4">
                                    Batch #{{ $batch->id }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $purchaseOrder?->po_number ?: '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $supplier?->supplier_name ?: '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $batch->call_off_letter_submitted_at?->format('M d, Y h:i A')
                                        ?? $batch->created_at?->format('M d, Y h:i A')
                                        ?? '—' }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClasses }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('tssd.call-off-letters.edit', $batch) }}"
                                            class="rounded-xl border border-[#B7D6E6] px-4 py-2 text-xs font-bold text-[#247BA0] transition hover:bg-[#F7FBFD]">
                                            Settings
                                        </a>

                                        <a href="{{ route('tssd.call-off-letters.print', $batch) }}" target="_blank"
                                            class="rounded-xl bg-[#339DCB] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#247BA0]">
                                            View Letter
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-14 text-center text-sm text-slate-500">
                                    No generated Call-Off request letters were found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($letterBatches->hasPages())
                <div class="border-t border-slate-200 p-5">
                    {{ $letterBatches->links() }}
                </div>
            @endif
        </section>
    </div>
</x-po_dashboard_layout>
