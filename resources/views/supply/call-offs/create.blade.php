<x-po_dashboard_layout>

    @php
        $batch = $callOff->distributionBatch;
        $purchaseOrder = $batch?->purchaseOrder;
    @endphp

    <div class="space-y-6">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <h1 class="text-3xl font-bold text-[#36566E]">
                    Review {{ $callOff->call_off_number }}
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Distribution Batch #{{ $batch?->id ?? 'N/A' }}
                </p>

            </div>

            <a href="{{ route('supply.call-offs.index') }}"
                class="rounded-xl border border-[#B7D6E6] bg-white px-5 py-3 font-semibold text-gray-700 hover:bg-[#F7FBFD]">
                Back to Call-Offs
            </a>

        </div>

        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())

            <div class="rounded-xl border border-red-200 bg-red-50 px-6 py-5">

                <ul class="list-disc space-y-1 pl-5 text-sm text-red-700">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif

        <div class="overflow-hidden rounded-2xl border border-[#E4EEF5] bg-white shadow">

            <div class="bg-[#339DCB] px-7 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Call-Off and Purchase Order
                </h2>

            </div>

            <div class="grid grid-cols-1 gap-6 p-7 sm:grid-cols-2 lg:grid-cols-4">

                <div>

                    <p class="text-sm font-medium text-[#70879A]">
                        Call-Off Number
                    </p>

                    <p class="mt-1 font-semibold text-[#36566E]">
                        {{ $callOff->call_off_number }}
                    </p>

                </div>

                <div>

                    <p class="text-sm font-medium text-[#70879A]">
                        Purchase Order
                    </p>

                    <p class="mt-1 font-semibold text-[#36566E]">
                        {{ $purchaseOrder?->po_number ?? 'Not available' }}
                    </p>

                </div>

                <div>

                    <p class="text-sm font-medium text-[#70879A]">
                        Supplier
                    </p>

                    <p class="mt-1 font-semibold text-[#36566E]">
                        {{ $purchaseOrder?->supplier?->supplier_name ?? 'Not available' }}
                    </p>

                </div>

                <div>

                    <p class="text-sm font-medium text-[#70879A]">
                        NEFA Number
                    </p>

                    <p class="mt-1 font-semibold text-[#36566E]">
                        {{ $purchaseOrder?->nefa_number ?? 'Not available' }}
                    </p>

                </div>

                <div>

                    <p class="text-sm font-medium text-[#70879A]">
                        Assigned By
                    </p>

                    <p class="mt-1 font-semibold text-[#36566E]">
                        {{ $callOff->assignedBy?->name ?? 'Not available' }}
                    </p>

                </div>

                <div>

                    <p class="text-sm font-medium text-[#70879A]">
                        Assigned Date
                    </p>

                    <p class="mt-1 font-semibold text-[#36566E]">
                        {{ $callOff->assigned_at?->format('F d, Y') ?? 'Not available' }}
                    </p>

                </div>

                <div>

                    <p class="text-sm font-medium text-[#70879A]">
                        Status
                    </p>

                    <p class="mt-1 font-semibold text-[#36566E]">
                        {{ $callOff->status }}
                    </p>

                </div>

                <div>

                    <p class="text-sm font-medium text-[#70879A]">
                        Total Provinces
                    </p>

                    <p class="mt-1 font-semibold text-[#36566E]">
                        {{ $batch?->provinceDistributions?->count() ?? 0 }}
                    </p>

                </div>

            </div>

        </div>

        <div class="overflow-hidden rounded-2xl border border-[#E4EEF5] bg-white shadow">

            <div class="bg-[#143A52] px-7 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Provincial Allocations
                </h2>

            </div>

            <div class="space-y-5 p-7">

                @forelse($batch?->provinceDistributions ?? collect() as $allocation)

                    <div class="overflow-hidden rounded-xl border border-[#E4EEF5]">

                        <div class="bg-[#B7D6E6]/25 px-5 py-4">

                            <h3 class="font-semibold text-[#36566E]">
                                {{ $allocation->province->name }}
                            </h3>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ $allocation->place_of_delivery }}
                            </p>

                        </div>

                        <div class="overflow-x-auto">

                            <table class="min-w-full divide-y divide-gray-200">

                                <thead>

                                    <tr class="text-xs uppercase text-gray-600">

                                        <th class="px-5 py-3 text-left">
                                            PPE
                                        </th>

                                        <th class="px-5 py-3 text-left">
                                            Size
                                        </th>

                                        <th class="px-5 py-3 text-center">
                                            Quantity
                                        </th>

                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-gray-100">

                                    @foreach ($allocation->items as $allocationItem)
                                        <tr>

                                            <td class="px-5 py-3 font-medium">
                                                {{ $allocationItem->item->item_name }}
                                            </td>

                                            <td class="px-5 py-3">
                                                {{ $allocationItem->item->label ?: '—' }}
                                            </td>

                                            <td class="px-5 py-3 text-center font-semibold">
                                                {{ number_format($allocationItem->quantity) }}
                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                @empty

                    <div class="rounded-xl bg-[#F7FBFD] px-6 py-10 text-center text-[#70879A]">
                        No provincial allocations found.
                    </div>

                @endforelse

            </div>

        </div>

        @if ($callOff->status === 'Pending')
            <form action="{{ route('supply.call-offs.review', $callOff) }}" method="POST"
                enctype="multipart/form-data"
                class="overflow-hidden rounded-2xl border border-[#E4EEF5] bg-white shadow">

                @csrf
                @method('PATCH')

                <div class="bg-[#339DCB] px-7 py-5">

                    <h2 class="text-xl font-semibold text-white">
                        Supply Decision
                    </h2>

                </div>

                <div class="grid grid-cols-1 gap-6 p-7 lg:grid-cols-2">

                    <div>

                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Decision
                        </label>

                        <select name="decision" required class="w-full rounded-xl border-[#B7D6E6]">

                            <option value="">
                                Select decision
                            </option>

                            <option value="Approved" @selected(old('decision') === 'Approved')>
                                Approve
                            </option>

                            <option value="Rejected" @selected(old('decision') === 'Rejected')>
                                Reject
                            </option>

                        </select>

                    </div>

                    <div>

                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Official Call-Off Date
                        </label>

                        <input type="date" name="call_off_date"
                            value="{{ old('call_off_date', now()->format('Y-m-d')) }}"
                            class="w-full rounded-xl border-[#B7D6E6]">

                    </div>

                    <div class="lg:col-span-2">

                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Approval Document
                        </label>

                        <input type="file" name="approval_document" accept="application/pdf,.pdf"
                            class="w-full rounded-xl border border-[#B7D6E6] bg-white px-4 py-3">

                        <p class="mt-2 text-xs text-[#70879A]">
                            PDF only, maximum 10 MB.
                        </p>

                    </div>

                    <div class="lg:col-span-2">

                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            Supply Remarks
                        </label>

                        <textarea name="remarks" rows="4" class="w-full rounded-xl border-[#B7D6E6]">{{ old('remarks') }}</textarea>

                    </div>

                    <div class="lg:col-span-2 flex justify-end">

                        <button type="submit"
                            class="rounded-xl bg-[#339DCB] px-7 py-3 font-semibold text-white hover:bg-red-800">
                            Submit Decision
                        </button>

                    </div>

                </div>

            </form>
        @else
            <div class="rounded-2xl border border-[#E4EEF5] bg-white p-7 shadow">

                <h2 class="text-xl font-semibold text-[#36566E]">
                    Supply Decision Completed
                </h2>

                <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">

                    <div>

                        <dt class="text-sm text-[#70879A]">
                            Decision
                        </dt>

                        <dd class="mt-1 font-semibold text-[#36566E]">
                            {{ $callOff->status }}
                        </dd>

                    </div>

                    <div>

                        <dt class="text-sm text-[#70879A]">
                            Reviewed By
                        </dt>

                        <dd class="mt-1 font-semibold text-[#36566E]">
                            {{ $callOff->approvedBy?->name ?? 'Not available' }}
                        </dd>

                    </div>

                    <div>

                        <dt class="text-sm text-[#70879A]">
                            Reviewed At
                        </dt>

                        <dd class="mt-1 font-semibold text-[#36566E]">
                            {{ $callOff->approved_at?->format('F d, Y h:i A') ?? 'Not available' }}
                        </dd>

                    </div>

                </dl>

            </div>
        @endif

    </div>

</x-po_dashboard_layout>
