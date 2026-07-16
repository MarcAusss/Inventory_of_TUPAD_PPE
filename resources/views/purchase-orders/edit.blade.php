<x-po_dashboard_layout title="Edit Purchase Order">
    <div class="mx-auto max-w-[1900px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#2D94BE] to-[#339DCB]"></div>
            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#143A52] ring-1 ring-[#90C4DD]">Supply Unit</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">Edit Purchase Order</span>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Edit {{ $purchaseOrder->po_number }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Update the Purchase Order information, PPE quantities, item costs, remarks, or supporting document.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('supply.purchase-orders.show', $purchaseOrder) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">View Purchase Order</a>
                    <a href="{{ route('supply.purchase-orders.index') }}" class="inline-flex items-center justify-center rounded-xl bg-[#143A52] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#2D94BE]">Purchase Order List</a>
                </div>
            </div>
        </section>

        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 shadow-sm">
                <p class="font-bold text-red-800">Please correct the following fields:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">Purchase Order update</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">Edit Purchase Order Information</h2>
                <p class="mt-1 text-sm text-slate-500">Review the existing values carefully before saving your changes.</p>
            </div>
            <div class="p-6 sm:p-7">
                <form action="{{ route('supply.purchase-orders.update', $purchaseOrder) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('purchase-orders._form')
                </form>
            </div>
        </section>
    </div>
</x-po_dashboard_layout>