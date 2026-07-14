<x-po_dashboard_layout title="Project PPE Designations">

    @php
        $itemQuantity = function ($designation, int $itemId): int {
            return (int) $designation->items->where('item_id', $itemId)->sum('quantity');
        };
    @endphp

    <div class="mx-auto max-w-[2200px] space-y-6">

        {{-- Header --}}
        <section
            class="relative overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm">
            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b from-[#641D21]
                       via-[#970C13] to-[#ED1B24]">
            </div>

            <div
                class="flex flex-col gap-6 px-6 py-7
                       sm:px-8 lg:flex-row
                       lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#DF979B]/20
                                   px-3 py-1 text-xs font-bold
                                   uppercase tracking-[0.16em]
                                   text-[#970C13]
                                   ring-1 ring-[#DF979B]">
                            Provincial Office
                        </span>

                        <span
                            class="rounded-full bg-slate-100
                                   px-3 py-1 text-xs font-semibold
                                   text-slate-700 ring-1
                                   ring-slate-200">
                            {{ auth()->user()->provinceName() }}
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold
                               tracking-tight text-slate-950
                               sm:text-3xl">
                        Project PPE Designations
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm
                               leading-6 text-slate-600">
                        Review PPE quantities distributed to each
                        provincial project from an exact Delivery
                        Receipt inventory source.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('provincial.project-designations.print-all', request()->only('search')) }}"
                        target="_blank" rel="noopener"
                        class="inline-flex items-center justify-center
                               rounded-xl bg-slate-900 px-5 py-3
                               text-sm font-bold text-white transition
                               hover:bg-slate-800">
                        Print Current Results
                    </a>

                    <a href="{{ route('provincial.project-designations.create') }}"
                        class="inline-flex items-center justify-center
                               rounded-xl bg-[#970C13] px-5 py-3
                               text-sm font-bold text-white transition
                               hover:bg-[#641D21]">
                        Create Project Designation
                    </a>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div
                class="rounded-2xl border border-green-200
                       bg-green-50 px-5 py-4
                       text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Search --}}
        <section class="rounded-3xl border border-slate-200
                   bg-white p-5 shadow-sm sm:p-6">
            <form action="{{ route('provincial.project-designations.index') }}"
                method="GET" class="flex flex-col gap-4
                       lg:flex-row lg:items-end">
                <div class="flex-1">
                    <label for="search"
                        class="mb-2 block text-xs font-bold
                               uppercase tracking-wider
                               text-slate-500">
                        Search Project Records
                    </label>

                    <input type="search" id="search" name="search" value="{{ $search }}"
                        placeholder="Project code, title, location, supplier, DR, or Call-Off..."
                        class="w-full rounded-xl border-slate-300
                               focus:border-[#970C13]
                               focus:ring-[#970C13]">
                </div>

                <button type="submit"
                    class="rounded-xl bg-[#970C13]
                           px-6 py-2.5 text-sm font-bold
                           text-white transition
                           hover:bg-[#641D21]">
                    Search
                </button>

                @if ($search)
                    <a href="{{ route('provincial.project-designations.index') }}"
                        class="rounded-xl border border-slate-300
                               bg-white px-5 py-2.5 text-sm
                               font-bold text-slate-700
                               hover:bg-slate-50">
                        Clear
                    </a>
                @endif
            </form>
        </section>

        {{-- Table --}}
        <section class="overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b
                       border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase
                           tracking-[0.16em] text-[#970C13]">
                    Project Distribution Records
                </p>

                <h2 class="text-xl font-bold text-slate-950">
                    PPE Project Designation History
                </h2>

                <p class="text-sm text-slate-500">
                    Every project record identifies its exact Delivery
                    Receipt and parent Call-Off inventory source.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[2500px] w-full
                           border-separate border-spacing-0">
                    <thead class="bg-slate-900 text-white">
                        <tr class="text-xs font-bold uppercase
                                   tracking-wide">
                            <th class="px-4 py-4 text-center">No.</th>
                            <th class="px-4 py-4 text-left">Project Code</th>
                            <th class="px-4 py-4 text-left">Project Title</th>
                            <th class="px-4 py-4 text-left">Location</th>
                            <th class="px-4 py-4 text-center">Beneficiaries</th>
                            <th class="px-4 py-4 text-center">Workdays</th>
                            <th class="px-4 py-4 text-left">Supplier</th>
                            <th class="px-4 py-4 text-left">Delivery Receipt</th>

                            <th class="bg-[#970C13]
                                       px-4 py-4 text-left">
                                Call-Off Number
                            </th>

                            <th class="px-4 py-4 text-center">Long Sleeve M</th>
                            <th class="px-4 py-4 text-center">Long Sleeve L</th>

                            <th class="bg-[#970C13]/90
                                       px-4 py-4 text-center">
                                Total Long Sleeve
                            </th>

                            <th class="px-4 py-4 text-center">Bucket Hat</th>
                            <th class="px-4 py-4 text-center">Boots US9</th>
                            <th class="px-4 py-4 text-center">Boots US10</th>

                            <th class="bg-[#970C13]/90
                                       px-4 py-4 text-center">
                                Total Rubber Boots
                            </th>

                            <th class="px-4 py-4 text-center">Gloves</th>
                            <th class="px-4 py-4 text-center">Mask</th>

                            <th class="bg-[#641D21]
                                       px-4 py-4 text-center">
                                Total PPE
                            </th>

                            <th class="px-4 py-4 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($designations as $designation)
                            @php
                                $allocation = $designation->provinceDistribution;

                                $batch = $allocation?->distributionBatch;

                                $callOff = $batch?->callOff;

                                $purchaseOrder = $batch?->purchaseOrder;

                                $supplier = $purchaseOrder?->supplier;

                                $receipt = $designation->deliveryReceipt;

                                $longSleeveMedium = $itemQuantity($designation, 1);

                                $longSleeveLarge = $itemQuantity($designation, 2);

                                $bucketHat = $itemQuantity($designation, 3);

                                $rubberBootsUs9 = $itemQuantity($designation, 4);

                                $rubberBootsUs10 = $itemQuantity($designation, 5);

                                $gloves = $itemQuantity($designation, 6);

                                $mask = $itemQuantity($designation, 7);

                                $totalLongSleeve = $longSleeveMedium + $longSleeveLarge;

                                $totalRubberBoots = $rubberBootsUs9 + $rubberBootsUs10;

                                $totalPpe = $totalLongSleeve + $bucketHat + $totalRubberBoots + $gloves + $mask;
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td
                                    class="border-b border-slate-200
                                           px-4 py-4 text-center
                                           text-slate-500">
                                    {{ $designations->firstItem() + $loop->index }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-4 py-4 font-bold
                                           text-slate-900">
                                    {{ $designation->project_code }}
                                </td>

                                <td
                                    class="min-w-64 border-b
                                           border-slate-200 px-4 py-4
                                           font-medium text-slate-900">
                                    {{ $designation->project_title }}
                                </td>

                                <td
                                    class="min-w-52 border-b
                                           border-slate-200 px-4 py-4
                                           text-slate-600">
                                    {{ $designation->location }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-4 py-4 text-center
                                           font-bold">
                                    {{ number_format($designation->number_of_beneficiaries) }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-4 py-4 text-center">
                                    {{ number_format($designation->number_of_days) }}
                                </td>

                                <td
                                    class="min-w-52 border-b
                                           border-slate-200 px-4 py-4">
                                    {{ $supplier?->supplier_name ?? '—' }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-4 py-4 font-semibold">
                                    {{ $receipt?->dr_number ?? '—' }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           bg-[#DF979B]/10 px-4 py-4
                                           font-bold text-[#970C13]">
                                    {{ $callOff?->call_off_number ?? '—' }}
                                </td>

                                @foreach ([$longSleeveMedium, $longSleeveLarge] as $quantity)
                                    <td
                                        class="border-b border-slate-200
                                               px-4 py-4 text-center">
                                        {{ number_format($quantity) }}
                                    </td>
                                @endforeach

                                <td
                                    class="border-b border-slate-200
                                           bg-[#DF979B]/10 px-4 py-4
                                           text-center font-bold
                                           text-[#970C13]">
                                    {{ number_format($totalLongSleeve) }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-4 py-4 text-center">
                                    {{ number_format($bucketHat) }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-4 py-4 text-center">
                                    {{ number_format($rubberBootsUs9) }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-4 py-4 text-center">
                                    {{ number_format($rubberBootsUs10) }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           bg-[#DF979B]/10 px-4 py-4
                                           text-center font-bold
                                           text-[#970C13]">
                                    {{ number_format($totalRubberBoots) }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-4 py-4 text-center">
                                    {{ number_format($gloves) }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-4 py-4 text-center">
                                    {{ number_format($mask) }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           bg-slate-100 px-4 py-4
                                           text-center font-bold
                                           text-[#641D21]">
                                    {{ number_format($totalPpe) }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-4 py-4 text-center">
                                    <div
                                        class="flex items-center
                                               justify-center gap-2">
                                        <a href="{{ route('provincial.project-designations.show', $designation) }}"
                                            class="rounded-xl bg-slate-900
                                                   px-4 py-2 text-sm
                                                   font-bold text-white
                                                   hover:bg-[#970C13]">
                                            View
                                        </a>

                                        <a href="{{ route('provincial.project-designations.print-one', $designation) }}"
                                            target="_blank" rel="noopener"
                                            class="rounded-xl border
                                                   border-[#970C13]
                                                   bg-white px-4 py-2
                                                   text-sm font-bold
                                                   text-[#970C13]
                                                   hover:bg-[#DF979B]/10">
                                            Print
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="20"
                                    class="px-6 py-16 text-center
                                           text-slate-500">
                                    No completed project PPE
                                    designations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($designations->hasPages())
                <div class="border-t border-slate-200
                           px-6 py-4">
                    {{ $designations->links() }}
                </div>
            @endif
        </section>
    </div>

</x-po_dashboard_layout>
