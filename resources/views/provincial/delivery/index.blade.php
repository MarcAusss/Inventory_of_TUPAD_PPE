@extends('layouts.provincial_office_dashboard')

@section('dashboard-provincial_office')

    <div class="bg-white rounded-xl shadow">

        <div class="bg-red-900 text-white px-6 py-4">
            <h2 class="text-2xl font-bold">
                PPE Deliveries
            </h2>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3">
                {{ session('error') }}
            </div>
        @endif

        <table class="min-w-full">

            <thead class="bg-gray-100">

                <tr>

                    <th class="px-4 py-3 text-left">
                        PO Number
                    </th>

                    <th class="px-4 py-3 text-left">
                        Supplier
                    </th>

                    <th class="px-4 py-3 text-center">
                        Delivery Date
                    </th>

                    <th class="px-4 py-3 text-center">
                        Status
                    </th>

                    <th class="px-4 py-3 text-center">
                        Action
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($deliveries as $delivery)

                    <tr>

                        <td class="border px-4 py-3">
                            {{ $delivery->purchaseOrder->po_number }}
                        </td>

                        <td class="border px-4 py-3">
                            {{ $delivery->purchaseOrder->supplier->supplier_name }}
                        </td>

                        <td class="border text-center">
                            {{ $delivery->items->first()->delivery_date }}
                        </td>

                        <td class="border text-center">

                            @if($delivery->receipt)

                                <span class="text-green-600 font-semibold">
                                    Received
                                </span>

                            @else

                                <span class="text-yellow-600 font-semibold">
                                    Pending
                                </span>

                            @endif

                        </td>

                        <td class="border text-center">

                            <a href="{{ route('provincial.deliveries.show', $delivery->purchase_order_id) }}">
                                View Delivery
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5" class="text-center py-6">
                            No deliveries available.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

@endsection