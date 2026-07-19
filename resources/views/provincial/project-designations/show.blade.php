<x-po_dashboard_layout title="Project PPE Designation">

    @php
        $totalPpe = $supplyDesignation
            ->items
            ->sum('quantity');

        $provinceDistribution =
            $supplyDesignation->provinceDistribution;

        $distributionBatch =
            $provinceDistribution?->distributionBatch;

        $callOff =
            $distributionBatch?->callOff;

        $purchaseOrder =
            $distributionBatch?->purchaseOrder;

        $supplier =
            $purchaseOrder?->supplier;

        $isLegacy =
            ! $supplyDesignation->province_distribution_id;

        $statusClass = match(
            $supplyDesignation->status
        ) {
            'Completed' =>
                'bg-green-100 text-green-800',

            'Cancelled' =>
                'bg-slate-200 text-slate-700',

            default =>
                'bg-amber-100 text-amber-800',
        };
    @endphp

    <div class="mx-auto max-w-[1700px] space-y-6">

        {{-- ============================================================
            HEADER
        ============================================================ --}}
        <section
            class="relative overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b from-[#075985]
                       via-[#0284C7] to-[#38BDF8]"
            ></div>

            <div
                class="flex flex-col gap-6 px-6 py-7
                       sm:px-8 lg:flex-row
                       lg:items-center lg:justify-between"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#7DD3FC]/20
                                   px-3 py-1 text-xs font-bold
                                   uppercase tracking-[0.16em]
                                   text-[#0284C7]
                                   ring-1 ring-[#7DD3FC]"
                        >
                            Project PPE Designation
                        </span>

                        <span
                            class="rounded-full px-3 py-1
                                   text-xs font-bold
                                   {{ $statusClass }}"
                        >
                            {{ $supplyDesignation->status }}
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-3xl font-bold
                               tracking-tight text-slate-950"
                    >
                        {{ $supplyDesignation->project_code }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-600">
                        {{ $supplyDesignation->project_title }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route(
                            'provincial.project-designations.index'
                        ) }}"
                        class="rounded-xl border border-slate-300
                               bg-white px-5 py-3 text-sm font-bold
                               text-slate-700 transition
                               hover:bg-slate-50"
                    >
                        Back to Projects
                    </a>

                    <a
                        href="{{ route(
                            'provincial.call-off-inventory.index'
                        ) }}"
                        class="rounded-xl bg-[#0284C7]
                               px-5 py-3 text-sm font-bold
                               text-white transition
                               hover:bg-[#075985]"
                    >
                        Call-Off Inventory
                    </a>
                </div>
            </div>
        </section>

        @if(session('success'))
            <div
                class="rounded-2xl border border-green-200
                       bg-green-50 px-5 py-4
                       text-sm font-semibold text-green-800"
            >
                {{ session('success') }}
            </div>
        @endif

        {{-- ============================================================
            CALL-OFF SOURCE
        ============================================================ --}}
        <section
            class="overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="border-b border-slate-200
                       bg-slate-900 px-7 py-5"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.16em] text-[#7DD3FC]"
                >
                    Inventory Source
                </p>

                <h2 class="mt-1 text-xl font-bold text-white">
                    Call-Off Source Information
                </h2>
            </div>

            @if($isLegacy)
                <div class="p-7">
                    <div
                        class="rounded-2xl border border-amber-200
                               bg-amber-50 p-5"
                    >
                        <p class="font-bold text-amber-900">
                            Legacy / Unassigned Call-Off
                        </p>

                        <p
                            class="mt-2 text-sm leading-6
                                   text-amber-800"
                        >
                            This historical project designation was
                            created before Call-Off source tracking was
                            introduced. Beginning and ending Call-Off
                            balances cannot be reliably reconstructed.
                        </p>
                    </div>
                </div>
            @else
                <div
                    class="grid grid-cols-1 gap-5 p-7
                           sm:grid-cols-2 xl:grid-cols-4"
                >
                    <div
                        class="rounded-2xl border border-[#7DD3FC]
                               bg-[#7DD3FC]/10 p-5"
                    >
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wider text-[#0284C7]"
                        >
                            Call-Off Number
                        </p>

                        <p
                            class="mt-2 text-xl font-bold
                                   text-[#075985]"
                        >
                            {{ $callOff?->call_off_number ?? '—' }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200
                               bg-slate-50 p-5"
                    >
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wider text-slate-500"
                        >
                            Purchase Order
                        </p>

                        <p
                            class="mt-2 font-bold text-slate-900"
                        >
                            {{ $purchaseOrder?->po_number ?? '—' }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200
                               bg-slate-50 p-5"
                    >
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wider text-slate-500"
                        >
                            Supplier
                        </p>

                        <p
                            class="mt-2 font-bold text-slate-900"
                        >
                            {{
                                $supplier?->supplier_name
                                ?? '—'
                            }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200
                               bg-slate-50 p-5"
                    >
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wider text-slate-500"
                        >
                            Designation Number
                        </p>

                        <p
                            class="mt-2 font-bold text-slate-900"
                        >
                            {{
                                $supplyDesignation
                                    ->designation_number
                                ?? '—'
                            }}
                        </p>
                    </div>
                </div>
            @endif
        </section>

        {{-- ============================================================
            PROJECT INFORMATION
        ============================================================ --}}
        <section
            class="overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="border-b border-slate-200
                       px-7 py-5"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.16em] text-[#0284C7]"
                >
                    Project Record
                </p>

                <h2
                    class="mt-1 text-xl font-bold text-slate-950"
                >
                    Project Information
                </h2>
            </div>

            <div
                class="grid grid-cols-1 gap-6 p-7
                       sm:grid-cols-2 lg:grid-cols-4"
            >
                @php
                    $details = [
                        [
                            'label' => 'Project Code',
                            'value' =>
                                $supplyDesignation->project_code,
                        ],

                        [
                            'label' => 'Designation Date',
                            'value' =>
                                $supplyDesignation
                                    ->designation_date
                                    ?->format('F d, Y')
                                ?? '—',
                        ],

                        [
                            'label' => 'Province',
                            'value' =>
                                $supplyDesignation
                                    ->province
                                    ?->name
                                ?? '—',
                        ],

                        [
                            'label' => 'Created By',
                            'value' =>
                                $supplyDesignation
                                    ->creator
                                    ?->name
                                ?? '—',
                        ],

                        [
                            'label' => 'Number of Days',
                            'value' =>
                                number_format(
                                    $supplyDesignation
                                        ->number_of_days
                                ),
                        ],

                        [
                            'label' => 'Beneficiaries',
                            'value' =>
                                number_format(
                                    $supplyDesignation
                                        ->number_of_beneficiaries
                                ),
                        ],

                        [
                            'label' => 'Total PPE Distributed',
                            'value' =>
                                number_format($totalPpe),
                        ],

                        [
                            'label' => 'Submitted At',
                            'value' =>
                                $supplyDesignation
                                    ->submitted_at
                                    ?->format(
                                        'F d, Y h:i A'
                                    )
                                ?? '—',
                        ],
                    ];
                @endphp

                @foreach($details as $detail)
                    <div>
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wider text-slate-400"
                        >
                            {{ $detail['label'] }}
                        </p>

                        <p
                            class="mt-2 font-bold text-slate-900"
                        >
                            {{ $detail['value'] }}
                        </p>
                    </div>
                @endforeach

                <div class="sm:col-span-2 lg:col-span-4">
                    <p
                        class="text-xs font-bold uppercase
                               tracking-wider text-slate-400"
                    >
                        Project Title
                    </p>

                    <p class="mt-2 font-bold text-slate-900">
                        {{ $supplyDesignation->project_title }}
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <p
                        class="text-xs font-bold uppercase
                               tracking-wider text-slate-400"
                    >
                        Location
                    </p>

                    <p class="mt-2 text-slate-900">
                        {{ $supplyDesignation->location }}
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <p
                        class="text-xs font-bold uppercase
                               tracking-wider text-slate-400"
                    >
                        Remarks
                    </p>

                    <p
                        class="mt-2 whitespace-pre-line
                               text-slate-900"
                    >
                        {{
                            $supplyDesignation->remarks
                            ?: 'No remarks provided.'
                        }}
                    </p>
                </div>

                @if($supplyDesignation->are_document)
                    <div class="sm:col-span-2 lg:col-span-4">
                        <a
                            href="{{ asset(
                                'storage/'
                                .$supplyDesignation->are_document
                            ) }}"
                            target="_blank"
                            class="inline-flex rounded-xl
                                   bg-slate-900 px-5 py-3
                                   text-sm font-bold text-white
                                   transition hover:bg-[#0284C7]"
                        >
                            View ARE PDF
                        </a>
                    </div>
                @endif
            </div>
        </section>

        {{-- ============================================================
            INVENTORY MOVEMENT TABLE
        ============================================================ --}}
        <section
            class="overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="border-b border-slate-200
                       px-7 py-5"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.16em] text-[#0284C7]"
                >
                    PPE Movement
                </p>

                <h2
                    class="mt-1 text-xl font-bold text-slate-950"
                >
                    Beginning, Actual and Ending Inventory
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Inventory balances recorded at the time this
                    project designation was completed.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-900 text-white">
                        <tr
                            class="text-xs font-bold uppercase
                                   tracking-wide"
                        >
                            <th class="px-5 py-4 text-left">
                                No.
                            </th>

                            <th class="px-5 py-4 text-left">
                                PPE Item
                            </th>

                            <th class="px-5 py-4 text-left">
                                Size / Label
                            </th>

                            <th class="px-5 py-4 text-left">
                                Unit
                            </th>

                            <th
                                class="bg-[#075985]
                                       px-5 py-4 text-center"
                            >
                                Beginning Inventory
                            </th>

                            <th
                                class="bg-[#0EA5E9]
                                       px-5 py-4 text-center"
                            >
                                Actual Distributed
                            </th>

                            <th
                                class="bg-[#0284C7]
                                       px-5 py-4 text-center"
                            >
                                Ending Inventory
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse(
                            $supplyDesignation->items
                            as $designationItem
                        )
                            @php
                                $movement =
                                    $movementBreakdown[
                                        $designationItem->item_id
                                    ] ?? null;
                            @endphp

                            <tr class="hover:bg-slate-50">
                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4 text-sm
                                           text-slate-500"
                                >
                                    {{ $loop->iteration }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4 font-bold
                                           text-slate-900"
                                >
                                    {{
                                        $designationItem
                                            ->item
                                            ->item_name
                                    }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4 text-slate-600"
                                >
                                    {{
                                        $designationItem
                                            ->item
                                            ->label
                                        ?: '—'
                                    }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4 text-slate-600"
                                >
                                    {{
                                        $designationItem
                                            ->item
                                            ->unit_of_measurement
                                    }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           bg-[#7DD3FC]/10
                                           px-5 py-4 text-center
                                           text-lg font-bold
                                           text-[#075985]"
                                >
                                    @if(
                                        $movement
                                        && $movement['beginning']
                                            !== null
                                    )
                                        {{
                                            number_format(
                                                $movement['beginning']
                                            )
                                        }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           px-5 py-4 text-center
                                           text-lg font-bold
                                           text-[#0EA5E9]"
                                >
                                    {{
                                        number_format(
                                            $movement['actual']
                                            ?? $designationItem->quantity
                                        )
                                    }}
                                </td>

                                <td
                                    class="border-b border-slate-200
                                           bg-green-50 px-5 py-4
                                           text-center text-lg
                                           font-bold text-green-700"
                                >
                                    @if(
                                        $movement
                                        && $movement['ending']
                                            !== null
                                    )
                                        {{
                                            number_format(
                                                $movement['ending']
                                            )
                                        }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="px-6 py-14 text-center
                                           text-slate-500"
                                >
                                    No designated PPE items found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot class="bg-slate-100">
                        <tr>
                            <td
                                colspan="5"
                                class="px-5 py-4 text-right
                                       font-bold text-slate-700"
                            >
                                Total PPE Distributed
                            </td>

                            <td
                                class="px-5 py-4 text-center
                                       text-xl font-bold
                                       text-[#0284C7]"
                            >
                                {{ number_format($totalPpe) }}
                            </td>

                            <td class="px-5 py-4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

    </div>

</x-po_dashboard_layout>