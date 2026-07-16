<x-po_dashboard_layout title="Supplier Details">
    <div class="mx-auto max-w-[1900px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#2D94BE] to-[#339DCB]"></div>
            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#B7D6E6]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE] ring-1 ring-[#B7D6E6]">Supply
                            Unit</span>
                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">Supplier
                            Details</span>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        {{ $supplier->supplier_name }}</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">View the supplier's registered contact and business
                        information.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('supply.suppliers.index') }}"
                        class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Back
                        to Suppliers</a>
                    <a href="{{ route('supply.suppliers.edit', $supplier) }}"
                        class="rounded-xl bg-[#2D94BE] px-5 py-3 text-sm font-bold text-white hover:bg-[#143A52]">Edit
                        Supplier</a>
                </div>
            </div>
        </section>

        @php
            $details = [
                ['Supplier Name', $supplier->supplier_name],
                ['Contact Person', $supplier->contact_person],
                ['Contact Number', $supplier->contact_number],
                ['Email Address', $supplier->email ?: '—'],
                ['Address', $supplier->address],
                ['Remarks', $supplier->remarks ?: 'No remarks provided.'],
            ];
        @endphp

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($details as [$label, $value])
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                    <p class="mt-3 text-base font-bold leading-6 text-[#143A52]">{{ $value }}</p>
                </article>
            @endforeach
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Supplier Status</p>
                    <p class="mt-1 text-sm text-slate-500">Current availability for Supply Unit transactions.</p>
                </div>
                @if ($supplier->is_active)
                    <span
                        class="rounded-full bg-green-100 px-4 py-2 text-xs font-bold text-green-800 ring-1 ring-green-200">Active
                        Supplier</span>
                @else
                    <span
                        class="rounded-full bg-red-100 px-4 py-2 text-xs font-bold text-red-800 ring-1 ring-red-200">Inactive
                        Supplier</span>
                @endif
            </div>
        </section>
    </div>
</x-po_dashboard_layout>
