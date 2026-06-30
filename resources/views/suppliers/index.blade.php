<x-po_dashboard_layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    Suppliers
                </h2>
                <p class="text-sm text-gray-500">
                    Manage all PPE suppliers.
                </p>
            </div>

            <a href="{{ route('supply.suppliers.create') }}"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-white font-medium shadow hover:bg-blue-700 transition">
                + Add Supplier
            </a>
        </div>
    </x-slot>

    <div class="py-8">

        <div class="mx-auto max-w-7xl">

            @if(session('success'))
                <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="rounded-xl bg-white shadow">

                <!-- Search -->
                <div class="border-b p-5">

                    <form method="GET">

                        <div class="flex gap-3">

                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search supplier..."
                                class="w-full rounded-lg border-gray-300">

                            <button
                                class="rounded-lg bg-gray-800 px-5 text-white hover:bg-black">

                                Search

                            </button>

                        </div>

                    </form>

                </div>

                <!-- Table -->

                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-gray-100">

                            <tr>

                                <th class="px-6 py-4 text-left text-sm font-semibold">
                                    Supplier
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold">
                                    Contact Person
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold">
                                    Contact Number
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold">
                                    Email
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold">
                                    Address
                                </th>

                                <th class="px-6 py-4 text-center text-sm font-semibold">
                                    Status
                                </th>

                                <th class="px-6 py-4 text-center text-sm font-semibold">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-200">

                            @forelse($suppliers as $supplier)

                                <tr class="hover:bg-gray-50 transition">

                                    <td class="px-6 py-4 font-semibold text-gray-800">
                                        {{ $supplier->supplier_name }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $supplier->contact_person }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $supplier->contact_number }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $supplier->email }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $supplier->address }}
                                    </td>

                                    <td class="px-6 py-4 text-center">

                                        @if($supplier->is_active)

                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                Active
                                            </span>

                                        @else

                                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                                Inactive
                                            </span>

                                        @endif

                                    </td>

                                    <td class="px-6 py-4">

                                        <div class="flex justify-center gap-2">

                                            <a
                                                href="{{ route('supply.suppliers.show',$supplier) }}"
                                                class="rounded-md bg-sky-500 px-3 py-2 text-sm text-white hover:bg-sky-600">
                                                View
                                            </a>

                                            <a
                                                href="{{ route('supply.suppliers.edit',$supplier) }}"
                                                class="rounded-md bg-amber-500 px-3 py-2 text-sm text-white hover:bg-amber-600">
                                                Edit
                                            </a>

                                            <form
                                                action="{{ route('supply.suppliers.destroy',$supplier) }}"
                                                method="POST"
                                                onsubmit="return confirm('Delete this supplier?')">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    class="rounded-md bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-700">

                                                    Delete

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="7"
                                        class="py-10 text-center text-gray-500">

                                        No suppliers found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="border-t p-5">
                    {{ $suppliers->links() }}
                </div>

            </div>

        </div>

    </div>

</x-po_dashboard_layout>