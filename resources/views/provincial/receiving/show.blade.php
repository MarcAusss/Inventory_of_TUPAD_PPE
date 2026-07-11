<x-po_dashboard_layout title="Provincial Office Dashboard">

    @php
        $batch = $provinceDistribution->distributionBatch;
        $callOff = $batch?->callOff;
        $purchaseOrder = $batch?->purchaseOrder;
        $receipt = $provinceDistribution->deliveryReceipt;

        $statusClass = match($provinceDistribution->status) {
            'Received' => 'bg-green-100 text-green-800',
            'Partially Received' => 'bg-yellow-100 text-yellow-800',
            'For Delivery' => 'bg-blue-100 text-blue-800',
            'Approved' => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-700',
        };
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $callOff?->call_off_number ?? 'Call-Off Allocation' }}
                    </h1>

                    <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $statusClass }}">
                        {{ $provinceDistribution->status }}
                    </span>

                </div>

                <p class="mt-2 text-sm text-gray-600">
                    Provincial PPE allocation for {{ $provinceDistribution->province->name }}.
                </p>

            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('provincial.receiving.index') }}"
                    class="rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Back to Allocations
                </a>

                @if(
                    !$receipt
                    && in_array($provinceDistribution->status, [
                        'Approved',
                        'For Delivery',
                        'Partially Received',
                    ], true)
                )

                    <a
                        href="{{ route('provincial.receiving.create', $provinceDistribution) }}"
                        class="rounded-xl bg-green-600 px-5 py-3 font-semibold text-white transition hover:bg-green-700"
                    >
                        Receive Delivery
                    </a>

                @endif

            </div>

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

            <div class="bg-red-900 px-7 py-5">
                <h2 class="text-xl font-semibold text-white">
                    Call-Off and Purchase Order
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 p-7 sm:grid-cols-2 lg:grid-cols-4">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Call-Off Number
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $callOff?->call_off_number ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Official Call-Off Date
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $callOff?->call_off_date?->format('F d, Y') ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Source Purchase Order
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $purchaseOrder?->po_number ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Supplier
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $purchaseOrder?->supplier?->supplier_name ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Province
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $provinceDistribution->province->name }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Scheduled Delivery
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $provinceDistribution->scheduled_delivery_date?->format('F d, Y') ?? 'Not set' }}
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <p class="text-sm font-medium text-gray-500">
                        Place of Delivery
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $provinceDistribution->place_of_delivery ?: 'No delivery location recorded' }}
                    </p>
                </div>

            </div>

        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-gray-900 px-7 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Assigned PPE
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
                                Size / Label
                            </th>

                            <th class="px-5 py-4 text-left">
                                Unit
                            </th>

                            <th class="px-5 py-4 text-center">
                                Assigned Quantity
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @foreach($provinceDistribution->items as $allocationItem)

                            <tr>

                                <td class="px-5 py-4 font-medium text-gray-900">
                                    {{ $allocationItem->item->item_name }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $allocationItem->item->label ?: '—' }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $allocationItem->item->unit_of_measurement }}
                                </td>

                                <td class="px-5 py-4 text-center font-semibold text-gray-900">
                                    {{ number_format($allocationItem->quantity) }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

        @if($receipt)

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

                <div class="bg-green-700 px-7 py-5">

                    <h2 class="text-xl font-semibold text-white">
                        Delivery Receipt
                    </h2>

                </div>

                <div class="grid grid-cols-1 gap-6 p-7 sm:grid-cols-2 lg:grid-cols-4">

                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            DR Number
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $receipt->dr_number }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Delivery Date
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $receipt->delivery_date?->format('F d, Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Physical Receiver
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $receipt->physical_receiver_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Submitted By
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $receipt->receivedByUser?->name ?? 'Not available' }}
                        </p>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <p class="text-sm font-medium text-gray-500">
                            Remarks
                        </p>

                        <p class="mt-1 whitespace-pre-line text-gray-900">
                            {{ $receipt->remarks ?: 'No remarks provided.' }}
                        </p>
                    </div>

                    <div>

                        @if($receipt->document)

                            <a
                                href="{{ asset('storage/'.$receipt->document) }}"
                                target="_blank"
                                class="inline-flex rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-700"
                            >
                                View DR Document
                            </a>

                        @endif

                    </div>

                </div>

                <div class="overflow-x-auto border-t border-gray-200">

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

                            @foreach($receipt->items as $receiptItem)

                                @php
                                    $difference =
                                        $receiptItem->assigned_quantity
                                        - $receiptItem->received_quantity;
                                @endphp

                                <tr>

                                    <td class="px-5 py-4 font-medium text-gray-900">
                                        {{ $receiptItem->item->item_name }}
                                    </td>

                                    <td class="px-5 py-4 text-gray-700">
                                        {{ $receiptItem->item->label ?: '—' }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        {{ number_format($receiptItem->assigned_quantity) }}
                                    </td>

                                    <td class="px-5 py-4 text-center font-semibold">
                                        {{ number_format($receiptItem->received_quantity) }}
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

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        @endif

    </div>

</x-po_dashboard_layout>