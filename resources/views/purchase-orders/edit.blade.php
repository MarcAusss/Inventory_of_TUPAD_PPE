<x-po_dashboard_layout>

    <x-slot name="header">

        <h2 class="text-3xl font-bold">

            Edit Purchase Order

        </h2>

    </x-slot>

    <form
        action="{{ route('supply.purchase-orders.update',$purchaseOrder) }}"
        method="POST"
        enctype="multipart/form-data">

        @csrf
        @method('PUT')

        @include('purchase-orders._form')

    </form>

</x-po_dashboard_layout>