<x-po_dashboard_layout>

    @php
        $batch = $callOff->distributionBatch;
        $purchaseOrder = $batch?->purchaseOrder;
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $callOff->call_off_number }}
                    </h1>

                    @php
                        $statusClasses = match($callOff->status) {
                            'Approved' => 'bg-green-100 text-green-800',
                            'Rejected' => 'bg-red-100 text-red-800',
                            'Cancelled' => 'bg-gray-200 text-gray-700',
                            'Completed' => 'bg-blue-100 text-blue-800',
                            default => 'bg-yellow-100 text-yellow-800',
                        };
                    @endphp

                    <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $statusClasses }}">
                        {{ $callOff->status }}
                    </span>

                </div>

                <p class="mt-2 text-sm text-gray-600">
                    Distribution Batch #{{ $batch?->id ?? 'N/A' }}
                </p>

            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('tssd.call-offs.index') }}"
                    class="rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Back to Call-Offs
                </a>

                @if($callOff->status === 'Pending')

                    <form
                        action="{{ route('tssd.call-offs.destroy', $callOff) }}"
                        method="POST"
                        onsubmit="return confirm('Cancel this pending Call-Off?');"
                    >

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="rounded-xl bg-red-600 px-5 py-3 font-semibold text-white transition hover:bg-red-700"
                        >
                            Cancel Call-Off
                        </button>

                    </form>

                @endif

            </div>

        </div>

        {{-- Success --}}
        @if(session('success'))

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>

        @endif

        {{-- Call-Off Information --}}
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
                        {{ $callOff->assigned_at?->format('F d, Y') ?? 'Not set' }}
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
                        Official Call-Off Date
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $callOff->call_off_date?->format('F d, Y') ?? 'Pending Supply approval' }}
                    </p>

                </div>

                <div>

                    <p class="text-sm font-medium text-gray-500">
                        Approved By
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $callOff->approvedBy?->name ?? 'Pending approval' }}
                    </p>

                </div>

                <div>

                    <p class="text-sm font-medium text-gray-500">
                        Approved At
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $callOff->approved_at?->format('F d, Y h:i A') ?? 'Pending approval' }}
                    </p>

                </div>

                <div class="sm:col-span-2">

                    <p class="text-sm font-medium text-gray-500">
                        Remarks
                    </p>

                    <p class="mt-1 whitespace-pre-line text-gray-900">
                        {{ $callOff->remarks ?: 'No remarks provided.' }}
                    </p>

                </div>

            </div>

        </div>

        {{-- Purchase Order Information --}}
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
                        All provinces below use the same Call-Off Number.
                    </p>

                </div>

                <span class="inline-flex w-fit rounded-full bg-white px-4 py-2 text-sm font-semibold text-red-900">
                    {{ $batch?->provinceDistributions?->count() ?? 0 }} Province(s)
                </span>

            </div>

            <div class="space-y-6 p-7">

                @forelse($batch?->provinceDistributions ?? collect() as $provinceDistribution)

                    <div class="overflow-hidden rounded-xl border border-gray-200">

                        <div class="flex flex-col gap-4 bg-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                            <div>

                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $provinceDistribution->province->name }}
                                </h3>

                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $provinceDistribution->place_of_delivery ?: 'No delivery address recorded' }}
                                </p>

                            </div>

                            <div class="text-sm text-gray-700">

                                <span class="font-medium">
                                    Scheduled Delivery:
                                </span>

                                {{ $provinceDistribution->scheduled_delivery_date?->format('F d, Y') ?? 'Not set' }}

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

                                    @foreach($provinceDistribution->items as $allocationItem)

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

    </div>

</x-po_dashboard_layout>