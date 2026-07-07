<x-po_dashboard_layout>


    <div class="max-w-7xl mx-auto p-8">

        <h1 class="text-3xl font-bold mb-6">
            Delivery Receipt
        </h1>

        <div class="bg-white rounded-xl shadow p-6">

            <p>
                Purchase Order:
                <strong>{{ $distribution->purchaseOrder->po_number }}</strong>
            </p>

            <p>
                Supplier:
                <strong>{{ $distribution->purchaseOrder->supplier->supplier_name }}</strong>
            </p>

            <p>
                Province:
                <strong>{{ $distribution->province->province_name }}</strong>
            </p>

            <hr class="my-6">

            <table class="w-full border">

                <thead class="bg-gray-100">

                    <tr>

                        <th class="border p-2">Item</th>

                        <th class="border p-2">Quantity</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($items as $item)

                        <tr>

                            <td class="border p-2">

                                {{ $item->item->item_name }}

                                @if($item->item->label)
                                    ({{ $item->item->label }})
                                @endif

                            </td>

                            <td class="border p-2">

                                {{ $item->quantity }}

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>
            @if(!$receipt)

                <a href="{{ route('provincial.deliveries.receive', $distribution->purchase_order_id) }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded">

                    Receive Delivery

                </a>

            @else

                <span class="bg-green-600 text-white px-4 py-2 rounded">

                    Delivery Received

                </span>

            @endif
        </div>

    </div>

</x-po_dashboard_layout>