<x-po_dashboard_layout>

    <div class="space-y-6">

        <div>

            <h1 class="text-3xl font-bold text-gray-900">
                Call-Off Approval
            </h1>

            <p class="mt-1 text-sm text-gray-600">
                Review Call-Off Numbers assigned by the TSSD Unit.
            </p>

        </div>

        @if(session('success'))

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>

        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-red-900 px-6 py-5">

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
                                Assigned By
                            </th>

                            <th class="px-5 py-4 text-center">
                                Status
                            </th>

                            <th class="px-5 py-4 text-center">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($callOffs as $callOff)

                            @php
                                $batch = $callOff->distributionBatch;
                                $purchaseOrder = $batch?->purchaseOrder;
                            @endphp

                            <tr class="hover:bg-gray-50">

                                <td class="px-5 py-4 text-sm text-gray-600">
                                    {{ $callOffs->firstItem() + $loop->index }}
                                </td>

                                <td class="px-5 py-4 font-semibold text-gray-900">
                                    {{ $callOff->call_off_number }}
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $purchaseOrder?->po_number ?? 'Not available' }}
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $purchaseOrder?->supplier?->supplier_name ?? 'Not available' }}
                                </td>

                                <td class="px-5 py-4 text-center">

                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                        {{ $batch?->provinceDistributions?->count() ?? 0 }}
                                    </span>

                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $callOff->assignedBy?->name ?? 'Not available' }}
                                </td>

                                <td class="px-5 py-4 text-center">

                                    @php
                                        $statusClass = match($callOff->status) {
                                            'Approved' => 'bg-green-100 text-green-800',
                                            'Rejected' => 'bg-red-100 text-red-800',
                                            'Cancelled' => 'bg-gray-200 text-gray-700',
                                            default => 'bg-yellow-100 text-yellow-800',
                                        };
                                    @endphp

                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $callOff->status }}
                                    </span>

                                </td>

                                <td class="px-5 py-4 text-center">

                                    <a
                                        href="{{ route('supply.call-offs.show', $callOff) }}"
                                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                                    >
                                        Review
                                    </a>

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