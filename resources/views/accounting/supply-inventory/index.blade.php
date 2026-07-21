<x-po_dashboard_layout title="Supply Inventory Summary">
    <div class="mx-auto max-w-[1800px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-[#B7D6E6] bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#247BA0] to-[#55B7D9]"></div>
            <div class="flex flex-col gap-5 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="inline-flex rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#247BA0] ring-1 ring-[#B7D6E6]">Accounting · Read Only</span>
                    <h1 class="mt-4 text-2xl font-bold text-slate-950 sm:text-3xl">Supply Inventory Summary</h1>
                    <p class="mt-2 text-sm text-slate-600">Current PPE quantities remaining in the central Supply Unit inventory.</p>
                </div>
                <div class="rounded-2xl bg-[#F7FBFD] px-5 py-4 ring-1 ring-[#B7D6E6]">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total available PPE</p>
                    <p class="mt-1 text-3xl font-bold text-[#247BA0]">{{ number_format($totalAvailable) }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row">
                <input name="search" value="{{ $search }}" placeholder="Search PPE item..." class="w-full rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB] sm:max-w-md">
                <button class="rounded-xl bg-[#339DCB] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#247BA0]">Search</button>
                <a href="{{ route('accounting.supply-inventory.index') }}" class="rounded-xl border border-[#B7D6E6] px-5 py-2.5 text-center text-sm font-bold text-[#247BA0] hover:bg-[#F7FBFD]">Reset</a>
            </form>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] divide-y divide-slate-200">
                    <thead class="bg-[#247BA0] text-white">
                        <tr class="text-xs font-bold uppercase tracking-wide">
                            <th class="px-6 py-4 text-left">PPE Item</th>
                            <th class="px-6 py-4 text-left">Variant</th>
                            <th class="px-6 py-4 text-left">Unit</th>
                            <th class="px-6 py-4 text-right">Available Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($inventories as $inventory)
                            <tr class="hover:bg-[#F7FBFD]">
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $inventory->item?->item_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $inventory->item?->label ?: 'Standard' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $inventory->item?->unit_of_measurement ?? '—' }}</td>
                                <td class="px-6 py-4 text-right text-lg font-bold text-[#247BA0]">{{ number_format($inventory->quantity) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">No supply inventory records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-5">{{ $inventories->links() }}</div>
        </section>
    </div>
</x-po_dashboard_layout>
