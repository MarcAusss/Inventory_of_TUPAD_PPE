<x-po_dashboard_layout title="Create Supply Designation">

    <div class="mx-auto max-w-6xl space-y-6">

        {{-- Page Header --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#075985] via-[#0284C7] to-[#38BDF8]"></div>
            <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-[#7DD3FC]/20 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#7DD3FC]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#075985] ring-1 ring-inset ring-[#7DD3FC]">
                            Provincial Office
                        </span>
                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                            Project Allocation
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                        Create Supply Designation
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        Assign available PPE from this delivery receipt to a project.
                    </p>
                </div>

                <a href="{{ route('provincial.inventory.show', $receipt->id) }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-[#7DD3FC] hover:bg-sky-50 hover:text-[#075985] sm:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                    </svg>
                    Back to Inventory
                </a>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-800" role="alert">
                <p class="font-bold">Please correct the following:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Source Receipt --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5 sm:px-7">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-[#0284C7] ring-1 ring-inset ring-sky-200">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75Zm2.25 4.5h7.5m-7.5 4h7.5m-7.5 4h4.5" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900">Source Delivery Receipt</h2>
                    <p class="mt-0.5 text-sm text-slate-500">PPE quantities will be deducted from this receipt.</p>
                </div>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-3">
                <div class="border-b border-slate-200 p-6 sm:border-b-0 sm:border-r">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">DR Number</dt>
                    <dd class="mt-2 font-black text-[#075985]">{{ $receipt->dr_number }}</dd>
                </div>
                <div class="border-b border-slate-200 p-6 sm:border-b-0 sm:border-r">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Purchase Order</dt>
                    <dd class="mt-2 font-bold text-slate-900">{{ $receipt->purchaseOrder->po_number }}</dd>
                </div>
                <div class="p-6">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Supplier</dt>
                    <dd class="mt-2 font-bold text-slate-900">{{ $receipt->purchaseOrder->supplier->supplier_name }}</dd>
                </div>
            </dl>
        </section>

        <form action="{{ route('provincial.inventory.designate.store', $receipt->id) }}" method="POST"
            class="space-y-6">
            @csrf

            {{-- Designation Details --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                    <h2 class="text-lg font-black text-slate-900">Designation Details</h2>
                    <p class="mt-1 text-sm text-slate-500">Enter the project and designation reference information.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 p-6 sm:p-7 md:grid-cols-2">
                    <div>
                        <label for="designation_number" class="mb-2 block text-sm font-bold text-slate-700">
                            Designation Number <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="designation_number" name="designation_number"
                            value="{{ old('designation_number') }}" required
                            class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0284C7] focus:ring-[#0284C7]">
                        @error('designation_number')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="designation_date" class="mb-2 block text-sm font-bold text-slate-700">
                            Designation Date <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="designation_date" name="designation_date"
                            value="{{ old('designation_date', date('Y-m-d')) }}" required
                            class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0284C7] focus:ring-[#0284C7]">
                        @error('designation_date')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="project_name" class="mb-2 block text-sm font-bold text-slate-700">
                            Project Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="project_name" name="project_name" value="{{ old('project_name') }}"
                            required
                            class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0284C7] focus:ring-[#0284C7]">
                        @error('project_name')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- PPE Quantities --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">PPE Quantities</h2>
                        <p class="mt-1 text-sm text-slate-500">Enter how many units will be allocated to the project.</p>
                    </div>
                    <span
                        class="inline-flex w-fit rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-[#075985] ring-1 ring-inset ring-sky-200">
                        {{ $receipt->items->count() }} item(s)
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#0EA5E9] text-xs font-bold uppercase tracking-[0.08em] text-white">
                                <th scope="col" class="px-6 py-4 text-left">Item</th>
                                <th scope="col" class="px-6 py-4 text-center">Available</th>
                                <th scope="col" class="px-6 py-4 text-center">Quantity to Designate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($receipt->items as $item)
                                <tr class="transition-colors hover:bg-sky-50/60">
                                    <td class="min-w-[260px] px-6 py-4">
                                        <p class="font-bold text-slate-900">{{ $item->item->item_name }}</p>
                                        @if ($item->item->label)
                                            <p class="mt-1 text-xs text-slate-500">{{ $item->item->label }}</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <span
                                            class="inline-flex min-w-14 justify-center rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-black text-slate-700">
                                            {{ number_format($item->quantity) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <input type="number" name="items[{{ $item->item_id }}]" min="0"
                                            max="{{ $item->quantity }}"
                                            value="{{ old('items.' . $item->item_id, 0) }}"
                                            aria-label="Quantity to designate for {{ $item->item->item_name }}"
                                            class="w-32 rounded-xl border-slate-300 px-3 py-2 text-center text-sm font-bold text-slate-900 shadow-sm focus:border-[#0284C7] focus:ring-[#0284C7]">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Remarks and Actions --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
                <label for="remarks" class="mb-2 block text-sm font-bold text-slate-700">Remarks</label>
                <textarea id="remarks" name="remarks" rows="4"
                    placeholder="Optional notes about this designation..."
                    class="block w-full resize-y rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-[#0284C7] focus:ring-[#0284C7]">{{ old('remarks') }}</textarea>
                @error('remarks')
                    <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                @enderror

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('provincial.inventory.show', $receipt->id) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#0EA5E9] px-7 py-3 text-sm font-bold text-white shadow-sm shadow-sky-600/20 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#0284C7] focus:ring-offset-2">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        </svg>
                        Save Supply Designation
                    </button>
                </div>
            </section>
        </form>
    </div>

</x-po_dashboard_layout>