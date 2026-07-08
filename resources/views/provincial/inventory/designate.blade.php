@extends('layouts.provincial_office_dashboard')

@section('dashboard-provincial_office')

<div class="max-w-5xl mx-auto bg-white shadow rounded-xl p-8">

    <h2 class="text-2xl font-bold mb-6">
        Create Supply Designation
    </h2>

    <div class="mb-6">

        <p>
            <strong>DR Number:</strong>
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

    </div>

    <form
        action="{{ route('provincial.inventory.designate.store', $receipt->id) }}"
        method="POST">

        @csrf

        <div class="mb-4">

            <label class="block font-semibold mb-2">
                Designation Number
            </label>

            <input
                type="text"
                name="designation_number"
                class="w-full border rounded p-2"
                required>

        </div>

        <div class="mb-4">

            <label class="block font-semibold mb-2">
                Designation Date
            </label>

            <input
                type="date"
                name="designation_date"
                class="w-full border rounded p-2"
                required>

        </div>

        <div class="mb-4">

            <label class="block font-semibold mb-2">
                Project Name
            </label>

            <input
                type="text"
                name="project_name"
                class="w-full border rounded p-2"
                required>

        </div>

        <table class="w-full border mb-6">

            <thead class="bg-gray-100">

                <tr>

                    <th class="border p-2 text-left">
                        Item
                    </th>

                    <th class="border p-2 text-center">
                        Delivered
                    </th>

                    <th class="border p-2 text-center">
                        Quantity to Designate
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

                    <td class="border p-2">

                        <input
                            type="number"
                            name="items[{{ $item->item_id }}]"
                            min="0"
                            max="{{ $item->quantity }}"
                            value="0"
                            class="w-full border rounded p-2">

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

        <div class="mb-6">

            <label class="block font-semibold mb-2">
                Remarks
            </label>

            <textarea
                name="remarks"
                rows="3"
                class="w-full border rounded p-2"></textarea>

        </div>

        <button
            class="bg-red-900 text-white px-6 py-2 rounded hover:bg-red-800">

            Save Supply Designation

        </button>

    </form>

</div>

@endsection