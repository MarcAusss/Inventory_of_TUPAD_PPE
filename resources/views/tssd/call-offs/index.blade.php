<x-po_dashboard_layout>

    <div class="space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Call-Off Management
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Assign and monitor Call-Off Numbers for submitted TSSD distribution batches.
                </p>
            </div>

            <a
                href="{{ route('tssd.call-offs.create') }}"
                class="inline-flex items-center justify-center rounded-xl bg-red-900 px-5 py-3 font-semibold text-white transition hover:bg-red-800"
            >
                Assign Call-Off Number
            </a>

        </div>

        {{-- Success Message --}}
        @if(session('success'))

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>

        @endif

        {{-- Error Message --}}
        @if(session('error'))

            <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
                {{ session('error') }}
            </div>

        @endif

        {{-- Call-Off Table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="border-b border-gray-200 bg-red-900 px-6 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Call-Off Records
                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-100">

                        <tr class="text-xs font-semibold uppercase tracking-wide text-gray-700">

                            <th class="px-5 py-4 text-left">
                                No.
                            </th>

                            <th class="px-5 py-4 text-left">
                                Call-Off Number
                            </th>

                            <th class="px-5 py-4 text-left">
                                Purchase Order
                            </th>

                            <th class="px-5 py-4 text-left">
                                Supplier
                            </th>

                            <th class="px-5 py-4 text-center">
                                Provinces
                            </th>

                            <th class="px-5 py-4 text-left">
                                Assigned Date
                            </th>

                            <th class="px-5 py-4 text-center">
                                Status
                            </th>

                            <th class="px-5 py-4 text-center">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">

                        @forelse($callOffs as $callOff)

                            @php
                                $batch = $callOff->distributionBatch;
                                $purchaseOrder = $batch?->purchaseOrder;
                                $provinceCount = $batch?->provinceDistributions?->count() ?? 0;
                            @endphp

                            <tr class="hover:bg-gray-50">

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                    {{ $callOffs->firstItem() + $loop->index }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">

                                    <div class="font-semibold text-gray-900">
                                        {{ $callOff->call_off_number }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        Batch #{{ $batch?->id ?? 'N/A' }}
                                    </div>

                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm">

                                    @if($purchaseOrder)

                                        <span class="font-medium text-gray-900">
                                            {{ $purchaseOrder->po_number }}
                                        </span>

                                    @else

                                        <span class="text-gray-400">
                                            Not available
                                        </span>

                                    @endif

                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $purchaseOrder?->supplier?->supplier_name ?? 'Not available' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center">

                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                        {{ $provinceCount }}
                                    </span>

                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">

                                    {{ $callOff->assigned_at?->format('F d, Y') ?? 'Not set' }}

                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center">

                                    @php
                                        $statusClasses = match($callOff->status) {
                                            'Approved' => 'bg-green-100 text-green-800',
                                            'Rejected' => 'bg-red-100 text-red-800',
                                            'Cancelled' => 'bg-gray-200 text-gray-700',
                                            'Completed' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-yellow-100 text-yellow-800',
                                        };
                                    @endphp

                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                        {{ $callOff->status }}
                                    </span>

                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center">

                                    <div class="flex items-center justify-center gap-2">

                                        <a
                                            href="{{ route('tssd.call-offs.show', $callOff) }}"
                                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                                        >
                                            View
                                        </a>

                                        @if($callOff->status === 'Pending')

                                            <form
                                                action="{{ route('tssd.call-offs.destroy', $callOff) }}"
                                                method="POST"
                                                onsubmit="return confirm('Cancel this Call-Off? The record will remain for audit purposes.');"
                                            >

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
                                                >
                                                    Cancel
                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="px-6 py-12 text-center text-gray-500"
                                >
                                    No Call-Off records found.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($callOffs->hasPages())

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $callOffs->links() }}
                </div>

            @endif

        </div>

    </div>

</x-po_dashboard_layout>
