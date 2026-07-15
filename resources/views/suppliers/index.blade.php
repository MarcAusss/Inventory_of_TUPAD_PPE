<x-po_dashboard_layout title="Suppliers">
    <div class="mx-auto max-w-[1900px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#641D21] via-[#970C13] to-[#ED1B24]"></div>
            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#DF979B]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#970C13] ring-1 ring-[#DF979B]">Supply
                            Unit</span>
                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">Supplier
                            Management</span>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Suppliers</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Manage PPE supplier records, contact information,
                        and active status.</p>
                </div>
                <a href="{{ route('supply.suppliers.create') }}"
                    class="inline-flex justify-center rounded-xl bg-[#970C13] px-5 py-3 text-sm font-bold text-white hover:bg-[#641D21]">Add
                    Supplier</a>
            </div>
        </section>

        @if (session('success'))
            <div
                class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800 shadow-sm">
                {{ session('success') }}</div>
        @endif

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">Registered suppliers</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">Supplier Records</h2>
                <form method="GET" class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search supplier..."
                        class="w-full rounded-xl border-slate-300 focus:border-[#970C13] focus:ring-[#970C13]">
                    <button
                        class="rounded-xl bg-slate-800 px-6 py-3 text-sm font-bold text-white hover:bg-slate-950">Search</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1250px] w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr class="text-xs font-bold uppercase tracking-wide text-slate-600">
                            <th class="px-6 py-4 text-left">Supplier</th>
                            <th class="px-6 py-4 text-left">Contact Person</th>
                            <th class="px-6 py-4 text-left">Contact Number</th>
                            <th class="px-6 py-4 text-left">Email</th>
                            <th class="px-6 py-4 text-left">Address</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($suppliers as $supplier)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-5 font-bold text-[#641D21]">{{ $supplier->supplier_name }}</td>
                                <td class="px-6 py-5 text-sm text-slate-700">{{ $supplier->contact_person }}</td>
                                <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600">
                                    {{ $supplier->contact_number }}</td>
                                <td class="px-6 py-5 text-sm text-slate-600">{{ $supplier->email ?: '—' }}</td>
                                <td class="max-w-sm px-6 py-5 text-sm text-slate-600">{{ $supplier->address }}</td>
                                <td class="px-6 py-5 text-center">
                                    @if ($supplier->is_active)
                                        <span
                                            class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800 ring-1 ring-green-200">Active</span>
                                    @else<span
                                            class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-800 ring-1 ring-red-200">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('supply.suppliers.show', $supplier) }}"
                                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">View</a>
                                        <a href="{{ route('supply.suppliers.edit', $supplier) }}"
                                            class="rounded-lg bg-[#970C13] px-4 py-2 text-sm font-bold text-white hover:bg-[#641D21]">Edit</a>
                                        <form action="{{ route('supply.suppliers.destroy', $supplier) }}"
                                            method="POST" onsubmit="return confirm('Delete this supplier?')">@csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-100">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty<tr>
                                <td colspan="7" class="px-6 py-14 text-center text-sm text-slate-500">No suppliers
                                    found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($suppliers->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">{{ $suppliers->links() }}</div>
            @endif
        </section>
    </div>
</x-po_dashboard_layout>
