<x-po_dashboard_layout title="Create Project PPE Designation">

    @php
        $allocations = $allocations ?? collect();
        $balances = $balances ?? [];
        $selectedAllocationId = (int) ($selectedAllocationId ?? 0);

        $batch =
            $selectedAllocation
                    ?->distributionBatch;

        $callOff =
            $batch?->callOff;

        $purchaseOrder =
            $batch?->purchaseOrder;

        $supplier =
            $purchaseOrder?->supplier;

        $availableTotal = collect($balances)
            ->sum('available_for_projects');

        $actualReceivedTotal = collect($balances)
            ->sum('actual_received');

        $previouslyDistributedTotal = collect($balances)
            ->sum('previously_distributed');

        $allocatedTotal = collect($balances)
            ->sum('allocated');
    @endphp

    <div class="mx-auto max-w-[1600px] space-y-6">

        {{-- =========================================================
        PAGE HEADER
        ========================================================== --}}
        <section class="relative overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b from-[#641D21]
                       via-[#970C13] to-[#ED1B24]"></div>

            <div class="flex flex-col gap-5 px-6 py-7
                       sm:px-8 lg:flex-row
                       lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-[#DF979B]/20
                                   px-3 py-1 text-xs font-bold
                                   uppercase tracking-[0.16em]
                                   text-[#970C13]
                                   ring-1 ring-[#DF979B]">
                            Provincial Office
                        </span>

                        <span class="rounded-full bg-slate-100
                                   px-3 py-1 text-xs font-semibold
                                   text-slate-700 ring-1
                                   ring-slate-200">
                            Project PPE Designation
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-bold
                               tracking-tight text-slate-950
                               sm:text-3xl">
                        Create Project PPE Designation
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm
                               leading-6 text-slate-600">
                        Select the source Call-Off, enter the project
                        information, and distribute only the PPE that
                        has been physically received and remains
                        available under that Call-Off.
                    </p>
                </div>

                <a href="{{ route(
    'provincial.project-designations.index'
) }}" class="inline-flex items-center justify-center
                           rounded-xl border border-slate-300
                           bg-white px-5 py-3 text-sm
                           font-bold text-slate-700 transition
                           hover:bg-slate-50">
                    Back to Project Designations
                </a>
            </div>
        </section>

        {{-- =========================================================
        VALIDATION ERRORS
        ========================================================== --}}
        @if($errors->any())
            <section class="rounded-2xl border border-red-200
                               bg-red-50 px-6 py-5">
                <h2 class="font-bold text-red-800">
                    Please correct the following:
                </h2>

                <ul class="mt-3 list-disc space-y-1 pl-5
                                   text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- =========================================================
        CALL-OFF SELECTOR
        ========================================================== --}}
        <section class="overflow-hidden rounded-3xl border
                   border-slate-200 bg-white shadow-sm">
            <div class="bg-[#970C13] px-6 py-5 sm:px-7">
                <h2 class="text-xl font-bold text-white">
                    Select Source Call-Off
                </h2>

                <p class="mt-1 text-sm text-red-100">
                    Only Call-Off allocations with physically received
                    and undistributed PPE are available.
                </p>
            </div>

            <form method="GET" action="{{ route(
    'provincial.project-designations.create'
) }}" class="p-6 sm:p-7">
                <div class="flex flex-col gap-4
                           lg:flex-row lg:items-end">
                    <div class="flex-1">
                        <label for="province_distribution_id" class="mb-2 block text-sm
                                   font-bold text-slate-700">
                            Call-Off Allocation
                        </label>

                        <select id="province_distribution_id" name="province_distribution_id" required class="w-full rounded-xl border-slate-300
                                   focus:border-[#970C13]
                                   focus:ring-[#970C13]">
                            <option value="">
                                Select a Call-Off
                            </option>

                            @foreach($allocations as $allocation)
                                                    @php
                                                        $allocationBatch =
                                                            $allocation
                                                                ->distributionBatch;

                                                        $allocationCallOff =
                                                            $allocationBatch
                                                                    ?->callOff;

                                                        $allocationPo =
                                                            $allocationBatch
                                                                    ?->purchaseOrder;

                                                        $allocationSupplier =
                                                            $allocationPo
                                                                    ?->supplier;

                                                        $allocationAvailable =
                                                            (int) (
                                                                $allocation
                                                                    ->available_for_projects_total
                                                                ?? 0
                                                            );
                                                    @endphp

                                                    <option value="{{ $allocation->id }}" @selected(
                                                        $selectedAllocationId
                                                        === (int) $allocation->id
                                                    )>
                                                        {{
                                $allocationCallOff
                                        ?->call_off_number
                                ?? 'No Call-Off Number'
                                                                                    }}
                                                        —
                                                        {{
                                $allocationPo?->po_number
                                ?? 'No PO'
                                                                                    }}
                                                        —
                                                        {{
                                $allocationSupplier
                                        ?->supplier_name
                                ?? 'Supplier unavailable'
                                                                                    }}
                                                        —
                                                        {{
                                number_format(
                                    $allocationAvailable
                                )
                                                                                    }}
                                                        PPE available
                                                    </option>
                            @endforeach
                        </select>

                        @error('province_distribution_id')
                            <p class="mt-2 text-sm
                                               font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button type="submit" class="inline-flex items-center justify-center
                               rounded-xl bg-slate-900 px-6 py-3
                               font-bold text-white transition
                               hover:bg-slate-800">
                        Load Call-Off Inventory
                    </button>
                </div>
            </form>
        </section>

        {{-- =========================================================
        NO AVAILABLE CALL-OFFS
        ========================================================== --}}
        @if($allocations->isEmpty())
                <section class="rounded-3xl border border-amber-200
                                       bg-amber-50 px-6 py-12 text-center">
                    <h2 class="text-xl font-bold text-amber-900">
                        No Call-Off inventory is available
                    </h2>

                    <p class="mx-auto mt-2 max-w-2xl text-sm
                                           leading-6 text-amber-800">
                        The Provincial Office must first receive PPE under
                        an approved Call-Off. A Call-Off will also disappear
                        from this selection once all of its received PPE has
                        been distributed to projects.
                    </p>

                    <a href="{{ route(
                'provincial.receiving.index'
            ) }}" class="mt-5 inline-flex rounded-xl
                                           bg-[#970C13] px-5 py-3
                                           font-bold text-white transition
                                           hover:bg-[#641D21]">
                        View Call-Off Allocations
                    </a>
                </section>
        @endif

        {{-- =========================================================
        SELECTED CALL-OFF CONTENT
        ========================================================== --}}
        @if($selectedAllocation)

                {{-- Call-Off summary --}}
                <section class="grid grid-cols-1 gap-4
                                       sm:grid-cols-2 xl:grid-cols-5">
                    <article class="rounded-2xl border border-slate-200
                                           bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase
                                               tracking-wider text-slate-400">
                            Call-Off Number
                        </p>

                        <p class="mt-3 text-xl font-bold
                                               text-[#641D21]">
                            {{ $callOff?->call_off_number ?? '—' }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Allocation #{{ $selectedAllocation->id }}
                        </p>
                    </article>

                    <article class="rounded-2xl border border-slate-200
                                           bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase
                                               tracking-wider text-slate-400">
                            Purchase Order
                        </p>

                        <p class="mt-3 text-xl font-bold
                                               text-slate-950">
                            {{ $purchaseOrder?->po_number ?? '—' }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ $supplier?->supplier_name ?? 'Supplier unavailable' }}
                        </p>
                    </article>

                    <article class="rounded-2xl border border-slate-200
                                           bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase
                                               tracking-wider text-slate-400">
                            Allocation
                        </p>

                        <p class="mt-3 text-2xl font-bold
                                               text-slate-950">
                            {{ number_format($allocatedTotal) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Original PPE allocation
                        </p>
                    </article>

                    <article class="rounded-2xl border border-slate-200
                                           bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase
                                               tracking-wider text-slate-400">
                            Actual Received
                        </p>

                        <p class="mt-3 text-2xl font-bold
                                               text-blue-700">
                            {{ number_format($actualReceivedTotal) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Physically received PPE
                        </p>
                    </article>

                    <article class="rounded-2xl border border-slate-200
                                           bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase
                                               tracking-wider text-slate-400">
                            Available for Projects
                        </p>

                        <p class="mt-3 text-2xl font-bold
                                               text-green-700">
                            {{ number_format($availableTotal) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            After
                            {{ number_format($previouslyDistributedTotal) }}
                            previously distributed
                        </p>
                    </article>
                </section>

                {{-- =====================================================
                PROJECT DESIGNATION FORM
                ====================================================== --}}
                <form id="projectDesignationForm" action="{{ route(
                'provincial.project-designations.store'
            ) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <input type="hidden" name="province_distribution_id" value="{{ $selectedAllocation->id }}">

                    {{-- Project information --}}
                    <section class="overflow-hidden rounded-3xl
                                           border border-slate-200
                                           bg-white shadow-sm">
                        <div class="bg-[#970C13] px-6 py-5 sm:px-7">
                            <h2 class="text-xl font-bold text-white">
                                Project Information
                            </h2>

                            <p class="mt-1 text-sm text-red-100">
                                Enter the official details of the project
                                receiving the PPE.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 p-6
                                               sm:p-7 lg:grid-cols-2">
                            <div>
                                <label for="project_code" class="mb-2 block text-sm
                                                       font-bold text-slate-700">
                                    Project Code
                                </label>

                                <input type="text" id="project_code" name="project_code" value="{{ old('project_code') }}"
                                    required maxlength="255" autocomplete="off" placeholder="Example: TUPAD-ALB-2026-001" class="w-full rounded-xl
                                                       border-slate-300 uppercase
                                                       focus:border-[#970C13]
                                                       focus:ring-[#970C13]">

                                @error('project_code')
                                    <p class="mt-2 text-sm
                                                                   font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="designation_date" class="mb-2 block text-sm
                                                       font-bold text-slate-700">
                                    Designation Date
                                </label>

                                <input type="date" id="designation_date" name="designation_date" value="{{ old(
                'designation_date',
                now()->format('Y-m-d')
            ) }}" required class="w-full rounded-xl
                                                       border-slate-300
                                                       focus:border-[#970C13]
                                                       focus:ring-[#970C13]">

                                @error('designation_date')
                                    <p class="mt-2 text-sm
                                                                   font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label for="project_title" class="mb-2 block text-sm
                                                       font-bold text-slate-700">
                                    Project Title
                                </label>

                                <input type="text" id="project_title" name="project_title" value="{{ old('project_title') }}"
                                    required maxlength="255" placeholder="Enter the official project title" class="w-full rounded-xl
                                                       border-slate-300
                                                       focus:border-[#970C13]
                                                       focus:ring-[#970C13]">

                                @error('project_title')
                                    <p class="mt-2 text-sm
                                                                   font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label for="location" class="mb-2 block text-sm
                                                       font-bold text-slate-700">
                                    Project Location
                                </label>

                                <input type="text" id="location" name="location" value="{{ old('location') }}" required
                                    maxlength="255" placeholder="Barangay, municipality, or complete project location" class="w-full rounded-xl
                                                       border-slate-300
                                                       focus:border-[#970C13]
                                                       focus:ring-[#970C13]">

                                @error('location')
                                    <p class="mt-2 text-sm
                                                                   font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="number_of_days" class="mb-2 block text-sm
                                                       font-bold text-slate-700">
                                    Number of Days
                                </label>

                                <input type="number" id="number_of_days" name="number_of_days" value="{{ old(
                'number_of_days',
                1
            ) }}" min="1" step="1" required class="w-full rounded-xl
                                                       border-slate-300
                                                       focus:border-[#970C13]
                                                       focus:ring-[#970C13]">

                                @error('number_of_days')
                                    <p class="mt-2 text-sm
                                                                   font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="number_of_beneficiaries" class="mb-2 block text-sm
                                                       font-bold text-slate-700">
                                    Number of Beneficiaries
                                </label>

                                <input type="number" id="number_of_beneficiaries" name="number_of_beneficiaries" value="{{ old(
                'number_of_beneficiaries',
                1
            ) }}" min="1" step="1" required class="w-full rounded-xl
                                                       border-slate-300
                                                       focus:border-[#970C13]
                                                       focus:ring-[#970C13]">

                                @error('number_of_beneficiaries')
                                    <p class="mt-2 text-sm
                                                                   font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label for="are_document" class="mb-2 block text-sm
                                                       font-bold text-slate-700">
                                    ARE Document
                                </label>

                                <input type="file" id="are_document" name="are_document" accept="application/pdf,.pdf" required
                                    class="w-full rounded-xl border
                                                       border-slate-300 bg-white
                                                       px-4 py-3 text-sm">

                                <p class="mt-2 text-xs text-slate-500">
                                    PDF only. Maximum file size: 10 MB.
                                </p>

                                @error('are_document')
                                    <p class="mt-2 text-sm
                                                                   font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label for="remarks" class="mb-2 block text-sm
                                                       font-bold text-slate-700">
                                    Remarks
                                </label>

                                <textarea id="remarks" name="remarks" rows="4" maxlength="5000"
                                    placeholder="Optional project or PPE distribution remarks." class="w-full rounded-xl
                                                       border-slate-300
                                                       focus:border-[#970C13]
                                                       focus:ring-[#970C13]">{{ old('remarks') }}</textarea>

                                @error('remarks')
                                    <p class="mt-2 text-sm
                                                                   font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- PPE distribution table --}}
                    <section class="overflow-hidden rounded-3xl
                                           border border-slate-200
                                           bg-white shadow-sm">
                        <div class="flex flex-col gap-3 bg-slate-900
                                               px-6 py-5 sm:flex-row
                                               sm:items-center
                                               sm:justify-between sm:px-7">
                            <div>
                                <h2 class="text-xl font-bold text-white">
                                    PPE to Distribute
                                </h2>

                                <p class="mt-1 text-sm text-slate-300">
                                    Quantities are limited to the PPE
                                    available under the selected Call-Off.
                                </p>
                            </div>

                            <div class="rounded-xl bg-white/10
                                                   px-4 py-3 text-right">
                                <p class="text-xs font-bold uppercase
                                                       tracking-wider
                                                       text-slate-300">
                                    Total for this project
                                </p>

                                <p id="totalDesignationQuantity" class="mt-1 text-xl font-bold
                                                       text-white">
                                    0
                                </p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-[1300px] w-full
                                                   divide-y divide-slate-200">
                                <thead class="bg-slate-100">
                                    <tr class="text-xs font-bold uppercase
                               tracking-wide text-slate-600">
                                        <th class="px-5 py-4 text-left">
                                            PPE Item
                                        </th>

                                        <th class="px-5 py-4 text-left">
                                            Size / Label
                                        </th>

                                        <th class="px-5 py-4 text-left">
                                            Unit
                                        </th>

                                        <th class="px-5 py-4 text-center">
                                            Allocation
                                        </th>

                                        <th class="px-5 py-4 text-center">
                                            Actual Received
                                        </th>

                                        <th class="px-5 py-4 text-center">
                                            Previously Distributed
                                        </th>

                                        <th class="px-5 py-4 text-center">
                                            Call-Off Balance
                                        </th>

                                        <th class="px-5 py-4 text-center">
                                            Provincial Stock
                                        </th>

                                        <th class="px-5 py-4 text-center">
                                            Available for Project
                                        </th>

                                        <th class="px-5 py-4 text-center">
                                            Project Quantity
                                        </th>

                                        <th class="px-5 py-4 text-center">
                                            Remaining After
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">

                                    @foreach($balances as $itemId => $balance)
                                                            @php
                                                                $item = $balance['item'];

                                                                $callOffAvailable = (int) (
                                                                    $balance['call_off_available']
                                                                    ?? 0
                                                                );

                                                                $pooledAvailable = (int) (
                                                                    $balance['pooled_available']
                                                                    ?? 0
                                                                );

                                                                $available = (int) (
                                                                    $balance['available_for_projects']
                                                                    ?? 0
                                                                );

                                                                $legacyReserve = (int) (
                                                                    $balance['legacy_or_unassigned_reserve']
                                                                    ?? 0
                                                                );

                                                                $oldQuantity = (int) old(
                                                                    'items.' . $itemId,
                                                                    0
                                                                );

                                                                $itemName = trim(
                                                                    ($item?->item_name ?? 'PPE Item')
                                                                    . ' '
                                                                    . ($item?->label ?? '')
                                                                );
                                                            @endphp

                                                            <tr class="transition hover:bg-slate-50">

                                                                <td class="px-5 py-4 font-semibold
                                                       text-slate-900">
                                                                    {{ $item?->item_name ?? '—' }}
                                                                </td>

                                                                <td class="px-5 py-4 text-slate-600">
                                                                    {{ $item?->label ?: '—' }}
                                                                </td>

                                                                <td class="px-5 py-4 text-slate-600">
                                                                    {{
                                            $item?->unit_of_measurement
                                            ?? '—'
                                                }}
                                                                </td>

                                                                <td class="px-5 py-4 text-center
                                                       font-semibold text-slate-800">
                                                                    {{
                                            number_format(
                                                $balance['allocated'] ?? 0
                                            )
                                                }}
                                                                </td>

                                                                <td class="px-5 py-4 text-center
                                                       font-semibold text-blue-700">
                                                                    {{
                                            number_format(
                                                $balance['actual_received'] ?? 0
                                            )
                                                }}
                                                                </td>

                                                                <td class="px-5 py-4 text-center
                                                       font-semibold text-amber-700">
                                                                    {{
                                            number_format(
                                                $balance[
                                                    'previously_distributed'
                                                ] ?? 0
                                            )
                                                }}
                                                                </td>

                                                                <td class="px-5 py-4 text-center
                                                       font-semibold text-indigo-700">
                                                                    {{ number_format($callOffAvailable) }}
                                                                </td>

                                                                <td class="px-5 py-4 text-center
                                                       font-semibold text-amber-700">
                                                                    {{ number_format($pooledAvailable) }}
                                                                </td>

                                                                <td class="px-5 py-4 text-center">
                                                                    <p class="text-lg font-bold
                                                           {{
                                            $available > 0
                                            ? 'text-green-700'
                                            : 'text-red-700'
                                                           }}">
                                                                        {{ number_format($available) }}
                                                                    </p>

                                                                    @if($legacyReserve > 0)
                                                                                            <p class="mt-2 max-w-48 text-xs
                                                                                       font-semibold leading-5
                                                                                       text-amber-700">
                                                                                                {{
                                                                        number_format(
                                                                            $legacyReserve
                                                                        )
                                                                                }}
                                                                                                units are unavailable because
                                                                                                province-wide stock was consumed by
                                                                                                legacy or unassigned transactions.
                                                                                            </p>
                                                                    @endif
                                                                </td>

                                                                <td class="px-5 py-4 text-center">

                                                                    <input type="number" name="items[{{ $itemId }}]" value="{{ min(
                                            $oldQuantity,
                                            $available
                                        ) }}" min="0" max="{{ $available }}" step="1" inputmode="numeric" data-stock-input
                                                                        data-available="{{ $available }}" data-item-name="{{ $itemName }}"
                                                                        @readonly($available <= 0) class="w-28 rounded-lg
                                                           border-slate-300 text-center
                                                           focus:border-[#970C13]
                                                           focus:ring-[#970C13]
                                                           read-only:cursor-not-allowed
                                                           read-only:bg-slate-100
                                                           read-only:text-slate-400">

                                                                    <p data-stock-error class="mt-2 hidden text-xs
                                                           font-semibold text-red-600"></p>

                                                                    @error('items.' . $itemId)
                                                                                <p class="mt-2 text-xs
                                                                           font-semibold text-red-600">
                                                                                    {{ $message }}
                                                                                </p>
                                                                    @enderror

                                                                </td>

                                                                <td class="px-5 py-4 text-center">
                                                                    <span data-remaining-output class="font-semibold text-green-700">
                                                                        {{
                                            number_format(
                                                max(
                                                    0,
                                                    $available
                                                    - min(
                                                        $oldQuantity,
                                                        $available
                                                    )
                                                )
                                            )
                                                    }}
                                                                    </span>
                                                                </td>

                                                            </tr>
                                    @endforeach

                                </tbody>

                                <tfoot class="bg-slate-100">
    <tr>
        <td
            colspan="9"
            class="px-5 py-4 text-right
                   font-bold text-slate-700"
        >
            Total PPE to Designate
        </td>

        <td
            class="px-5 py-4 text-center
                   text-lg font-bold text-[#970C13]"
        >
            <span id="totalDesignationQuantityFooter">
                0
            </span>
        </td>

        <td></td>
    </tr>
</tfoot>
                            </table>
                        </div>

                        @error('items')
                            <p class="border-t border-red-100
                                                           bg-red-50 px-7 py-4
                                                           text-sm font-semibold
                                                           text-red-700">
                                {{ $message }}
                            </p>
                        @enderror
                    </section>

                    {{-- Form actions --}}
                    <div class="flex flex-col-reverse gap-3
                                           sm:flex-row sm:justify-end">
                        <a href="{{ route(
                'provincial.project-designations.index'
            ) }}" class="inline-flex items-center justify-center
                                               rounded-xl border border-slate-300
                                               bg-white px-6 py-3 font-bold
                                               text-slate-700 transition
                                               hover:bg-slate-50">
                            Cancel
                        </a>

                        <button type="submit" id="submitDesignationButton" disabled class="inline-flex items-center justify-center
                                               rounded-xl bg-[#970C13] px-7 py-3
                                               font-bold text-white transition
                                               hover:bg-[#641D21]
                                               disabled:cursor-not-allowed
                                               disabled:opacity-50">
                            Save Project Designation
                        </button>
                    </div>
                </form>
        @endif
    </div>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const callOffSelect =
                    document.getElementById(
                        'province_distribution_id'
                    );

                if (callOffSelect) {
                    callOffSelect.addEventListener(
                        'change',
                        function () {
                            if (this.value) {
                                this.form.submit();
                            }
                        }
                    );
                }

                const form =
                    document.getElementById(
                        'projectDesignationForm'
                    );

                if (!form) {
                    return;
                }

                const stockInputs = Array.from(
                    form.querySelectorAll(
                        '[data-stock-input]'
                    )
                );

                const totalHeader =
                    document.getElementById(
                        'totalDesignationQuantity'
                    );

                const totalFooter =
                    document.getElementById(
                        'totalDesignationQuantityFooter'
                    );

                const submitButton =
                    document.getElementById(
                        'submitDesignationButton'
                    );

                function updateInventoryPreview() {
                    let total = 0;
                    let valid = true;
                    let hasQuantity = false;

                    stockInputs.forEach(input => {
                        const available = Number(
                            input.dataset.available || 0
                        );

                        const itemName =
                            input.dataset.itemName
                            || 'PPE item';

                        let quantity = Number(
                            input.value || 0
                        );

                        const row =
                            input.closest('tr');

                        const remainingOutput =
                            row?.querySelector(
                                '[data-remaining-output]'
                            );

                        const stockError =
                            row?.querySelector(
                                '[data-stock-error]'
                            );

                        if (
                            !Number.isFinite(quantity)
                            || quantity < 0
                        ) {
                            quantity = 0;
                            input.value = 0;
                        }

                        quantity = Math.floor(quantity);
                        input.value = quantity;

                        const exceeds =
                            quantity > available;

                        const remaining =
                            available - quantity;

                        if (quantity > 0) {
                            hasQuantity = true;
                        }

                        if (remainingOutput) {
                            remainingOutput.textContent =
                                Math.max(
                                    0,
                                    remaining
                                ).toLocaleString();

                            remainingOutput.className =
                                exceeds
                                    ? 'font-semibold text-red-700'
                                    : 'font-semibold text-green-700';
                        }

                        if (exceeds) {
                            valid = false;

                            input.classList.add(
                                'border-red-500',
                                'bg-red-50',
                                'text-red-900'
                            );

                            input.setAttribute(
                                'aria-invalid',
                                'true'
                            );

                            if (stockError) {
                                stockError.textContent =
                                    `${itemName} has only `
                                    + `${available.toLocaleString()} `
                                    + 'available under this Call-Off.';

                                stockError.classList.remove(
                                    'hidden'
                                );
                            }
                        } else {
                            input.classList.remove(
                                'border-red-500',
                                'bg-red-50',
                                'text-red-900'
                            );

                            input.removeAttribute(
                                'aria-invalid'
                            );

                            if (stockError) {
                                stockError.textContent = '';

                                stockError.classList.add(
                                    'hidden'
                                );
                            }
                        }

                        total += quantity;
                    });

                    if (!hasQuantity || total <= 0) {
                        valid = false;
                    }

                    const formattedTotal =
                        total.toLocaleString();

                    if (totalHeader) {
                        totalHeader.textContent =
                            formattedTotal;
                    }

                    if (totalFooter) {
                        totalFooter.textContent =
                            formattedTotal;
                    }

                    if (submitButton) {
                        submitButton.disabled = !valid;
                    }

                    return {
                        valid,
                        total,
                    };
                }

                stockInputs.forEach(input => {
                    input.addEventListener(
                        'input',
                        updateInventoryPreview
                    );
                });

                form.addEventListener(
                    'submit',
                    function (event) {
                        const result =
                            updateInventoryPreview();

                        if (!result.valid) {
                            event.preventDefault();

                            const invalidInput =
                                stockInputs.find(input => {
                                    const available =
                                        Number(
                                            input.dataset
                                                .available || 0
                                        );

                                    const quantity =
                                        Number(
                                            input.value || 0
                                        );

                                    return quantity
                                        > available;
                                });

                            if (invalidInput) {
                                invalidInput.focus();

                                return;
                            }

                            alert(
                                'Enter at least one PPE quantity '
                                + 'greater than zero.'
                            );

                            return;
                        }

                        if (submitButton) {
                            submitButton.disabled = true;

                            submitButton.textContent =
                                'Saving Designation...';
                        }
                    }
                );

                updateInventoryPreview();
            }
        );
    </script>

</x-po_dashboard_layout>