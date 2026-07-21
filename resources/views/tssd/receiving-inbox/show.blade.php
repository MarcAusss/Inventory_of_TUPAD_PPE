<x-po_dashboard_layout>

    @php
        $notification = $workflowNotification;
        $receipt = $notification->deliveryReceipt;
        $allocation = $receipt?->provinceDistribution;
        $callOff = $notification->callOff;
        $purchaseOrder = $callOff?->distributionBatch?->purchaseOrder;

        $hasDiscrepancy = $receipt?->items?->contains(
            fn($item) =>
                $item->assigned_quantity
                !== $item->received_quantity
        ) ?? false;
    @endphp

    <div class="space-y-6">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ $notification->title }}
                </h1>

                <p class="mt-2 text-sm text-gray-600">
                    Review the Provincial Office receiving report and remarks.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('tssd.receiving-inbox.index') }}"
                    class="rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Back to Inbox
                </a>

                @if($notification->status !== 'Resolved')

                    <form
                        action="{{ route('tssd.receiving-inbox.resolve', $notification) }}"
                        method="POST"
                        onsubmit="return confirm('Mark this receiving notification as resolved?');"
                    >

                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="rounded-xl bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700"
                        >
                            Mark Resolved
                        </button>

                    </form>

                @endif

            </div>

        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-[#339DCB] px-7 py-5">
                <h2 class="text-xl font-semibold text-white">
                    Receiving Summary
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 p-7 sm:grid-cols-2 lg:grid-cols-4">

                <div>
                    <p class="text-sm text-gray-500">
                        Province
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $notification->province?->name ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">
                        Call-Off Number
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $callOff?->call_off_number ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">
                        Purchase Order
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $purchaseOrder?->po_number ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">
                        Supplier
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $purchaseOrder?->supplier?->supplier_name ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">
                        Delivery Receipt
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $receipt?->dr_number ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">
                        Delivery Date
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $receipt?->delivery_date?->format('F d, Y') ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">
                        Physical Receiver
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $receipt?->physical_receiver_name ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">
                        Receiving Result
                    </p>

                    @if($hasDiscrepancy)

                        <p class="mt-1 font-semibold text-yellow-700">
                            With Quantity Discrepancy
                        </p>

                    @else

                        <p class="mt-1 font-semibold text-green-700">
                            Complete Delivery
                        </p>

                    @endif
                </div>

                <div class="sm:col-span-2 lg:col-span-4">
                    <p class="text-sm text-gray-500">
                        Provincial Remarks
                    </p>

                    <p class="mt-1 whitespace-pre-line text-gray-900">
                        {{ $receipt?->remarks ?: 'No remarks provided.' }}
                    </p>
                </div>

                @if($receipt?->document)

                    <div class="sm:col-span-2 lg:col-span-4">

                        <a
                            href="{{ asset('storage/'.$receipt->document) }}"
                            target="_blank"
                            class="inline-flex rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700"
                        >
                            View Delivery Receipt PDF
                        </a>

                    </div>

                @endif

            </div>

        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-gray-900 px-7 py-5">
                <h2 class="text-xl font-semibold text-white">
                    Received PPE Comparison
                </h2>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-100">

                        <tr class="text-xs font-semibold uppercase tracking-wide text-gray-700">

                            <th class="px-5 py-4 text-left">
                                PPE Item
                            </th>

                            <th class="px-5 py-4 text-left">
                                Size
                            </th>

                            <th class="px-5 py-4 text-center">
                                Assigned
                            </th>

                            <th class="px-5 py-4 text-center">
                                Received
                            </th>

                            <th class="px-5 py-4 text-center">
                                Difference
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($receipt?->items ?? collect() as $item)

                            @php
                                $difference =
                                    $item->assigned_quantity
                                    - $item->received_quantity;
                            @endphp

                            <tr>

                                <td class="px-5 py-4 font-medium text-gray-900">
                                    {{ $item->item->item_name }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $item->item->label ?: '—' }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    {{ number_format($item->assigned_quantity) }}
                                </td>

                                <td class="px-5 py-4 text-center font-semibold">
                                    {{ number_format($item->received_quantity) }}
                                </td>

                                <td class="px-5 py-4 text-center">

                                    @if($difference === 0)

                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                            Complete
                                        </span>

                                    @else

                                        <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                            Short by {{ number_format($difference) }}
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="5"
                                    class="px-6 py-12 text-center text-gray-500"
                                >
                                    No received PPE records found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-po_dashboard_layout>