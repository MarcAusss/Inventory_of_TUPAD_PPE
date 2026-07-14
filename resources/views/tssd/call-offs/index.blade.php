<x-po_dashboard_layout title="Call-Off Management">

    <div class="mx-auto max-w-[1900px] space-y-6">

        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#641D21] via-[#970C13] to-[#ED1B24]"></div>

            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-[#DF979B]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#970C13] ring-1 ring-[#DF979B]">
                            TSSD Unit
                        </span>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                            Call-Off Management
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        Call-Off Management
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Assign and monitor shared Call-Off Numbers for submitted TSSD distribution batches.
                    </p>
                </div>

                <a href="{{ route('tssd.call-offs.create') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-[#970C13] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#641D21]">
                    Assign Call-Off Number
                </a>
            </div>
        </section>

        @if(session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">
                    TSSD Call-Off records
                </p>

                <h2 class="mt-1 text-lg font-bold text-slate-950">
                    Call-Off Records
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Review the source Purchase Order, included provinces, assigned date, and approval status.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1250px] w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr class="text-xs font-bold uppercase tracking-wide text-slate-600">
                            <th class="px-6 py-4 text-left">No.</th>
                            <th class="px-6 py-4 text-left">Call-Off Number</th>
                            <th class="px-6 py-4 text-left">Purchase Order</th>
                            <th class="px-6 py-4 text-left">Supplier</th>
                            <th class="px-6 py-4 text-center">Provinces</th>
                            <th class="px-6 py-4 text-left">Assigned Date</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($callOffs as $callOff)
                            @php
                                $batch = $callOff->distributionBatch;
                                $purchaseOrder = $batch?->purchaseOrder;
                                $provinceCount = $batch?->provinceDistributions?->count() ?? 0;

                                $statusClasses = match($callOff->status) {
                                    'Approved' => 'bg-green-100 text-green-800 ring-green-200',
                                    'Rejected' => 'bg-red-100 text-red-800 ring-red-200',
                                    'Cancelled' => 'bg-slate-200 text-slate-700 ring-slate-300',
                                    'Completed' => 'bg-blue-100 text-blue-800 ring-blue-200',
                                    default => 'bg-amber-100 text-amber-800 ring-amber-200',
                                };
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-500">
                                    {{ $callOffs->firstItem() + $loop->index }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-5">
                                    <a href="{{ route('tssd.call-offs.show', $callOff) }}"
                                       class="font-bold text-[#641D21] hover:underline">
                                        {{ $callOff->call_off_number }}
                                    </a>

                                    <div class="mt-1 text-xs text-slate-400">
                                        Batch #{{ $batch?->id ?? 'N/A' }}
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 text-sm font-semibold text-slate-800">
                                    {{ $purchaseOrder?->po_number ?? '—' }}
                                </td>

                                <td class="min-w-56 px-6 py-5 text-sm text-slate-600">
                                    {{ $purchaseOrder?->supplier?->supplier_name ?? '—' }}
                                </td>

                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex min-w-9 justify-center rounded-full bg-[#DF979B]/20 px-3 py-1 text-xs font-bold text-[#970C13] ring-1 ring-[#DF979B]">
                                        {{ $provinceCount }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600">
                                    {{ $callOff->assigned_at?->format('M d, Y') ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 text-center">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClasses }}">
                                        {{ $callOff->status }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('tssd.call-offs.show', $callOff) }}"
                                           class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                            View
                                        </a>

                                        @if($callOff->status === 'Pending')
                                            <form action="{{ route('tssd.call-offs.destroy', $callOff) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Cancel this Call-Off? The record will remain for audit purposes.');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="inline-flex rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-bold text-red-700 transition hover:bg-red-100">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-14 text-center text-sm text-slate-500">
                                    No Call-Off records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($callOffs->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $callOffs->links() }}
                </div>
            @endif
        </section>

    </div>

</x-po_dashboard_layout>