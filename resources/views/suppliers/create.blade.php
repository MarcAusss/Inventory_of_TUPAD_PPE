<x-po_dashboard_layout title="Add Supplier">
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
                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Add Supplier</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Register a new PPE supplier and maintain
                        its contact and availability information.</p>
                </div>
                <a href="{{ route('supply.suppliers.index') }}"
                    class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Back
                    to Suppliers</a>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 shadow-sm">
                <p class="font-bold text-red-800">Please correct the following:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('supply.suppliers.store') }}" method="POST" class="space-y-6">
            @csrf

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">Supplier information</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-950">Supplier Details</h2>
                    <p class="mt-1 text-sm text-slate-500">Enter the supplier's company and contact information.</p>
                </div>
                <div class="p-6 sm:p-7">@include('suppliers._form')</div>
            </section>
            <section
                class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:justify-end">
                <a href="{{ route('supply.suppliers.index') }}"
                    class="inline-flex justify-center rounded-xl border border-slate-300 px-6 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit"
                    class="inline-flex justify-center rounded-xl bg-[#970C13] px-7 py-3 text-sm font-bold text-white hover:bg-[#641D21]">Save
                    Supplier</button>
            </section>
        </form>
    </div>
</x-po_dashboard_layout>
