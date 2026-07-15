<x-po_dashboard_layout title="Create Purchase Order">
    <div class="mx-auto max-w-[1900px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#641D21] via-[#970C13] to-[#ED1B24]"></div>
            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#DF979B]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#970C13] ring-1 ring-[#DF979B]">
                            Supply Unit'
                        </span>
                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                            New Purchase Order
                        </span>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Create Purchase Order
                    </h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Record the supplier, PO references,
                        ordered PPE quantities, item costs, remarks, and supporting Purchase Order document.</p>
                </div>
                <a href="{{ route('supply.purchase-orders.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Back
                    to Purchase Orders</a>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 shadow-sm">
                <p class="font-bold text-red-800">Please correct the following fields:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">Purchase Order entry</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">Purchase Order Information</h2>
                <p class="mt-1 text-sm text-slate-500">Complete all required information before saving the Purchase
                    Order.</p>
            </div>
            <div class="p-6 sm:p-7">@include('purchase-orders._form')</div>
        </section>
    </div>
</x-po_dashboard_layout>
