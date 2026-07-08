@extends('layouts.provincial_office_dashboard')

@section('dashboard-provincial_office')


    <div class="max-w-7xl mx-auto p-8">

        <h1 class="text-3xl font-bold mb-6">
            Provincial Inventory
        </h1>

        <div class="bg-white rounded-xl shadow p-6">

            <div class="mb-6">

                <p>
                    <strong>Delivery Receipt:</strong>
                    {{ $receipt->dr_number }}
                </p>

                <p>
                    <strong>Purchase Order:</strong>
                    {{ $receipt->purchaseOrder->po_number }}
                </p>

                <p>
                    <strong>Supplier:</strong>
                    {{ $receipt->purchaseOrder->supplier->supplier_name }}
                </p>

                <p>
                    <strong>Province:</strong>
                    {{ $receipt->province->province_name }}
                </p>

            </div>

            <table class="w-full border">

                <thead class="bg-gray-100">

                    <tr>

                        <th class="border p-2">
                            Item
                        </th>

                        <th class="border p-2">
                            Remaining Quantity
                        </th>

                        <th class="border p-2">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($receipt->items as $item)

                        <tr>

                            <td class="border p-2">

                                {{ $item->item->item_name }}

                                @if($item->item->label)

                                    ({{ $item->item->label }})

                                @endif

                            </td>

                            <td class="border p-2 text-center">

                                {{ $item->quantity }}

                            </td>

                            <td class="border p-2 text-center">

                                <a href="{{ route('provincial.inventory.designate', $item->id) }}"
                                    class="bg-blue-600 text-white px-4 py-2 rounded">

                                    Designate

                                </a>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

@endsection