<x-po_dashboard_layout>

<div class="space-y-6">

    <h1 class="text-3xl font-bold">
        Provincial Dashboard
    </h1>

    @forelse($deliveries as $purchaseOrderId => $items)

        @php
            $po = $items->first()->purchaseOrder;
        @endphp

        <div class="bg-white rounded-xl shadow border">

            <div class="bg-green-700 text-white px-6 py-4 flex justify-between">

                <div>

                    <h2 class="text-xl font-bold">
                        {{ $po->po_number }}
                    </h2>

                    <p>
                        {{ $po->supplier->supplier_name }}
                    </p>

                </div>

                <button
                    class="bg-white text-green-700 px-5 py-2 rounded-lg">

                    Receive Delivery

                </button>

            </div>

            <table class="w-full">

                <thead class="bg-gray-100">

                    <tr>

                        <th class="p-3 text-left">
                            PPE Item
                        </th>

                        <th class="p-3">
                            Qty
                        </th>

                    </tr>

                </thead>

                <tbody>

                @foreach($items as $distribution)

                    <tr>

                        <td class="border p-3">

                            {{ $distribution->item->item_name }}

                            @if($distribution->item->label)

                                ({{ $distribution->item->label }})

                            @endif

                        </td>

                        <td class="border text-center">

                            {{ $distribution->quantity }}

                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

    @empty

        <div class="bg-white rounded-xl p-10 text-center">

            No deliveries assigned.

        </div>

    @endforelse

</div>

</x-po_dashboard_layout>