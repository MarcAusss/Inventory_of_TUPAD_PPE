<x-po_dashboard_layout title="Review Call-Off">
    @php
        $batch = $callOff->distributionBatch;
        $purchaseOrder = $batch?->purchaseOrder;
        $allocations = $batch?->provinceDistributions ?? collect();

        $statusClass = match ($callOff->status) {
            'Approved' => 'bg-green-100 text-green-800 ring-green-200',
            'Rejected' => 'bg-red-100 text-red-800 ring-red-200',
            'Cancelled' => 'bg-slate-200 text-slate-700 ring-slate-300',
            default => 'bg-amber-100 text-amber-800 ring-amber-200',
        };

        $normalizeAllocation = function ($allocation) {
            $data = [
                'ls_m' => 0,
                'ls_l' => 0,
                'bucket' => 0,
                'boots_9' => 0,
                'boots_10' => 0,
                'gloves' => 0,
                'mask' => 0,
            ];

            foreach ($allocation->items as $allocationItem) {
                $name = strtolower(trim((string) $allocationItem->item?->item_name));
                $label = strtolower(trim((string) $allocationItem->item?->label));
                $qty = (int) $allocationItem->quantity;

                if (in_array($name, ['long sleeve', 'long sleeves', 'longsleeve', 'longsleeves'], true)) {
                    if (in_array($label, ['m', 'medium'], true)) {
                        $data['ls_m'] += $qty;
                    }
                    if (in_array($label, ['l', 'large'], true)) {
                        $data['ls_l'] += $qty;
                    }
                } elseif ($name === 'bucket hat') {
                    $data['bucket'] += $qty;
                } elseif ($name === 'rubber boots') {
                    if (in_array($label, ['us9', 'us 9', '9'], true)) {
                        $data['boots_9'] += $qty;
                    }
                    if (in_array($label, ['us10', 'us 10', '10'], true)) {
                        $data['boots_10'] += $qty;
                    }
                } elseif (in_array($name, ['gloves', 'hand gloves', 'hand glove'], true)) {
                    $data['gloves'] += $qty;
                } elseif ($name === 'mask') {
                    $data['mask'] += $qty;
                }
            }

            return $data;
        };
    @endphp

    <div class="mx-auto max-w-[1900px] space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#641D21] via-[#970C13] to-[#ED1B24]"></div>

            <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#DF979B]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#970C13] ring-1 ring-[#DF979B]">
                            Supply Unit
                        </span>

                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">
                            {{ $callOff->status }}
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        Review {{ $callOff->call_off_number }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-600">
                        Distribution Batch #{{ $batch?->id ?? 'N/A' }}. Review all provincial allocations before
                        submitting the Supply Unit decision.
                    </p>
                </div>

                <a href="{{ route('supply.call-offs.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Back to Call-Offs
                </a>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
                <p class="font-bold text-red-800">Please correct the following:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['Call-Off Number', $callOff->call_off_number, 'text-[#641D21]'], ['Purchase Order', $purchaseOrder?->po_number ?? '—', 'text-slate-900'], ['Supplier', $purchaseOrder?->supplier?->supplier_name ?? '—', 'text-slate-900'], ['Total Provinces', number_format($allocations->count()), 'text-[#970C13]']] as [$label, $value, $color])
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                    <p class="mt-3 text-xl font-bold {{ $color }}">{{ $value }}</p>
                </article>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">Reference information</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">Call-Off and Purchase Order</h2>
            </div>

            <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2 lg:grid-cols-4 sm:p-7">
                @foreach ([['NEFA Number', $purchaseOrder?->nefa_number ?? '—'], ['Assigned By', $callOff->assignedBy?->name ?? '—'], ['Assigned Date', $callOff->assigned_at?->format('F d, Y') ?? '—'], ['Status', $callOff->status]] as [$label, $value])
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $label }}</p>
                        <p class="mt-2 font-semibold text-slate-900">{{ $value }}</p>
                    </div>
                @endforeach

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:col-span-2 lg:col-span-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">TSSD Remarks</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                        {{ $callOff->remarks ?: 'No remarks provided.' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div
                class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">Provincial distribution
                        summary</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-950">Provincial Allocations</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Every province uses Call-Off Number
                        <span class="font-bold text-[#970C13]">{{ $callOff->call_off_number }}</span>.
                    </p>
                </div>

                <span
                    class="w-fit rounded-full bg-[#DF979B]/20 px-4 py-2 text-sm font-bold text-[#970C13] ring-1 ring-[#DF979B]">
                    {{ number_format($allocations->count()) }} Province(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1500px] w-full border-separate border-spacing-0">
                    <thead>
                        <tr class="text-xs font-bold uppercase tracking-wide text-white">
                            <th rowspan="2"
                                class="border-b border-r border-slate-300 bg-[#970C13] px-5 py-4 text-left">Province
                            </th>
                            <th rowspan="2"
                                class="border-b border-r border-slate-300 bg-[#970C13] px-5 py-4 text-center">Delivery
                                Date</th>
                            <th rowspan="2"
                                class="border-b border-r border-slate-300 bg-[#970C13] px-5 py-4 text-left">Place of
                                Delivery</th>
                            <th colspan="3"
                                class="border-b border-r border-slate-300 bg-[#970C13] px-5 py-4 text-center">Long
                                Sleeves</th>
                            <th rowspan="2"
                                class="border-b border-r border-slate-300 bg-[#970C13] px-5 py-4 text-center">Bucket Hat
                            </th>
                            <th colspan="3"
                                class="border-b border-r border-slate-300 bg-[#970C13] px-5 py-4 text-center">Rubber
                                Boots</th>
                            <th rowspan="2"
                                class="border-b border-r border-slate-300 bg-[#970C13] px-5 py-4 text-center">Gloves
                            </th>
                            <th rowspan="2" class="border-b border-slate-300 bg-[#970C13] px-5 py-4 text-center">Mask
                            </th>
                        </tr>

                        <tr class="text-[11px] font-bold uppercase">
                            @foreach (['M', 'L', 'Total', 'US9', 'US10', 'Total'] as $label)
                                <th
                                    class="border-b border-r border-slate-300 bg-[#DF979B] px-4 py-3 text-center text-[#641D21]">
                                    {{ $label }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($allocations as $allocation)
                            @php
                                $q = $normalizeAllocation($allocation);
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td
                                    class="border-b border-r border-slate-200 px-5 py-4 font-bold uppercase text-slate-900">
                                    {{ $allocation->province?->name ?? '—' }}
                                </td>
                                <td
                                    class="border-b border-r border-slate-200 px-5 py-4 text-center text-sm text-slate-600">
                                    {{ $allocation->scheduled_delivery_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td
                                    class="min-w-56 border-b border-r border-slate-200 px-5 py-4 text-sm text-slate-600">
                                    {{ $allocation->place_of_delivery ?: '—' }}
                                </td>
                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['ls_m']) }}</td>
                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['ls_l']) }}</td>
                                <td
                                    class="border-b border-r border-slate-200 bg-[#DF979B]/10 px-4 py-4 text-center font-bold text-[#970C13]">
                                    {{ number_format($q['ls_m'] + $q['ls_l']) }}</td>
                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['bucket']) }}</td>
                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['boots_9']) }}</td>
                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['boots_10']) }}</td>
                                <td
                                    class="border-b border-r border-slate-200 bg-[#DF979B]/10 px-4 py-4 text-center font-bold text-[#970C13]">
                                    {{ number_format($q['boots_9'] + $q['boots_10']) }}</td>
                                <td class="border-b border-r border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['gloves']) }}</td>
                                <td class="border-b border-slate-200 px-4 py-4 text-center">
                                    {{ number_format($q['mask']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-6 py-14 text-center text-sm text-slate-500">
                                    No provincial allocations were found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($callOff->status === 'Pending')
            <form action="{{ route('supply.call-offs.review', $callOff) }}" method="POST"
                enctype="multipart/form-data"
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                @csrf
                @method('PATCH')

                <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#970C13]">Supply Unit review</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-950">Supply Decision</h2>
                    <p class="mt-1 text-sm text-slate-500">Choose Approve or Reject using the radio buttons below.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2 sm:p-7">
                    <fieldset class="lg:col-span-2">
                        <legend class="mb-3 text-sm font-bold text-slate-700">
                            Decision <span class="text-red-600">*</span>
                        </legend>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="decision" value="Approved" required class="peer sr-only"
                                    @checked(old('decision') === 'Approved')>

                                <div
                                    class="rounded-2xl border-2 border-slate-200 p-5 transition hover:border-green-300 peer-checked:border-green-600 peer-checked:bg-green-50">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="h-5 w-5 rounded-full border-2 border-slate-400 peer-checked:border-green-600"></span>
                                        <div>
                                            <p class="font-bold text-green-800">Approve Call-Off</p>
                                            <p class="mt-1 text-sm text-slate-600">Authorize the provincial allocations.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="decision" value="Rejected" required class="peer sr-only"
                                    @checked(old('decision') === 'Rejected')>

                                <div
                                    class="rounded-2xl border-2 border-slate-200 p-5 transition hover:border-red-300 peer-checked:border-red-600 peer-checked:bg-red-50">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="h-5 w-5 rounded-full border-2 border-slate-400 peer-checked:border-red-600"></span>
                                        <div>
                                            <p class="font-bold text-red-800">Reject Call-Off</p>
                                            <p class="mt-1 text-sm text-slate-600">Return it to TSSD for correction.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </fieldset>

                    <div>
                        <label for="call_off_date" class="mb-2 block text-sm font-bold text-slate-700">Official
                            Call-Off Date</label>
                        <input type="date" id="call_off_date" name="call_off_date"
                            value="{{ old('call_off_date', now()->format('Y-m-d')) }}"
                            class="w-full rounded-xl border-slate-300 focus:border-[#970C13] focus:ring-[#970C13]">
                    </div>

                    <div>
                        <label for="approval_document" class="mb-2 block text-sm font-bold text-slate-700">Approval
                            Document</label>
                        <input type="file" id="approval_document" name="approval_document"
                            accept="application/pdf,.pdf"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <p class="mt-2 text-xs text-slate-500">PDF only. Maximum 10 MB.</p>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="remarks" class="mb-2 block text-sm font-bold text-slate-700">Supply
                            Remarks</label>
                        <textarea id="remarks" name="remarks" rows="4" maxlength="5000"
                            class="w-full rounded-xl border-slate-300 focus:border-[#970C13] focus:ring-[#970C13]">{{ old('remarks') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-6 py-5 sm:px-7">
                    <button type="submit"
                        class="rounded-xl bg-[#970C13] px-7 py-3 text-sm font-bold text-white transition hover:bg-[#641D21]">
                        Submit Decision
                    </button>
                </div>
            </form>
        @else
            <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Supply Decision Completed</h2>
                <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt class="text-sm text-slate-500">Decision</dt>
                        <dd class="mt-1 font-bold">{{ $callOff->status }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Official Date</dt>
                        <dd class="mt-1 font-bold">{{ $callOff->call_off_date?->format('F d, Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Reviewed By</dt>
                        <dd class="mt-1 font-bold">{{ $callOff->approvedBy?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Reviewed At</dt>
                        <dd class="mt-1 font-bold">{{ $callOff->approved_at?->format('F d, Y h:i A') ?? '—' }}</dd>
                    </div>
                </dl>
            </section>
        @endif
    </div>
</x-po_dashboard_layout>
