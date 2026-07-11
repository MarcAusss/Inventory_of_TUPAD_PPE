@extends('layouts.provincial_office_dashboard')

@section('dashboard-provincial_office')

    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-900">
                    Current Inventory
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Available PPE inventory for
                    <span class="font-semibold text-gray-900">
                        {{ auth()->user()->provinceName() }}
                    </span>.
                </p>

            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('provincial.receiving.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Call-Off Allocations
                </a>

                <a
                    href="{{ route('provincial.receiving.history') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-red-900 px-5 py-3 font-semibold text-white transition hover:bg-red-800"
                >
                    Receiving History
                </a>

            </div>

        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

                <p class="text-sm font-medium text-gray-500">
                    Total Available PPE
                </p>

                <p class="mt-3 text-3xl font-bold text-gray-900">
                    {{ number_format($totalQuantity) }}
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    Combined quantity of all PPE
                </p>

            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

                <p class="text-sm font-medium text-gray-500">
                    Available PPE Types
                </p>

                <p class="mt-3 text-3xl font-bold text-gray-900">
                    {{ number_format($availableItemTypes) }}
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    PPE variants with stock above zero
                </p>

            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

                <p class="text-sm font-medium text-gray-500">
                    Recent Receipts
                </p>

                <p class="mt-3 text-3xl font-bold text-gray-900">
                    {{ number_format($recentReceipts->count()) }}
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    Most recent Delivery Receipt records
                </p>

            </div>

        </div>

        {{-- PPE Summary --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-red-900 px-6 py-5">

                <h2 class="text-xl font-semibold text-white">
                    PPE Inventory Summary
                </h2>

            </div>

            <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">

                <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 text-center">

                    <p class="text-sm font-medium text-blue-700">
                        Long Sleeve M
                    </p>

                    <p class="mt-2 text-2xl font-bold text-blue-900">
                        {{ number_format($summary['long_sleeve_medium']) }}
                    </p>

                </div>

                <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-5 text-center">

                    <p class="text-sm font-medium text-indigo-700">
                        Long Sleeve L
                    </p>

                    <p class="mt-2 text-2xl font-bold text-indigo-900">
                        {{ number_format($summary['long_sleeve_large']) }}
                    </p>

                </div>

                <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-5 text-center">

                    <p class="text-sm font-medium text-yellow-700">
                        Bucket Hat
                    </p>

                    <p class="mt-2 text-2xl font-bold text-yellow-900">
                        {{ number_format($summary['bucket_hat']) }}
                    </p>

                </div>

                <div class="rounded-xl border border-orange-200 bg-orange-50 p-5 text-center">

                    <p class="text-sm font-medium text-orange-700">
                        Boots US9
                    </p>

                    <p class="mt-2 text-2xl font-bold text-orange-900">
                        {{ number_format($summary['rubber_boots_us9']) }}
                    </p>

                </div>

                <div class="rounded-xl border border-red-200 bg-red-50 p-5 text-center">

                    <p class="text-sm font-medium text-red-700">
                        Boots US10
                    </p>

                    <p class="mt-2 text-2xl font-bold text-red-900">
                        {{ number_format($summary['rubber_boots_us10']) }}
                    </p>

                </div>

                <div class="rounded-xl border border-green-200 bg-green-50 p-5 text-center">

                    <p class="text-sm font-medium text-green-700">
                        Hand Gloves
                    </p>

                    <p class="mt-2 text-2xl font-bold text-green-900">
                        {{ number_format($summary['hand_gloves']) }}
                    </p>

                </div>

                <div class="rounded-xl border border-gray-300 bg-gray-100 p-5 text-center">

                    <p class="text-sm font-medium text-gray-700">
                        Mask
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ number_format($summary['mask']) }}
                    </p>

                </div>

            </div>

        </div>

        {{-- Inventory Table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="flex flex-col gap-4 border-b border-gray-200 bg-gray-900 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <h2 class="text-xl font-semibold text-white">
                        Current PPE Stock
                    </h2>

                    <p class="mt-1 text-sm text-gray-300">
                        Quantities update automatically after receiving and project distribution.
                    </p>

                </div>

                <form
                    action="{{ route('provincial.current-inventory.index') }}"
                    method="GET"
                    class="flex w-full max-w-md gap-2"
                >

                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search PPE item..."
                        class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                    >

                    <button
                        type="submit"
                        class="rounded-xl bg-red-900 px-5 py-2 font-semibold text-white transition hover:bg-red-800"
                    >
                        Search
                    </button>

                    @if($search)

                        <a
                            href="{{ route('provincial.current-inventory.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700"
                        >
                            Clear
                        </a>

                    @endif

                </form>

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
                                Available Quantity
                            </th>

                            <th class="px-5 py-4 text-center">
                                Stock Status
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($inventories as $inventory)

                            <tr class="hover:bg-gray-50">

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                    {{ $inventories->firstItem() + $loop->index }}
                                </td>

                                <td class="px-5 py-4 font-semibold text-gray-900">
                                    {{ $inventory->item->item_name }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $inventory->item->label ?: '—' }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $inventory->item->unit_of_measurement }}
                                </td>

                                <td class="px-5 py-4 text-center text-lg font-bold text-gray-900">
                                    {{ number_format($inventory->quantity) }}
                                </td>

                                <td class="px-5 py-4 text-center">

                                    @if($inventory->quantity <= 0)

                                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                            Out of Stock
                                        </span>

                                    @elseif($inventory->quantity <= 10)

                                        <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                            Low Stock
                                        </span>

                                    @else

                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                            Available
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center text-gray-500"
                                >
                                    No inventory records found.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($inventories->hasPages())

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $inventories->links() }}
                </div>

            @endif

        </div>

        {{-- Recent Receiving --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-red-900 px-6 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Recent Inventory Additions
                </h2>

                <p class="mt-1 text-sm text-red-100">
                    Recent Delivery Receipts that increased provincial inventory.
                </p>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-100">

                        <tr class="text-xs font-semibold uppercase tracking-wide text-gray-700">

                            <th class="px-5 py-4 text-left">
                                DR Number
                            </th>

                            <th class="px-5 py-4 text-left">
                                Call-Off Number
                            </th>

                            <th class="px-5 py-4 text-left">
                                Supplier
                            </th>

                            <th class="px-5 py-4 text-left">
                                Delivery Date
                            </th>

                            <th class="px-5 py-4 text-center">
                                Total Received
                            </th>

                            <th class="px-5 py-4 text-center">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($recentReceipts as $receipt)

                            @php
                                $allocation = $receipt->provinceDistribution;
                                $batch = $allocation?->distributionBatch;
                                $callOff = $batch?->callOff;
                                $purchaseOrder = $batch?->purchaseOrder;
                            @endphp

                            <tr class="hover:bg-gray-50">

                                <td class="px-5 py-4 font-semibold text-gray-900">
                                    {{ $receipt->dr_number }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $callOff?->call_off_number ?? 'Not available' }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $purchaseOrder?->supplier?->supplier_name ?? 'Not available' }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $receipt->delivery_date?->format('F d, Y') }}
                                </td>

                                <td class="px-5 py-4 text-center font-semibold text-gray-900">
                                    {{ number_format($receipt->items->sum('received_quantity')) }}
                                </td>

                                <td class="px-5 py-4 text-center">

                                    @if($allocation)

                                        <a
                                            href="{{ route('provincial.receiving.show', $allocation) }}"
                                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                                        >
                                            View
                                        </a>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="px-6 py-10 text-center text-gray-500"
                                >
                                    No receiving records found.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection