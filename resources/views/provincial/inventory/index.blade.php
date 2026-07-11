<x-po_dashboard_layout title="Provincial Office Dashboard">

<div class="bg-white rounded-xl shadow">

    <div class="bg-red-900 text-white px-6 py-4">

        <h2 class="text-2xl font-bold">
            Provincial Inventory
        </h2>

    </div>

    <table class="min-w-full">

        <thead class="bg-gray-100">

            <tr>

                <th class="px-4 py-3 text-left">
                    DR Number
                </th>

                <th class="px-4 py-3 text-left">
                    Purchase Order
                </th>

                <th class="px-4 py-3 text-left">
                    Supplier
                </th>

                <th class="px-4 py-3 text-center">
                    Delivery Date
                </th>

                <th class="px-4 py-3 text-center">
                    Action
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($receipts as $receipt)

                <tr>

                    <td class="border px-4 py-3">
                        {{ $receipt->dr_number }}
                    </td>

                    <td class="border px-4 py-3">
                        {{ $receipt->purchaseOrder->po_number }}
                    </td>

                    <td class="border px-4 py-3">
                        {{ $receipt->purchaseOrder->supplier->supplier_name }}
                    </td>

                    <td class="border text-center">
                        {{ $receipt->delivery_date->format('F d, Y') }}
                    </td>

                    <td class="border text-center">

                        <a
                            href="{{ route('provincial.inventory.show', $receipt->id) }}"
                            class="text-blue-600 underline">

                            View

                        </a>

                        |

                        <a
                            href="{{ route('provincial.inventory.designate', $receipt->id) }}"
                            class="text-green-600 underline">

                            Designate

                        </a>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="5" class="text-center py-8">

                        No Delivery Receipts found.

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>

</x-po_dashboard_layout>