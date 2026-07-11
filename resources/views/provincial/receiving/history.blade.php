<x-po_dashboard_layout title="Provincial Office Dashboard">

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Receiving History
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    View Delivery Receipts submitted by your Provincial Office.
                </p>
            </div>

            <a
                href="{{ route('provincial.receiving.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Back to Allocations
            </a>

        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-red-900 px-6 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Submitted Delivery Receipts
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
                                Delivery Receipt
                            </th>

                            <th class="px-5 py-4 text-left">
                                Delivery Date
                            </th>

                            <th class="px-5 py-4 text-left">
                                Supplier
                            </th>

                            <th class="px-5 py-4 text-left">
                                Receiver
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

                        @forelse($receipts as $receipt)

                            @php
                                $allocation = $receipt->provinceDistribution;
                                $batch = $allocation?->distributionBatch;
                                $callOff = $batch?->callOff;
                                $purchaseOrder = $batch?->purchaseOrder;

                                $hasDiscrepancy = $receipt->items->contains(
                                    fn($item) =>
                                        $item->assigned_quantity
                                        !== $item->received_quantity
                                );
                            @endphp

                            <tr class="hover:bg-gray-50">

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                    {{ $receipts->firstItem() + $loop->index }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-900">
                                    {{ $callOff?->call_off_number ?? 'Not available' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">

                                    <div class="font-medium text-gray-900">
                                        {{ $receipt->dr_number }}
                                    </div>

                                    @if($receipt->document)

                                        <a
                                            href="{{ asset('storage/'.$receipt->document) }}"
                                            target="_blank"
                                            class="mt-1 inline-block text-xs font-semibold text-blue-600 hover:text-blue-800"
                                        >
                                            View PDF
                                        </a>

                                    @endif

                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                                    {{ $receipt->delivery_date?->format('F d, Y') }}
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $purchaseOrder?->supplier?->supplier_name ?? 'Not available' }}
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $receipt->physical_receiver_name }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center">

                                    @if($hasDiscrepancy)

                                        <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                            With Discrepancy
                                        </span>

                                    @else

                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                            Complete
                                        </span>

                                    @endif

                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center">

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
                                    colspan="8"
                                    class="px-6 py-12 text-center text-gray-500"
                                >
                                    No Delivery Receipts have been submitted yet.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($receipts->hasPages())

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $receipts->links() }}
                </div>

            @endif

        </div>

    </div>

</x-po_dashboard_layout>