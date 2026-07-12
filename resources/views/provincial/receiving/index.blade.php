<x-po_dashboard_layout title="Provincial Office Dashboard">

    <div class="mx-auto max-w-7xl space-y-6">

        <div
            class="flex flex-col gap-4 sm:flex-row
                   sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Call-Off Allocations
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    View approved PPE allocations and record one or more
                    Delivery Receipts for your Provincial Office.
                </p>
            </div>

            <a
                href="{{ route('provincial.receiving.history') }}"
                class="inline-flex items-center justify-center rounded-xl
                       border border-gray-300 bg-white px-5 py-3
                       font-semibold text-gray-700 transition
                       hover:bg-gray-50"
            >
                Receiving History
            </a>
        </div>

        @if(session('success'))
            <div
                class="rounded-xl border border-green-200 bg-green-50
                       px-5 py-4 text-green-800"
            >
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div
                class="rounded-xl border border-red-200 bg-red-50
                       px-5 py-4 text-red-800"
            >
                {{ session('error') }}
            </div>
        @endif

        <div
            class="overflow-hidden rounded-2xl border border-gray-200
                   bg-white shadow"
        >
            <div class="bg-red-900 px-6 py-5">
                <h2 class="text-xl font-semibold text-white">
                    Approved Provincial Allocations
                </h2>

                <p class="mt-1 text-sm text-red-100">
                    Partially received allocations remain available until
                    all PPE quantities are completely received.
                </p>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-[1100px] w-full divide-y divide-gray-200">

                    <thead class="bg-gray-100">

                        <tr
                            class="text-xs font-semibold uppercase
                                   tracking-wide text-gray-700"
                        >
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

                            <th class="px-5 py-4 text-center">
                                Allocation
                            </th>

                            <th class="px-5 py-4 text-center">
                                Received
                            </th>

                            <th class="px-5 py-4 text-center">
                                Remaining
                            </th>

                            <th class="px-5 py-4 text-center">
                                DR Count
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

                                $allocatedTotal = (int) $allocation
                                    ->items
                                    ->sum('quantity');

                                $receivedTotal = (int) $allocation
                                    ->deliveryReceipts
                                    ->flatMap(
                                        fn ($receipt) => $receipt->items
                                    )
                                    ->sum('received_quantity');

                                $remainingTotal = max(
                                    0,
                                    $allocatedTotal - $receivedTotal
                                );

                                $fullyReceived = $remainingTotal <= 0;

                                $canReceive = ! $fullyReceived
                                    && in_array(
                                        $allocation->status,
                                        [
                                            'Approved',
                                            'For Delivery',
                                            'Partially Received',
                                        ],
                                        true
                                    );

                                $statusClass = match(
                                    $allocation->status
                                ) {
                                    'Received' =>
                                        'bg-green-100 text-green-800',

                                    'Partially Received' =>
                                        'bg-yellow-100 text-yellow-800',

                                    'For Delivery' =>
                                        'bg-blue-100 text-blue-800',

                                    'Approved' =>
                                        'bg-indigo-100 text-indigo-800',

                                    'Cancelled' =>
                                        'bg-gray-200 text-gray-700',

                                    default =>
                                        'bg-gray-100 text-gray-700',
                                };

                                $percentage = $allocatedTotal > 0
                                    ? min(
                                        100,
                                        round(
                                            ($receivedTotal / $allocatedTotal)
                                            * 100
                                        )
                                    )
                                    : 0;
                            @endphp

                            <tr class="align-top transition hover:bg-gray-50">

                                <td
                                    class="whitespace-nowrap px-5 py-5
                                           text-sm text-gray-600"
                                >
                                    {{
                                        $allocations->firstItem()
                                        + $loop->index
                                    }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-5">
                                    <div class="font-semibold text-gray-900">
                                        {{
                                            $callOff?->call_off_number
                                            ?? 'Not available'
                                        }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        Batch #{{ $batch?->id ?? 'N/A' }}
                                    </div>
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-5
                                           text-sm font-medium text-gray-900"
                                >
                                    {{
                                        $purchaseOrder?->po_number
                                        ?? 'Not available'
                                    }}
                                </td>

                                <td
                                    class="min-w-48 px-5 py-5
                                           text-sm text-gray-700"
                                >
                                    {{
                                        $purchaseOrder
                                            ?->supplier
                                            ?->supplier_name
                                        ?? 'Not available'
                                    }}
                                </td>

                                <td
                                    class="px-5 py-5 text-center
                                           font-semibold text-gray-900"
                                >
                                    {{ number_format($allocatedTotal) }}
                                </td>

                                <td
                                    class="px-5 py-5 text-center
                                           font-semibold text-blue-700"
                                >
                                    {{ number_format($receivedTotal) }}
                                </td>

                                <td
                                    class="px-5 py-5 text-center
                                           font-bold
                                           {{
                                               $remainingTotal > 0
                                                   ? 'text-amber-700'
                                                   : 'text-green-700'
                                           }}"
                                >
                                    {{ number_format($remainingTotal) }}
                                </td>

                                <td class="px-5 py-5 text-center">
                                    <span
                                        class="inline-flex min-w-8
                                               justify-center rounded-full
                                               bg-gray-100 px-3 py-1
                                               text-xs font-bold
                                               text-gray-700"
                                    >
                                        {{
                                            number_format(
                                                $allocation
                                                    ->deliveryReceipts
                                                    ->count()
                                            )
                                        }}
                                    </span>
                                </td>

                                <td class="min-w-44 px-5 py-5">

                                    <span
                                        class="inline-flex rounded-full
                                               px-3 py-1 text-xs
                                               font-semibold
                                               {{ $statusClass }}"
                                    >
                                        {{ $allocation->status }}
                                    </span>

                                    <div
                                        class="mt-3 h-2 overflow-hidden
                                               rounded-full bg-gray-200"
                                    >
                                        <div
                                            class="h-full rounded-full
                                                   bg-red-900"
                                            style="width: {{ $percentage }}%"
                                        ></div>
                                    </div>

                                    <p
                                        class="mt-1 text-right text-xs
                                               font-semibold text-gray-500"
                                    >
                                        {{ $percentage }}%
                                    </p>

                                </td>

                                <td class="px-5 py-5 text-center">

                                    <div
                                        class="flex flex-wrap items-center
                                               justify-center gap-2"
                                    >
                                        <a
                                            href="{{ route(
                                                'provincial.receiving.show',
                                                $allocation
                                            ) }}"
                                            class="rounded-lg bg-blue-600
                                                   px-4 py-2 text-sm
                                                   font-semibold text-white
                                                   transition
                                                   hover:bg-blue-700"
                                        >
                                            View
                                        </a>

                                        @if($canReceive)
                                            <a
                                                href="{{ route(
                                                    'provincial.receiving.create',
                                                    $allocation
                                                ) }}"
                                                class="rounded-lg bg-green-600
                                                       px-4 py-2 text-sm
                                                       font-semibold text-white
                                                       transition
                                                       hover:bg-green-700"
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
                                    colspan="10"
                                    class="px-6 py-12 text-center
                                           text-gray-500"
                                >
                                    No approved Call-Off allocations are
                                    currently assigned to your province.
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

</x-po_dashboard_layout>