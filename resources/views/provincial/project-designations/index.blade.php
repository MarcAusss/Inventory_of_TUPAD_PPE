<x-po_dashboard_layout title="Project PPE Designations">

    <div class="mx-auto max-w-[1800px] space-y-6">

        {{-- ============================================================
            PAGE HEADER
        ============================================================ --}}
        <section
            class="relative overflow-hidden rounded-3xl border
                   border-slate-200 bg-white shadow-sm"
        >
            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b from-[#641D21]
                       via-[#970C13] to-[#ED1B24]"
            ></div>

            <div
                class="flex flex-col gap-6 px-6 py-7
                       sm:px-8 lg:flex-row
                       lg:items-center lg:justify-between"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#DF979B]/20
                                   px-3 py-1 text-xs font-bold
                                   uppercase tracking-[0.16em]
                                   text-[#970C13]
                                   ring-1 ring-[#DF979B]"
                        >
                            Provincial Office
                        </span>

                        <span
                            class="rounded-full bg-slate-100
                                   px-3 py-1 text-xs font-semibold
                                   text-slate-700 ring-1
                                   ring-slate-200"
                        >
                            {{ auth()->user()->provinceName() }}
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold
                               tracking-tight text-slate-950
                               sm:text-3xl"
                    >
                        Project PPE Designations
                    </h1>

                    <p
                        class="mt-2 max-w-3xl text-sm
                               leading-6 text-slate-600"
                    >
                        Track PPE distributed from individual
                        Call-Off allocations to provincial projects.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route(
                            'provincial.current-inventory.index'
                        ) }}"
                        class="inline-flex items-center justify-center
                               rounded-xl border border-slate-300
                               bg-white px-5 py-3 text-sm font-bold
                               text-slate-700 transition
                               hover:bg-slate-50"
                    >
                        Current Inventory
                    </a>

                    <a
                        href="{{ route(
                            'provincial.call-off-inventory.index'
                        ) }}"
                        class="inline-flex items-center justify-center
                               rounded-xl border border-[#970C13]
                               bg-white px-5 py-3 text-sm font-bold
                               text-[#970C13] transition
                               hover:bg-[#DF979B]/10"
                    >
                        Call-Off Inventory
                    </a>

                    <a
                        href="{{ route(
                            'provincial.project-designations.create'
                        ) }}"
                        class="inline-flex items-center justify-center
                               rounded-xl bg-[#970C13] px-5 py-3
                               text-sm font-bold text-white
                               shadow-sm transition
                               hover:bg-[#641D21]"
                    >
                        Create Project Designation
                    </a>
                </div>
            </div>
        </section>

        {{-- ============================================================
            FLASH MESSAGES
        ============================================================ --}}
        @if(session('success'))
            <div
                class="rounded-2xl border border-green-200
                       bg-green-50 px-5 py-4
                       text-sm font-semibold text-green-800"
            >
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div
                class="rounded-2xl border border-red-200
                       bg-red-50 px-5 py-4
                       text-sm font-semibold text-red-800"
            >
                {{ session('error') }}
            </div>
        @endif

        {{-- ============================================================
            SEARCH
        ============================================================ --}}
        <section
            class="rounded-3xl border border-slate-200
                   bg-white p-5 shadow-sm sm:p-6"
        >
            <form
                action="{{ route(
                    'provincial.project-designations.index'
                ) }}"
                method="GET"
                class="flex flex-col gap-4
                       lg:flex-row lg:items-end"
            >
                <div class="flex-1">
                    <label
                        for="search"
                        class="mb-2 block text-xs font-bold
                               uppercase tracking-wider
                               text-slate-500"
                    >
                        Search Project Records
                    </label>

                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search Call-Off, project code, project title, or location..."
                        class="w-full rounded-xl border-slate-300
                               focus:border-[#970C13]
                               focus:ring-[#970C13]"
                    >
                </div>

                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="rounded-xl bg-[#970C13]
                               px-6 py-2.5 text-sm font-bold
                               text-white transition
                               hover:bg-[#641D21]"
                    >
                        Search
                    </button>

                    @if($search)
                        <a
                            href="{{ route(
                                'provincial.project-designations.index'
                            ) }}"
                            class="inline-flex items-center
                                   justify-center rounded-xl
                                   border border-slate-300 bg-white
                                   px-5 py-2.5 text-sm font-bold
                                   text-slate-700 transition
                                   hover:bg-slate-50"
                        >
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- ============================================================
            PROJECT DESIGNATION TABLE
        ============================================================ --}}
        <section
            class="overflow-hidden rounded-3xl
                   border border-slate-200
                   bg-white shadow-sm"
        >
            <div
                class="flex flex-col gap-2 border-b
                       border-slate-200 px-6 py-5 sm:px-7"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.16em] text-[#970C13]"
                >
                    Project Distribution Records
                </p>

                <h2
                    class="text-xl font-bold text-slate-950"
                >
                    PPE Project Designation History
                </h2>

                <p class="text-sm text-slate-500">
                    Each project record identifies the Call-Off
                    allocation used as its PPE inventory source.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table
                    class="min-w-[1900px] w-full
                           border-separate border-spacing-0"
                >
                    <thead class="bg-slate-900 text-white">
                        <tr
                            class="text-xs font-bold uppercase
                                   tracking-wide"
                        >
                            <th class="px-5 py-4 text-center">
                                No.
                            </th>

                            <th class="px-5 py-4 text-left">
                                Designation
                            </th>

                            <th
                                class="bg-[#970C13]
                                       px-5 py-4 text-left"
                            >
                                Call-Off Number
                            </th>

                            <th class="px-5 py-4 text-left">
                                Purchase Order
                            </th>

                            <th class="px-5 py-4 text-left">
                                Supplier
                            </th>

                            <th class="px-5 py-4 text-left">
                                Project Code
                            </th>

                            <th class="px-5 py-4 text-left">
                                Project Title
                            </th>

                            <th class="px-5 py-4 text-left">
                                Location
                            </th>

                            <th class="px-5 py-4 text-left">
                                Designation Date
                            </th>

                            <th class="px-5 py-4 text-center">
                                Beneficiaries
                            </th>

                            <th class="px-5 py-4 text-center">
                                Days
                            </th>

                            <th class="px-5 py-4 text-center">
                                Total PPE
                            </th>

                            <th class="px-5 py-4 text-center">
                                Status
                            </th>

                            <th class="px-5 py-4 text-center">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($designations as $designation)
                            @php
                                $provinceDistribution =
                                    $designation->provinceDistribution;

                                $distributionBatch =
                                    $provinceDistribution
                                        ?->distributionBatch;

                                $callOff =
                                    $distributionBatch
                                        ?->callOff;

                                $purchaseOrder =
                                    $distributionBatch
                                        ?->purchaseOrder;

                                $supplier =
                                    $purchaseOrder
                                        ?->supplier;

                                $totalPpe =
                                    $designation
                                        ->items
                                        ->sum('quantity');

                                $statusClass = match(
                                    $designation->status
                                ) {
                                    'Completed' =>
                                        'bg-green-100 text-green-800',

                                    'Cancelled' =>
                                        'bg-slate-200 text-slate-700',

                                    default =>
                                        'bg-amber-100 text-amber-800',
                                };

                                $isLegacy =
                                    ! $designation
                                        ->province_distribution_id;
                            @endphp

                            <tr
                                class="group transition
                                       hover:bg-slate-50"
                            >
                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4 text-center
                                           text-sm text-slate-500"
                                >
                                    {{
                                        $designations->firstItem()
                                        + $loop->index
                                    }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4"
                                >
                                    <p
                                        class="font-bold
                                               text-slate-900"
                                    >
                                        {{
                                            $designation
                                                ->designation_number
                                            ?? '—'
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           bg-[#DF979B]/10
                                           px-5 py-4"
                                >
                                    @if($isLegacy)
                                        <span
                                            class="inline-flex
                                                   rounded-full
                                                   bg-amber-100
                                                   px-3 py-1
                                                   text-xs font-bold
                                                   text-amber-800"
                                        >
                                            Legacy / Unassigned
                                        </span>
                                    @else
                                        <span
                                            class="font-bold
                                                   text-[#970C13]"
                                        >
                                            {{
                                                $callOff
                                                    ?->call_off_number
                                                ?? '—'
                                            }}
                                        </span>
                                    @endif
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4 font-semibold
                                           text-slate-800"
                                >
                                    {{
                                        $purchaseOrder?->po_number
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="min-w-56 border-b
                                           border-slate-200
                                           px-5 py-4
                                           text-sm text-slate-600"
                                >
                                    {{
                                        $supplier?->supplier_name
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4"
                                >
                                    <p
                                        class="font-bold
                                               text-slate-900"
                                    >
                                        {{ $designation->project_code }}
                                    </p>
                                </td>

                                <td
                                    class="min-w-64 border-b
                                           border-slate-200
                                           px-5 py-4 font-medium
                                           text-slate-900"
                                >
                                    {{ $designation->project_title }}
                                </td>

                                <td
                                    class="min-w-52 border-b
                                           border-slate-200
                                           px-5 py-4 text-sm
                                           text-slate-600"
                                >
                                    {{ $designation->location }}
                                </td>

                                <td
                                    class="whitespace-nowrap border-b
                                           border-slate-200
                                           px-5 py-4 text-sm
                                           text-slate-600"
                                >
                                    {{
                                        $designation
                                            ->designation_date
                                            ?->format('F d, Y')
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4 text-center
                                           font-bold text-slate-900"
                                >
                                    {{
                                        number_format(
                                            $designation
                                                ->number_of_beneficiaries
                                        )
                                    }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4 text-center
                                           text-slate-700"
                                >
                                    {{
                                        number_format(
                                            $designation
                                                ->number_of_days
                                        )
                                    }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4 text-center"
                                >
                                    <span
                                        class="inline-flex min-w-16
                                               justify-center rounded-xl
                                               bg-[#DF979B]/20
                                               px-3 py-2 font-bold
                                               text-[#970C13]"
                                    >
                                        {{
                                            number_format(
                                                $totalPpe
                                            )
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4 text-center"
                                >
                                    <span
                                        class="inline-flex rounded-full
                                               px-3 py-1 text-xs
                                               font-bold
                                               {{ $statusClass }}"
                                    >
                                        {{ $designation->status }}
                                    </span>
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4 text-center"
                                >
                                    <a
                                        href="{{ route(
                                            'provincial.project-designations.show',
                                            $designation
                                        ) }}"
                                        class="inline-flex items-center
                                               justify-center rounded-xl
                                               bg-slate-900 px-4 py-2
                                               text-sm font-bold
                                               text-white transition
                                               hover:bg-[#970C13]"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td
                                    colspan="14"
                                    class="px-6 py-16 text-center
                                           text-slate-500"
                                >
                                    No project PPE designations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($designations->hasPages())
                <div
                    class="border-t border-slate-200
                           px-6 py-4"
                >
                    {{ $designations->links() }}
                </div>
            @endif
        </section>

    </div>

</x-po_dashboard_layout>