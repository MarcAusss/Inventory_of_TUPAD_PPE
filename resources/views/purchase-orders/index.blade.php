<x-po_dashboard_layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">
                Purchase Orders
            </h2>

            <a href="{{ route('supply.purchase-orders.create') }}"
               class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow transition">
                + New Purchase Order
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8">

        @if(session('success'))
            <div class="mb-5 rounded-lg bg-green-100 text-green-700 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow">

            <div class="p-6 border-b">

                <form method="GET" class="flex gap-3">

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search purchase order..."
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">

                    <button
                        class="bg-indigo-600 text-white px-6 rounded-xl hover:bg-indigo-700">
                        Search
                    </button>

                </form>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-gray-50">

                    <tr class="text-left text-sm font-semibold text-gray-600">

                        <th class="px-6 py-4">PO Number</th>
                        <th class="px-6 py-4">Supplier</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>

                    </tr>

                    </thead>

                    <tbody class="divide-y">

                    @forelse($purchaseOrders as $po)

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4 font-semibold">
                                {{ $po->po_number }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $po->supplier->supplier_name }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $po->po_date }}
                            </td>

                            <td class="px-6 py-4">
                                ₱{{ number_format($po->total_amount,2) }}
                            </td>

                            <td class="px-6 py-4">

                                <span class="px-3 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">
                                    {{ $po->status }}
                                </span>

                            </td>

                            <td class="px-6 py-4 text-right space-x-2">

                                <a href="{{ route('supply.purchase-orders.show',$po) }}"
                                   class="text-blue-600 hover:underline">
                                    View
                                </a>

                                <a href="{{ route('supply.purchase-orders.edit',$po) }}"
                                   class="text-indigo-600 hover:underline">
                                    Edit
                                </a>

                                <form
                                    action="{{ route('supply.purchase-orders.destroy',$po) }}"
                                    method="POST"
                                    class="inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        onclick="return confirm('Delete this purchase order?')"
                                        class="text-red-600 hover:underline">

                                        Delete

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center py-10 text-gray-500">
                                No purchase orders found.
                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="p-6">
                {{ $purchaseOrders->links() }}
            </div>

        </div>

    </div>
</x-po_dashboard_layout>