<x-po_dashboard_layout>
    <div class="max-w-7xl mx-auto py-8 space-y-8">

        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Purchase Order Details
                </h1>

                <p class="text-gray-500 mt-1">
                    View purchase order information and ordered PPE items.
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('supply.purchase-orders.index') }}"
                    class="px-5 py-2 rounded-xl border bg-white hover:bg-gray-100">
                    Back
                </a>

                <a href="{{ route('supply.purchase-orders.edit', $purchaseOrder) }}"
                    class="px-5 py-2 rounded-xl bg-red-900 text-white hover:bg-red-800">
                    Edit
                </a>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow border">

            <div class="bg-red-900 px-8 py-5">
                <h2 class="text-xl font-semibold text-white">
                    Purchase Order Information
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8 p-8">

                <div>
                    <p class="text-sm text-gray-500">PO Number</p>
                    <p class="font-semibold text-lg">
                        {{ $purchaseOrder->po_number }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">PO Date</p>
                    <p class="font-semibold">
                        {{ optional($purchaseOrder->po_date)->format('F d, Y') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">NEFA Number</p>
                    <p class="font-semibold">
                        {{ $purchaseOrder->nefa_number }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Supplier</p>
                    <p class="font-semibold">
                        {{ $purchaseOrder->supplier->supplier_name }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Grand Total</p>
                    <p class="text-2xl font-bold text-red-900">
                        ₱{{ number_format($purchaseOrder->total_amount, 2) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Remarks</p>

                    <p>
                        {{ $purchaseOrder->remarks ?: '—' }}
                    </p>
                </div>

            </div>

        </div>
        <div class="bg-white rounded-2xl shadow border overflow-hidden">

            <div class="bg-red-900 px-8 py-5">

                <h2 class="text-xl font-semibold text-white">
                    PPE Inventory
                </h2>

            </div>

            <table class="min-w-full">

                <thead class="bg-gray-100">

                    <tr>

                        <th class="px-6 py-4 text-left">Description</th>
                        <th class="px-6 py-4 text-center">Size</th>
                        <th class="px-6 py-4 text-center">Quantity</th>
                        <th class="px-6 py-4 text-center">Unit Cost</th>
                        <th class="px-6 py-4 text-center">Total</th>

                    </tr>

                </thead>

                <tbody class="divide-y">

                    @foreach($purchaseOrder->items as $poItem)

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4 font-medium">
                                {{ $poItem->item->item_name }}
                            </td>

                            <td class="text-center">

                                @if($poItem->item->label)

                                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm">
                                        {{ $poItem->item->label }}
                                    </span>

                                @else

                                    —

                                @endif

                            </td>

                            <td class="text-center">
                                {{ number_format($poItem->quantity) }}
                            </td>

                            <td class="text-center">
                                ₱{{ number_format($poItem->unit_cost, 2) }}
                            </td>

                            <td class="text-center font-semibold text-red-900">
                                ₱{{ number_format($poItem->quantity * $poItem->unit_cost, 2) }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

                <tfoot class="bg-gray-100">

                    <tr>

                        <td colspan="4" class="text-right px-6 py-5 font-bold">

                            GRAND TOTAL

                        </td>

                        <td class="text-center text-2xl font-bold text-red-900">

                            ₱{{ number_format($purchaseOrder->total_amount, 2) }}

                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>
        <div class="bg-white rounded-2xl shadow border">

            <div class="bg-red-900 px-8 py-5">
                <h2 class="text-xl font-semibold text-white">
                    Supporting Document
                </h2>
            </div>

            <div class="p-8">

                @if($purchaseOrder->document)

                    <a href="{{ Storage::url($purchaseOrder->document) }}" target="_blank"
                        class="inline-flex items-center gap-3 px-5 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700">

                        View Purchase Order Document

                    </a>

                @else

                    <p class="text-gray-500">
                        No supporting document uploaded.
                    </p>

                @endif

            </div>

        </div>

    </div>
</x-po_dashboard_layout>