<x-po_dashboard_layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Add Supplier
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8">

        <div class="bg-white shadow rounded-xl p-6">

            <form
                action="{{ route('supply.suppliers.store') }}"
                method="POST">

                @csrf

                @include('suppliers._form')

                <div class="mt-8 flex gap-3">

                    <button
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg">

                        Save Supplier

                    </button>

                    <a
                        href="{{ route('supply.suppliers.index') }}"
                        class="px-5 py-2 rounded-lg border">

                        Cancel

                    </a>

                </div>

            </form>

        </div>

    </div>

</x-po_dashboard_layout>