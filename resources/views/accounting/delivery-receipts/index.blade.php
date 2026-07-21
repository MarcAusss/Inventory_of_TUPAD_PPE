<x-po_dashboard_layout title="TSSD Distribution Summary">
    <div class="mx-auto max-w-[1900px] space-y-6">
        <x-accounting-summary-header title="TSSD Distribution Summary" description="Read-only monitoring of provincial allocations created by the TSSD Unit." />
        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" class="grid gap-3 border-b border-slate-200 p-5 md:grid-cols-4">
                <input name="search" value="{{ $search }}" placeholder="Search Call-Off, PO, supplier..." class="rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]">
                <select name="province_id" class="rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]"><option value="">All provinces</option>@foreach($provinces as $province)<option value="{{ $province->id }}" @selected((int)$provinceId === (int)$province->id)>{{ $province->name }}</option>@endforeach</select>
                <select name="status" class="rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]"><option value="">All statuses</option>@foreach(['Pending','Approved','For Delivery','Partially Received','Received','Cancelled'] as $option)<option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>@endforeach</select>
                <div class="flex gap-2"><button class="flex-1 rounded-xl bg-[#339DCB] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#247BA0]">Apply</button><a href="{{ route('accounting.distributions.index') }}" class="rounded-xl border border-[#B7D6E6] px-4 py-2.5 text-sm font-bold text-[#247BA0]">Reset</a></div>
            </form>
            <div class="overflow-x-auto"><table class="w-full min-w-[1250px] divide-y divide-slate-200"><thead class="bg-[#247BA0] text-xs font-bold uppercase tracking-wide text-white"><tr><th class="px-5 py-4 text-left">Batch</th><th class="px-5 py-4 text-left">PO</th><th class="px-5 py-4 text-left">Call-Off</th><th class="px-5 py-4 text-left">Province</th><th class="px-5 py-4 text-left">Supplier</th><th class="px-5 py-4 text-left">Scheduled Delivery</th><th class="px-5 py-4 text-center">Total PPE</th><th class="px-5 py-4 text-center">Status</th></tr></thead><tbody class="divide-y divide-slate-100">
                @forelse($distributions as $distribution)
                    @php($batch = $distribution->distributionBatch)
                    <tr class="hover:bg-[#F7FBFD]"><td class="px-5 py-4 font-bold text-slate-900">#{{ $batch?->id ?? '—' }}</td><td class="px-5 py-4">{{ $batch?->purchaseOrder?->po_number ?? '—' }}</td><td class="px-5 py-4 font-semibold text-[#247BA0]">{{ $batch?->callOff?->call_off_number ?? 'Pending' }}</td><td class="px-5 py-4">{{ $distribution->province?->name ?? '—' }}</td><td class="px-5 py-4">{{ $batch?->purchaseOrder?->supplier?->supplier_name ?? '—' }}</td><td class="px-5 py-4">{{ $distribution->scheduled_delivery_date?->format('M d, Y') ?? '—' }}</td><td class="px-5 py-4 text-center text-lg font-bold text-[#247BA0]">{{ number_format($distribution->items->sum('quantity')) }}</td><td class="px-5 py-4 text-center"><span class="rounded-full bg-[#EAF6FC] px-3 py-1 text-xs font-bold text-[#247BA0] ring-1 ring-[#B7D6E6]">{{ $distribution->status }}</span></td></tr>
                @empty<tr><td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">No distribution records found.</td></tr>@endforelse
            </tbody></table></div><div class="p-5">{{ $distributions->links() }}</div>
        </section>
    </div>
</x-po_dashboard_layout>
