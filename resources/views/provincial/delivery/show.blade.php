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

                <tr>

                    <td class="border p-2">
                        {{ $distribution->item->item_name }}

                        @if($distribution->item->label)
                            ({{ $distribution->item->label }})
                        @endif
                    </td>

                    <td class="border p-2">
                        {{ $distribution->quantity }}
                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

</x-po_dashboard_layout>