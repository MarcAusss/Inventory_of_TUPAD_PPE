<x-po_dashboard_layout>

<div class="bg-white rounded-xl shadow">

    <div class="bg-red-900 text-white px-6 py-4">

        <h2 class="text-2xl font-bold">
            PPE Deliveries
        </h2>

    </div>

    <table class="min-w-full">

        <thead class="bg-gray-100">

            <tr>

                <th class="px-4 py-3 text-left">
                    PO Number
                </th>

                <th class="px-4 py-3 text-left">
                    Supplier
                </th>

                <th class="px-4 py-3">
                    Delivery Date
                </th>

                <th class="px-4 py-3">
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

                    {{ $delivery->delivery_date }}

                </td>

                <td class="border text-center">

                    <a
                        href="{{ route('provincial.show', $delivery->purchase_order_id) }}"
                        class="text-blue-600 underline">

                        View Delivery

                    </a>

                </td>

            </tr>

        @empty

            <tr>

                <td colspan="4" class="text-center py-8">

                    No deliveries yet.

                </td>

            </tr>

        @endforelse

        </tbody>

    </table>

</div>

</x-po_dashboard_layout>