<x-po_dashboard_layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Supplier Details
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8">

        <div class="bg-white rounded-xl shadow p-6 space-y-4">

            <div>

                <h3 class="font-semibold">
                    Supplier Name
                </h3>

                {{ $supplier->supplier_name }}

            </div>

            <div>

                <h3 class="font-semibold">
                    Contact Person
                </h3>

                {{ $supplier->contact_person }}

            </div>

            <div>

                <h3 class="font-semibold">
                    Contact Number
                </h3>

                {{ $supplier->contact_number }}

            </div>

            <div>

                <h3 class="font-semibold">
                    Email
                </h3>

                {{ $supplier->email }}

            </div>

            <div>

                <h3 class="font-semibold">
                    Address
                </h3>

                {{ $supplier->address }}

            </div>

            <div>

                <h3 class="font-semibold">
                    Remarks
                </h3>

                {{ $supplier->remarks }}

            </div>

        </div>

    </div>

</x-po_dashboard_layout>