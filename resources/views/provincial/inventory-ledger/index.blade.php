<x-po_dashboard_layout title="Provincial Office Dashboard">
    @php
        $summaryTotals = [
            'beginning_inventory' => collect($summary)
                ->sum('beginning_inventory'),

            'received_inventory' => collect($summary)
                ->sum('received_inventory'),

            'issued_inventory' => collect($summary)
                ->sum('issued_inventory'),

            'actual_inventory' => collect($summary)
                ->sum('actual_inventory'),

            'ending_inventory' => collect($summary)
                ->sum('ending_inventory'),
        ];
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-900">
                    Inventory Ledger
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Beginning, received, issued, actual, and ending PPE inventory for

                    <span class="font-semibold text-gray-900">
                        {{ auth()->user()->provinceName() }}
                    </span>.
                </p>

            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('provincial.current-inventory.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Current Inventory
                </a>

                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center justify-center rounded-xl bg-red-900 px-5 py-3 font-semibold text-white transition hover:bg-red-800"
                >
                    Print Ledger
                </button>

            </div>

        </div>

        {{-- Year Notice --}}
        @if($year === $currentYear)

            <div class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 text-blue-800">

                You are viewing the current inventory year:

                <span class="font-semibold">
                    {{ $year }}
                </span>.

            </div>

        @else

            <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-5 py-4 text-yellow-900">

                You are viewing archived inventory data for:

                <span class="font-semibold">
                    {{ $year }}
                </span>.

                Historical data remains in the same database.

            </div>

        @endif

        {{-- Filters --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

            <form
                action="{{ route('provincial.inventory-ledger.index') }}"
                method="GET"
                class="grid grid-cols-1 gap-4 md:grid-cols-4"
            >

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
                                @selected((int) $availableYear === (int) $year)
                            >
                                {{ $availableYear }}

                                @if((int) $availableYear === (int) $currentYear)
                                    — Current
                                @else
                                    — Archive
                                @endif
                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="md:col-span-2">

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
                        placeholder="Search PPE, DR number, project code, or description..."
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

                    @if($search || $year !== $currentYear)

                        <a
                            href="{{ route('provincial.inventory-ledger.index') }}"
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

            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-6 shadow-sm">

                <p class="text-sm font-medium text-blue-700">
                    Beginning Inventory
                </p>

                <p class="mt-3 text-3xl font-bold text-blue-900">
                    {{ number_format($summaryTotals['beginning_inventory']) }}
                </p>

                <p class="mt-1 text-xs text-blue-700">
                    Balance before January 1, {{ $year }}
                </p>

            </div>

            <div class="rounded-2xl border border-green-200 bg-green-50 p-6 shadow-sm">

                <p class="text-sm font-medium text-green-700">
                    Received Inventory
                </p>

                <p class="mt-3 text-3xl font-bold text-green-900">
                    {{ number_format($summaryTotals['received_inventory']) }}
                </p>

                <p class="mt-1 text-xs text-green-700">
                    PPE received during {{ $year }}
                </p>

            </div>

            <div class="rounded-2xl border border-orange-200 bg-orange-50 p-6 shadow-sm">

                <p class="text-sm font-medium text-orange-700">
                    Issued Inventory
                </p>

                <p class="mt-3 text-3xl font-bold text-orange-900">
                    {{ number_format($summaryTotals['issued_inventory']) }}
                </p>

                <p class="mt-1 text-xs text-orange-700">
                    PPE distributed to projects
                </p>

            </div>

            <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-6 shadow-sm">

                <p class="text-sm font-medium text-indigo-700">
                    Actual Inventory
                </p>

                <p class="mt-3 text-3xl font-bold text-indigo-900">
                    {{ number_format($summaryTotals['actual_inventory']) }}
                </p>

                <p class="mt-1 text-xs text-indigo-700">
                    Remaining PPE after project issues
                </p>

            </div>

            <div class="rounded-2xl border border-gray-300 bg-gray-100 p-6 shadow-sm">

                <p class="text-sm font-medium text-gray-700">
                    Ending Inventory
                </p>

                <p class="mt-3 text-3xl font-bold text-gray-900">
                    {{ number_format($summaryTotals['ending_inventory']) }}
                </p>

                <p class="mt-1 text-xs text-gray-600">
                    Carries forward to the next year
                </p>

            </div>

        </div>

        {{-- Inventory Summary Table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-red-900 px-6 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Annual Inventory Summary — {{ $year }}
                </h2>

                <p class="mt-1 text-sm text-red-100">
                    Ending Inventory becomes the next year’s Beginning Inventory.
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
                                Beginning Inventory
                            </th>

                            <th class="px-5 py-4 text-center">
                                Received
                            </th>

                            <th class="px-5 py-4 text-center">
                                Issued
                            </th>

                            <th class="px-5 py-4 text-center">
                                Actual Inventory
                            </th>

                            <th class="px-5 py-4 text-center">
                                Ending Inventory
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($summary as $row)

                            @php
                                $endingQuantity =
                                    (int) $row['ending_inventory'];

                                $stockClass = match (true) {
                                    $endingQuantity <= 0 =>
                                        'bg-red-100 text-red-800',

                                    $endingQuantity <= 10 =>
                                        'bg-yellow-100 text-yellow-800',

                                    default =>
                                        'bg-green-100 text-green-800',
                                };
                            @endphp

                            <tr class="hover:bg-gray-50">

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
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
                                    No PPE items were found.
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
                                {{ number_format($summaryTotals['beginning_inventory']) }}
                            </td>

                            <td class="px-5 py-4 text-center text-green-700">
                                {{ number_format($summaryTotals['received_inventory']) }}
                            </td>

                            <td class="px-5 py-4 text-center text-orange-700">
                                {{ number_format($summaryTotals['issued_inventory']) }}
                            </td>

                            <td class="px-5 py-4 text-center text-indigo-900">
                                {{ number_format($summaryTotals['actual_inventory']) }}
                            </td>

                            <td class="px-5 py-4 text-center text-gray-900">
                                {{ number_format($summaryTotals['ending_inventory']) }}
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
                    Inventory Movement History
                </h2>

                <p class="mt-1 text-sm text-gray-300">
                    Detailed PPE receiving and project distribution records for {{ $year }}.
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
                                Date
                            </th>

                            <th class="px-5 py-4 text-left">
                                Reference
                            </th>

                            <th class="px-5 py-4 text-left">
                                Movement
                            </th>

                            <th class="px-5 py-4 text-left">
                                PPE Item
                            </th>

                            <th class="px-5 py-4 text-left">
                                Size / Label
                            </th>

                            <th class="px-5 py-4 text-center">
                                Quantity
                            </th>

                            <th class="px-5 py-4 text-left">
                                Description
                            </th>

                            <th class="px-5 py-4 text-left">
                                Recorded By
                            </th>

                            <th class="px-5 py-4 text-center">
                                Source
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($movements as $movement)

                            @php
                                $isStockIn = $movement->isStockIn();

                                $movementClass = $isStockIn
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-orange-100 text-orange-800';
                            @endphp

                            <tr class="hover:bg-gray-50">

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                    {{ $movements->firstItem() + $loop->index }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                                    {{ $movement->movement_date?->format('F d, Y') }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">

                                    <div class="font-semibold text-gray-900">
                                        {{ $movement->reference_number ?: 'No reference' }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        Record #{{ $movement->id }}
                                    </div>

                                </td>

                                <td class="whitespace-nowrap px-5 py-4">

                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $movementClass }}">
                                        {{ $movement->movement_type }}
                                    </span>

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

                                <td class="min-w-64 px-5 py-4 text-sm text-gray-700">
                                    {{ $movement->description ?: 'No description' }}
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $movement->creator?->name ?? 'System' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center">

                                    @if($movement->deliveryReceipt)

                                        @php
                                            $allocation =
                                                $movement
                                                    ->deliveryReceipt
                                                    ?->provinceDistribution;
                                        @endphp

                                        @if($allocation)

                                            <a
                                                href="{{ route('provincial.receiving.show', $allocation) }}"
                                                class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700"
                                            >
                                                Delivery Receipt
                                            </a>

                                        @else

                                            <span class="text-xs text-gray-500">
                                                Delivery Receipt
                                            </span>

                                        @endif

                                    @elseif($movement->supplyDesignation)

                                        <a
                                            href="{{ route('provincial.project-designations.show', $movement->supplyDesignation) }}"
                                            class="rounded-lg bg-purple-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-purple-700"
                                        >
                                            Project
                                        </a>

                                    @else

                                        <span class="text-xs text-gray-500">
                                            Adjustment
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="10"
                                    class="px-6 py-12 text-center text-gray-500"
                                >
                                    No inventory movements were recorded for {{ $year }}.
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

    {{-- Basic browser print styling. --}}
    <style>
        @media print {
            aside,
            nav,
            header,
            button,
            form,
            a {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .shadow,
            .shadow-sm {
                box-shadow: none !important;
            }

            .rounded-2xl,
            .rounded-xl {
                border-radius: 0 !important;
            }

            table {
                width: 100% !important;
                font-size: 10px !important;
            }

            .overflow-x-auto {
                overflow: visible !important;
            }
        }
    </style>

</x-po_dashboard_layout>