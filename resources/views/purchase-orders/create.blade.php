<x-po_dashboard_layout>

    <x-slot name="header">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>

                <h2 class="text-3xl font-bold text-gray-800">
                    Create Purchase Order
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Supply Management /
                    <span class="font-medium text-red-900">
                        Create Purchase Order
                    </span>
                </p>

            </div>

            <a href="{{ route('supply.purchase-orders.index') }}"
                class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50 transition">

                ← Back to Purchase Orders

            </a>

        </div>

    </x-slot>

   
 @include('purchase-orders._form')
</x-po_dashboard_layout>