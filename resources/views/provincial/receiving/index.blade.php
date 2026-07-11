@extends('layouts.provincial_office_dashboard')

@section('dashboard-provincial_office')

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Call-Off Allocations
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    View approved PPE allocations assigned to your Provincial Office.
                </p>
            </div>

            <a
                href="{{ route('provincial.receiving.history') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Receiving History
            </a>

        </div>

        @if(session('success'))

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>

        @endif

        @if(session('error'))

            <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
                {{ session('error') }}
            </div>

        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-red-900 px-6 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Approved Provincial Allocations
                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-100">

                        <tr class="text-xs font-semibold uppercase tracking-wide text-gray-700">

                            <th class="px-5 py-4 text-left">
                                No.
                            </th>

                            <th class="px-5 py-4 text-left">
                                Call-Off Number
                            </th>

                            <th class="px-5 py-4 text-left">
                                Source PO
                            </th>

                            <th class="px-5 py-4 text-left">
                                Supplier
                            </th>

                            <th class="px-5 py-4 text-left">
                                Scheduled Delivery
                            </th>

                            <th class="px-5 py-4 text-left">
                                Place of Delivery
                            </th>

                            <th class="px-5 py-4 text-center">
                                Status
                            </th>

                            <th class="px-5 py-4 text-center">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($allocations as $allocation)

                            @php
                                $batch = $allocation->distributionBatch;
                                $callOff = $batch?->callOff;
                                $purchaseOrder = $batch?->purchaseOrder;

                                $statusClass = match($allocation->status) {
                                    'Received' => 'bg-green-100 text-green-800',
                                    'Partially Received' => 'bg-yellow-100 text-yellow-800',
                                    'For Delivery' => 'bg-blue-100 text-blue-800',
                                    'Approved' => 'bg-indigo-100 text-indigo-800',
                                    'Cancelled' => 'bg-gray-200 text-gray-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp

                            <tr class="hover:bg-gray-50">

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                    {{ $allocations->firstItem() + $loop->index }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">

                                    <div class="font-semibold text-gray-900">
                                        {{ $callOff?->call_off_number ?? 'Not available' }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        Batch #{{ $batch?->id ?? 'N/A' }}
                                    </div>

                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-gray-900">
                                    {{ $purchaseOrder?->po_number ?? 'Not available' }}
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $purchaseOrder?->supplier?->supplier_name ?? 'Not available' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                                    {{ $allocation->scheduled_delivery_date?->format('F d, Y') ?? 'Not set' }}
                                </td>

                                <td class="min-w-64 px-5 py-4 text-sm text-gray-700">
                                    {{ $allocation->place_of_delivery ?: 'No delivery address recorded' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center">

                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $allocation->status }}
                                    </span>

                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center">

                                    <div class="flex items-center justify-center gap-2">

                                        <a
                                            href="{{ route('provincial.receiving.show', $allocation) }}"
                                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                                        >
                                            View
                                        </a>

                                        @if(
                                            !$allocation->deliveryReceipt
                                            && in_array($allocation->status, [
                                                'Approved',
                                                'For Delivery',
                                                'Partially Received',
                                            ], true)
                                        )

                                            <a
                                                href="{{ route('provincial.receiving.create', $allocation) }}"
                                                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700"
                                            >
                                                Receive
                                            </a>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="px-6 py-12 text-center text-gray-500"
                                >
                                    No approved Call-Off allocations are currently assigned to your province.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($allocations->hasPages())

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $allocations->links() }}
                </div>

            @endif

        </div>

    </div>

@endsection