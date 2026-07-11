<x-po_dashboard_layout title="Provincial Office Dashboard">

    <div class="max-w-7xl mx-auto">

        <div class="bg-white rounded-xl shadow">

            <div class="bg-red-900 text-white px-6 py-4">

                <h2 class="text-2xl font-bold">
                    Receive Delivery
                </h2>

            </div>

            <div class="p-6">

                <div class="grid grid-cols-2 gap-6 mb-8">

                    <div>
                        <p class="text-gray-500">Purchase Order</p>
                        <p class="font-semibold">
                            {{ $distribution->purchaseOrder->po_number }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Supplier</p>
                        <p class="font-semibold">
                            {{ $distribution->purchaseOrder->supplier->supplier_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Province</p>
                        <p class="font-semibold">
                            {{ $distribution->province->province_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Delivery Date</p>
                        <p class="font-semibold">
                            {{ $distribution->delivery_date }}
                        </p>
                    </div>

                </div>

                <form action="{{ route('provincial.deliveries.receipt.store', $distribution->purchase_order_id) }}"
                    method="POST">

                    @csrf

                    <div class="grid grid-cols-2 gap-6">

                        <div>

                            <label class="block font-medium mb-2">
                                Delivery Receipt Number
                            </label>

                            <input type="text" name="dr_number" required class="w-full border rounded px-3 py-2">

                        </div>

                        <div>

                            <label class="block font-medium mb-2">
                                Delivery Date
                            </label>

                            <input type="date" name="delivery_date" value="{{ date('Y-m-d') }}" required
                                class="w-full border rounded px-3 py-2">

                        </div>

                        <div>

                            <label class="block font-medium mb-2">
                                Received By
                            </label>

                            <input type="text" name="received_by" required class="w-full border rounded px-3 py-2">

                        </div>

                        <div>

                            <label class="block font-medium mb-2">
                                Remarks
                            </label>

                            <textarea name="remarks" rows="2" class="w-full border rounded px-3 py-2"></textarea>

                        </div>

                    </div>

                    <hr class="my-8">

                    <table class="min-w-full border">

                        <thead class="bg-gray-100">

                            <tr>

                                <th class="border px-4 py-3 text-left">
                                    Item
                                </th>

                                <th class="border px-4 py-3 text-center">
                                    Allocated
                                </th>

                                <th class="border px-4 py-3 text-center">
                                    Received
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($items as $item)

                                <tr>

                                    <td class="border px-4 py-3">

                                        {{ $item->item->item_name }}

                                        @if($item->item->label)
                                            ({{ $item->item->label }})
                                        @endif

                                    </td>

                                    <td class="border text-center">

                                        {{ $item->quantity }}

                                    </td>

                                    <td class="border text-center">

                                        <input type="number" name="items[{{ $item->item_id }}]" value="{{ $item->quantity }}"
                                            min="0" class="w-24 border rounded px-2 py-1 text-center">

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                    <div class="mt-8 flex justify-end">

                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">

                            Confirm Receipt

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-po_dashboard_layout>