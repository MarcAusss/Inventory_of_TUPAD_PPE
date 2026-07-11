<x-po_dashboard_layout>

    @php
        $batch = $callOff->distributionBatch;
        $purchaseOrder = $batch?->purchaseOrder;

        $statusClass = match ($callOff->status) {
            'Approved' => 'bg-green-100 text-green-800',
            'Rejected' => 'bg-red-100 text-red-800',
            'Cancelled' => 'bg-gray-200 text-gray-700',
            'Completed' => 'bg-blue-100 text-blue-800',
            default => 'bg-yellow-100 text-yellow-800',
        };
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-3xl font-bold text-gray-900">
                        Review {{ $callOff->call_off_number }}
                    </h1>

                    <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $statusClass }}">
                        {{ $callOff->status }}
                    </span>

                </div>

                <p class="mt-2 text-sm text-gray-600">
                    Distribution Batch #{{ $batch?->id ?? 'N/A' }}
                </p>
            </div>

            <a
                href="{{ route('supply.call-offs.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Back to Call-Offs
            </a>

        </div>

        {{-- Success Message --}}
        @if(session('success'))

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>

        @endif

        {{-- Validation Errors --}}
        @if($errors->any())

            <div class="rounded-xl border border-red-200 bg-red-50 px-6 py-5">

                <h2 class="font-semibold text-red-800">
                    Please correct the following:
                </h2>

                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-red-700">

                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif

        {{-- Call-Off Details --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-red-900 px-7 py-5">
                <h2 class="text-xl font-semibold text-white">
                    Call-Off Information
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 p-7 sm:grid-cols-2 lg:grid-cols-4">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Call-Off Number
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $callOff->call_off_number }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Assigned Date
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $callOff->assigned_at?->format('F d, Y') ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Assigned By
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $callOff->assignedBy?->name ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Status
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $callOff->status }}
                    </p>
                </div>

                <div class="sm:col-span-2 lg:col-span-4">
                    <p class="text-sm font-medium text-gray-500">
                        TSSD Remarks
                    </p>

                    <p class="mt-1 whitespace-pre-line text-gray-900">
                        {{ $callOff->remarks ?: 'No remarks provided.' }}
                    </p>
                </div>

            </div>

        </div>

        {{-- Source Purchase Order --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-gray-900 px-7 py-5">
                <h2 class="text-xl font-semibold text-white">
                    Source Purchase Order
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 p-7 sm:grid-cols-2 lg:grid-cols-4">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        PO Number
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $purchaseOrder?->po_number ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        PO Date
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $purchaseOrder?->po_date?->format('F d, Y') ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Supplier
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $purchaseOrder?->supplier?->supplier_name ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        NEFA Number
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $purchaseOrder?->nefa_number ?? 'Not available' }}
                    </p>
                </div>

            </div>

        </div>

        {{-- Provincial Allocations --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="flex flex-col gap-3 bg-red-900 px-7 py-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-xl font-semibold text-white">
                        Provincial Allocations
                    </h2>

                    <p class="mt-1 text-sm text-red-100">
                        Every province below uses the same Call-Off Number.
                    </p>
                </div>

                <span class="w-fit rounded-full bg-white px-4 py-2 text-sm font-semibold text-red-900">
                    {{ $batch?->provinceDistributions?->count() ?? 0 }} Province(s)
                </span>

            </div>

            <div class="space-y-6 p-7">

                @forelse($batch?->provinceDistributions ?? collect() as $allocation)

                    <div class="overflow-hidden rounded-xl border border-gray-200">

                        <div class="flex flex-col gap-4 bg-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $allocation->province->name }}
                                </h3>

                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $allocation->place_of_delivery ?: 'No delivery location recorded' }}
                                </p>
                            </div>

                            <div class="text-sm text-gray-700">
                                <span class="font-medium">
                                    Scheduled Delivery:
                                </span>

                                {{ $allocation->scheduled_delivery_date?->format('F d, Y') ?? 'Not set' }}
                            </div>

                        </div>

                        <div class="overflow-x-auto">

                            <table class="min-w-full divide-y divide-gray-200">

                                <thead class="bg-white">

                                    <tr class="text-xs font-semibold uppercase tracking-wide text-gray-600">

                                        <th class="px-5 py-3 text-left">
                                            PPE Item
                                        </th>

                                        <th class="px-5 py-3 text-left">
                                            Size / Label
                                        </th>

                                        <th class="px-5 py-3 text-center">
                                            Quantity
                                        </th>

                                        <th class="px-5 py-3 text-left">
                                            Unit
                                        </th>

                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-gray-100">

                                    @foreach($allocation->items as $allocationItem)

                                        <tr>

                                            <td class="px-5 py-3 font-medium text-gray-900">
                                                {{ $allocationItem->item->item_name }}
                                            </td>

                                            <td class="px-5 py-3 text-gray-700">
                                                {{ $allocationItem->item->label ?: '—' }}
                                            </td>

                                            <td class="px-5 py-3 text-center font-semibold text-gray-900">
                                                {{ number_format($allocationItem->quantity) }}
                                            </td>

                                            <td class="px-5 py-3 text-gray-700">
                                                {{ $allocationItem->item->unit_of_measurement }}
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                @empty

                    <div class="rounded-xl bg-gray-50 px-6 py-10 text-center text-gray-500">
                        No provincial allocations were found.
                    </div>

                @endforelse

            </div>

        </div>

        {{-- Supply Decision Form --}}
        @if($callOff->status === 'Pending')

            <form
                action="{{ route('supply.call-offs.review', $callOff) }}"
                method="POST"
                enctype="multipart/form-data"
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow"
            >

                @csrf
                @method('PATCH')

                <div class="bg-red-900 px-7 py-5">
                    <h2 class="text-xl font-semibold text-white">
                        Supply Decision
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-6 p-7 lg:grid-cols-2">

                    <div>

                        <label
                            for="decision"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Decision
                        </label>

                        <select
                            id="decision"
                            name="decision"
                            required
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                        >

                            <option value="">
                                Select decision
                            </option>

                            <option
                                value="Approved"
                                @selected(old('decision') === 'Approved')
                            >
                                Approve
                            </option>

                            <option
                                value="Rejected"
                                @selected(old('decision') === 'Rejected')
                            >
                                Reject
                            </option>

                        </select>

                    </div>

                    <div>

                        <label
                            for="call_off_date"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Official Call-Off Date
                        </label>

                        <input
                            type="date"
                            id="call_off_date"
                            name="call_off_date"
                            value="{{ old('call_off_date', now()->format('Y-m-d')) }}"
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                        >

                    </div>

                    <div class="lg:col-span-2">

                        <label
                            for="approval_document"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Approval Document
                        </label>

                        <input
                            type="file"
                            id="approval_document"
                            name="approval_document"
                            accept="application/pdf,.pdf"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3"
                        >

                        <p class="mt-2 text-xs text-gray-500">
                            PDF only. Maximum file size: 10 MB.
                        </p>

                    </div>

                    <div class="lg:col-span-2">

                        <label
                            for="remarks"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Supply Remarks
                        </label>

                        <textarea
                            id="remarks"
                            name="remarks"
                            rows="4"
                            maxlength="5000"
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                        >{{ old('remarks') }}</textarea>

                    </div>

                    <div class="lg:col-span-2 flex justify-end">

                        <button
                            type="submit"
                            class="rounded-xl bg-red-900 px-7 py-3 font-semibold text-white transition hover:bg-red-800"
                        >
                            Submit Decision
                        </button>

                    </div>

                </div>

            </form>

        @else

            {{-- Completed Decision --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-7 shadow">

                <h2 class="text-xl font-semibold text-gray-900">
                    Supply Decision Completed
                </h2>

                <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">

                    <div>
                        <dt class="text-sm text-gray-500">
                            Decision
                        </dt>

                        <dd class="mt-1 font-semibold text-gray-900">
                            {{ $callOff->status }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Official Call-Off Date
                        </dt>

                        <dd class="mt-1 font-semibold text-gray-900">
                            {{ $callOff->call_off_date?->format('F d, Y') ?? 'Not available' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Reviewed By
                        </dt>

                        <dd class="mt-1 font-semibold text-gray-900">
                            {{ $callOff->approvedBy?->name ?? 'Not available' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Reviewed At
                        </dt>

                        <dd class="mt-1 font-semibold text-gray-900">
                            {{ $callOff->approved_at?->format('F d, Y h:i A') ?? 'Not available' }}
                        </dd>
                    </div>

                </dl>

                @if($callOff->approval_document)

                    <div class="mt-6">

                        <a
                            href="{{ asset('storage/'.$callOff->approval_document) }}"
                            target="_blank"
                            class="inline-flex rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-700"
                        >
                            View Approval Document
                        </a>

                    </div>

                @endif

            </div>

        @endif

    </div>

</x-po_dashboard_layout>