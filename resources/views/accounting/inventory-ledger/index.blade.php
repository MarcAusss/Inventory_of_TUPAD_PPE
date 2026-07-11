<x-po_dashboard_layout title="Accounting Dashboard">

    @php
        $provinceLabel = $selectedProvince
            ? $selectedProvince->name
            : 'All Provincial Offices';
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-900">
                    Provincial Inventory Monitoring
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Read-only inventory ledger for
                    <span class="font-semibold text-gray-900">
                        {{ $provinceLabel }}
                    </span>.
                </p>

            </div>

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center justify-center rounded-xl bg-red-900 px-5 py-3 font-semibold text-white transition hover:bg-red-800"
            >
                Print Inventory Report
            </button>

        </div>

        {{-- Read-Only Notice --}}
        <div class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 text-blue-900">

            <strong>Accounting access is view-only.</strong>

            Records, quantities, approvals, receiving entries, and project
            distributions cannot be modified from this account.

        </div>

        {{-- Current or Archive Notice --}}
        @if((int) $year === (int) $currentYear)

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">

                Viewing current inventory records for

                <span class="font-semibold">
                    {{ $year }}
                </span>.

            </div>

        @else

            <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-5 py-4 text-yellow-900">

                Viewing archived inventory records for

                <span class="font-semibold">
                    {{ $year }}
                </span>.

                The records remain stored in the same database.

            </div>

        @endif

        {{-- Filters --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

            <form
                action="{{ route('accounting.inventory-ledger.index') }}"
                method="GET"
                class="grid grid-cols-1 gap-4 lg:grid-cols-5"
            >

                <div>

                    <label
                        for="province_id"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Provincial Office
                    </label>

                    <select
                        id="province_id"
                        name="province_id"
                        class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                    >

                        <option value="">
                            All Provincial Offices
                        </option>

                        @foreach($provinces as $province)

                            <option
                                value="{{ $province->id }}"
                                @selected(
                                    (int) $provinceId
                                    === (int) $province->id
                                )
                            >
                                {{ $province->name }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div>

                    <label
                        for="year"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Inventory Year
                    </label>

                    <select
                        id="year"
                        name="year"
                        class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                    >

                        @foreach($availableYears as $availableYear)

                            <option
                                value="{{ $availableYear }}"
                                @selected(
                                    (int) $availableYear
                                    === (int) $year
                                )
                            >
                                {{ $availableYear }}

                                @if(
                                    (int) $availableYear
                                    === (int) $currentYear
                                )
                                    — Current
                                @else
                                    — Archive
                                @endif
                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="lg:col-span-2">

                    <label
                        for="search"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Search
                    </label>

                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="PPE, Call-Off, DR, project, province..."
                        class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                    >

                </div>

                <div class="flex items-end gap-2">

                    <button
                        type="submit"
                        class="inline-flex flex-1 items-center justify-center rounded-xl bg-red-900 px-5 py-3 font-semibold text-white transition hover:bg-red-800"
                    >
                        Apply
                    </button>

                    @if(
                        $search
                        || $provinceId
                        || (int) $year !== (int) $currentYear
                    )

                        <a
                            href="{{ route('accounting.inventory-ledger.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            Reset
                        </a>

                    @endif

                </div>

            </form>

        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-5">

            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-6">

                <p class="text-sm font-medium text-blue-700">
                    Beginning Inventory
                </p>

                <p class="mt-3 text-3xl font-bold text-blue-900">
                    {{ number_format($totals['beginning_inventory']) }}
                </p>

            </div>

            <div class="rounded-2xl border border-green-200 bg-green-50 p-6">

                <p class="text-sm font-medium text-green-700">
                    Received Inventory
                </p>

                <p class="mt-3 text-3xl font-bold text-green-900">
                    {{ number_format($totals['received_inventory']) }}
                </p>

            </div>

            <div class="rounded-2xl border border-orange-200 bg-orange-50 p-6">

                <p class="text-sm font-medium text-orange-700">
                    Project Issues
                </p>

                <p class="mt-3 text-3xl font-bold text-orange-900">
                    {{ number_format($totals['issued_inventory']) }}
                </p>

            </div>

            <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-6">

                <p class="text-sm font-medium text-indigo-700">
                    Actual Inventory
                </p>

                <p class="mt-3 text-3xl font-bold text-indigo-900">
                    {{ number_format($totals['actual_inventory']) }}
                </p>

            </div>

            <div class="rounded-2xl border border-gray-300 bg-gray-100 p-6">

                <p class="text-sm font-medium text-gray-700">
                    Ending Inventory
                </p>

                <p class="mt-3 text-3xl font-bold text-gray-900">
                    {{ number_format($totals['ending_inventory']) }}
                </p>

            </div>

        </div>

        {{-- Annual Summary --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-red-900 px-6 py-5">

                <h2 class="text-xl font-semibold text-white">
                    {{ $provinceLabel }} Inventory Summary — {{ $year }}
                </h2>

                <p class="mt-1 text-sm text-red-100">
                    Ending Inventory carries forward as the next year’s Beginning Inventory.
                </p>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-100">

                        <tr class="text-xs font-semibold uppercase tracking-wide text-gray-700">

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

                            <th class="px-5 py-4 text-center">
                                Beginning
                            </th>

                            <th class="px-5 py-4 text-center">
                                Received
                            </th>

                            <th class="px-5 py-4 text-center">
                                Issued
                            </th>

                            <th class="px-5 py-4 text-center">
                                Actual
                            </th>

                            <th class="px-5 py-4 text-center">
                                Ending
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($summary as $row)

                            @php
                                $ending =
                                    (int) $row['ending_inventory'];

                                $stockClass = match (true) {
                                    $ending <= 0 =>
                                        'bg-red-100 text-red-800',

                                    $ending <= 10 =>
                                        'bg-yellow-100 text-yellow-800',

                                    default =>
                                        'bg-green-100 text-green-800',
                                };
                            @endphp

                            <tr class="hover:bg-gray-50">

                                <td class="px-5 py-4 text-sm text-gray-600">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-5 py-4 font-semibold text-gray-900">
                                    {{ $row['item']->item_name }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $row['item']->label ?: '—' }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $row['item']->unit_of_measurement }}
                                </td>

                                <td class="px-5 py-4 text-center font-semibold text-blue-900">
                                    {{ number_format($row['beginning_inventory']) }}
                                </td>

                                <td class="px-5 py-4 text-center font-semibold text-green-700">
                                    +{{ number_format($row['received_inventory']) }}
                                </td>

                                <td class="px-5 py-4 text-center font-semibold text-orange-700">
                                    -{{ number_format($row['issued_inventory']) }}
                                </td>

                                <td class="px-5 py-4 text-center font-bold text-indigo-900">
                                    {{ number_format($row['actual_inventory']) }}
                                </td>

                                <td class="px-5 py-4 text-center">

                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-bold {{ $stockClass }}">
                                        {{ number_format($row['ending_inventory']) }}
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="9"
                                    class="px-6 py-12 text-center text-gray-500"
                                >
                                    No PPE inventory records found.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                    <tfoot class="bg-gray-100">

                        <tr class="font-bold text-gray-900">

                            <td
                                colspan="4"
                                class="px-5 py-4 text-right"
                            >
                                Total
                            </td>

                            <td class="px-5 py-4 text-center text-blue-900">
                                {{ number_format($totals['beginning_inventory']) }}
                            </td>

                            <td class="px-5 py-4 text-center text-green-700">
                                {{ number_format($totals['received_inventory']) }}
                            </td>

                            <td class="px-5 py-4 text-center text-orange-700">
                                {{ number_format($totals['issued_inventory']) }}
                            </td>

                            <td class="px-5 py-4 text-center text-indigo-900">
                                {{ number_format($totals['actual_inventory']) }}
                            </td>

                            <td class="px-5 py-4 text-center text-gray-900">
                                {{ number_format($totals['ending_inventory']) }}
                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

        {{-- Movement History --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-gray-900 px-6 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Detailed Inventory Movements
                </h2>

                <p class="mt-1 text-sm text-gray-300">
                    Read-only receiving and project issue transactions.
                </p>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-100">

                        <tr class="text-xs font-semibold uppercase tracking-wide text-gray-700">

                            <th class="px-5 py-4 text-left">
                                Date
                            </th>

                            <th class="px-5 py-4 text-left">
                                Province
                            </th>

                            <th class="px-5 py-4 text-left">
                                Reference
                            </th>

                            <th class="px-5 py-4 text-left">
                                Type
                            </th>

                            <th class="px-5 py-4 text-left">
                                PPE Item
                            </th>

                            <th class="px-5 py-4 text-left">
                                Size
                            </th>

                            <th class="px-5 py-4 text-center">
                                Quantity
                            </th>

                            <th class="px-5 py-4 text-left">
                                Call-Off / DR / Project
                            </th>

                            <th class="px-5 py-4 text-left">
                                Description
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($movements as $movement)

                            @php
                                $receipt =
                                    $movement->deliveryReceipt;

                                $allocation =
                                    $receipt?->provinceDistribution;

                                $callOff =
                                    $allocation
                                        ?->distributionBatch
                                        ?->callOff;

                                $purchaseOrder =
                                    $allocation
                                        ?->distributionBatch
                                        ?->purchaseOrder;

                                $designation =
                                    $movement->supplyDesignation;

                                $isStockIn =
                                    $movement->isStockIn();
                            @endphp

                            <tr class="hover:bg-gray-50">

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                                    {{ $movement->movement_date?->format('F d, Y') }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900">
                                    {{ $movement->province?->name ?? 'Not available' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-900">
                                    {{ $movement->reference_number ?: 'No reference' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">

                                    @if($isStockIn)

                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                            {{ $movement->movement_type }}
                                        </span>

                                    @else

                                        <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-800">
                                            {{ $movement->movement_type }}
                                        </span>

                                    @endif

                                </td>

                                <td class="px-5 py-4 font-medium text-gray-900">
                                    {{ $movement->item?->item_name ?? 'Not available' }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $movement->item?->label ?: '—' }}
                                </td>

                                <td class="px-5 py-4 text-center">

                                    @if($isStockIn)

                                        <span class="font-bold text-green-700">
                                            +{{ number_format($movement->quantity) }}
                                        </span>

                                    @else

                                        <span class="font-bold text-orange-700">
                                            -{{ number_format($movement->quantity) }}
                                        </span>

                                    @endif

                                </td>

                                <td class="min-w-56 px-5 py-4 text-sm text-gray-700">

                                    @if($receipt)

                                        <div>
                                            <strong>Call-Off:</strong>
                                            {{ $callOff?->call_off_number ?? 'N/A' }}
                                        </div>

                                        <div>
                                            <strong>DR:</strong>
                                            {{ $receipt->dr_number }}
                                        </div>

                                        <div>
                                            <strong>PO:</strong>
                                            {{ $purchaseOrder?->po_number ?? 'N/A' }}
                                        </div>

                                    @elseif($designation)

                                        <div>
                                            <strong>Project:</strong>
                                            {{ $designation->project_code }}
                                        </div>

                                        <div>
                                            {{ $designation->project_title }}
                                        </div>

                                    @else

                                        Adjustment

                                    @endif

                                </td>

                                <td class="min-w-64 px-5 py-4 text-sm text-gray-700">
                                    {{ $movement->description ?: 'No description' }}
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="9"
                                    class="px-6 py-12 text-center text-gray-500"
                                >
                                    No inventory movements found for the selected filters.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($movements->hasPages())

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $movements->links() }}
                </div>

            @endif

        </div>

    </div>

    <style>
        @media print {
            aside,
            nav,
            header,
            form,
            button,
            a {
                display: none !important;
            }

            body {
                background: #ffffff !important;
            }

            .shadow,
            .shadow-sm {
                box-shadow: none !important;
            }

            .rounded-2xl,
            .rounded-xl {
                border-radius: 0 !important;
            }

            .overflow-x-auto {
                overflow: visible !important;
            }

            table {
                width: 100% !important;
                font-size: 9px !important;
            }

            .max-w-7xl {
                max-width: none !important;
            }
        }
    </style>

</x-po_dashboard_layout>