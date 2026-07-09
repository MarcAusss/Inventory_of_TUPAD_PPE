@extends('layouts.provincial_office_dashboard')

@section('dashboard-provincial_office')

    <div class="bg-white rounded-xl shadow">

        <div class="bg-red-900 text-white px-6 py-4">
            <h2 class="text-2xl font-bold">
                Supply Designations
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

                    <th class="px-4 py-3 text-left">
                        Designation No.
                    </th>

                    <th class="px-4 py-3 text-left">
                        Project
                    </th>

                    <th class="px-4 py-3 text-left">
                        Designated Items
                    </th>

                    <th class="px-4 py-3 text-center">
                        Date
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($designations as $designation)

                    <tr>

                        <td class="border px-4 py-3">
                            {{ $designation->deliveryReceipt->purchaseOrder->po_number }}
                        </td>

                        <td class="border px-4 py-3">
                            {{ $designation->deliveryReceipt->purchaseOrder->supplier->supplier_name }}
                        </td>

                        <td class="border px-4 py-3">
                            {{ $designation->designation_number }}
                        </td>

                        <td class="border px-4 py-3">
                            {{ $designation->project_name }}
                        </td>

                        <td class="border px-4 py-3">

                            @foreach($designation->items as $designationItem)

                                <div class="mb-1">

                                    {{ $designationItem->item->item_name }}

                                    @if($designationItem->item->label)
                                        ({{ $designationItem->item->label }})
                                    @endif

                                    —
                                    <strong>{{ $designationItem->quantity }}</strong>

                                </div>

                            @endforeach

                        </td>

                        <td class="border text-center">
                            {{ $designation->designation_date->format('F d, Y') }}
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6" class="text-center py-8">
                            No Supply Designations found.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

@endsection