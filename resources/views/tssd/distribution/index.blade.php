<x-po_dashboard_layout>

<div class="max-w-7xl mx-auto py-8">

    <div class="flex justify-between items-center mb-6">

        <div>

            <h1 class="text-3xl font-bold">
                TSSD Distribution
            </h1>

            <p class="text-gray-500">
                Select a Purchase Order to distribute.
            </p>

        </div>

    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">

        <table class="min-w-full">

            <thead class="bg-red-900 text-white">

                <tr>

                    <th class="px-6 py-4 text-left">
                        PO Number
                    </th>

                    <th class="px-6 py-4">
                        Date
                    </th>

                    <th class="px-6 py-4">
                        Supplier
                    </th>

                    <th class="px-6 py-4">
                        Total
                    </th>

                    <th class="px-6 py-4">
                        Action
                    </th>

                </tr>

            </thead>

            <tbody class="divide-y">

                @forelse($purchaseOrders as $po)

                    <tr>

                        <td class="px-6 py-4 font-semibold">

                            {{ $po->po_number }}

                        </td>

                        <td class="text-center">

                            {{ $po->po_date }}

                        </td>

                        <td class="text-center">

                            {{ $po->supplier->supplier_name }}

                        </td>

                        <td class="text-center">

                            ₱{{ number_format($po->total_amount,2) }}

                        </td>

                        <td class="text-center">

                            <a href="{{ route('tssd.distributions.show',$po) }}"
                               class="bg-red-900 text-white px-4 py-2 rounded-lg">

                                View Distributed PPE's

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5" class="text-center py-8">

                            No Purchase Orders Found

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-6">

        {{ $purchaseOrders->links() }}

    </div>

</div>

</x-po_dashboard_layout>