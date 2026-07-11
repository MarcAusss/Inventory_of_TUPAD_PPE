<x-po_dashboard_layout title="Provincial Office Dashboard">

    @php
        $totalPpe = $supplyDesignation->items->sum('quantity');

        $statusClass = match($supplyDesignation->status) {
            'Completed' => 'bg-green-100 text-green-800',
            'Cancelled' => 'bg-gray-200 text-gray-700',
            default => 'bg-yellow-100 text-yellow-800',
        };
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $supplyDesignation->project_code }}
                    </h1>

                    <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $statusClass }}">
                        {{ $supplyDesignation->status }}
                    </span>

                </div>

                <p class="mt-2 text-sm text-gray-600">
                    {{ $supplyDesignation->project_title }}
                </p>

            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('provincial.project-designations.index') }}"
                    class="rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Back to Projects
                </a>

                <a
                    href="{{ route('provincial.current-inventory.index') }}"
                    class="rounded-xl bg-red-900 px-5 py-3 font-semibold text-white transition hover:bg-red-800"
                >
                    Current Inventory
                </a>

            </div>

        </div>

        @if(session('success'))

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>

        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-red-900 px-7 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Project Information
                </h2>

            </div>

            <div class="grid grid-cols-1 gap-6 p-7 sm:grid-cols-2 lg:grid-cols-4">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Project Code
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $supplyDesignation->project_code }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Designation Date
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $supplyDesignation->designation_date?->format('F d, Y') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Province
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $supplyDesignation->province?->name ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Created By
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $supplyDesignation->creator?->name ?? 'Not available' }}
                    </p>
                </div>

                <div class="sm:col-span-2 lg:col-span-4">
                    <p class="text-sm font-medium text-gray-500">
                        Project Title
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $supplyDesignation->project_title }}
                    </p>
                </div>

                <div class="sm:col-span-2 lg:col-span-4">
                    <p class="text-sm font-medium text-gray-500">
                        Location
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $supplyDesignation->location }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Number of Days
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ number_format($supplyDesignation->number_of_days) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Number of Beneficiaries
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ number_format($supplyDesignation->number_of_beneficiaries) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Total PPE Distributed
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ number_format($totalPpe) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Submitted At
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $supplyDesignation->submitted_at?->format('F d, Y h:i A') ?? 'Not available' }}
                    </p>
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <p class="text-sm font-medium text-gray-500">
                        Remarks
                    </p>

                    <p class="mt-1 whitespace-pre-line text-gray-900">
                        {{ $supplyDesignation->remarks ?: 'No remarks provided.' }}
                    </p>
                </div>

                <div>

                    @if($supplyDesignation->are_document)

                        <a
                            href="{{ asset('storage/'.$supplyDesignation->are_document) }}"
                            target="_blank"
                            class="inline-flex rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-700"
                        >
                            View ARE PDF
                        </a>

                    @endif

                </div>

            </div>

        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-gray-900 px-7 py-5">

                <h2 class="text-xl font-semibold text-white">
                    PPE Distributed to Project
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
                                PPE Item
                            </th>

                            <th class="px-5 py-4 text-left">
                                Size / Label
                            </th>

                            <th class="px-5 py-4 text-left">
                                Unit
                            </th>

                            <th class="px-5 py-4 text-center">
                                Quantity Distributed
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($supplyDesignation->items as $designationItem)

                            <tr>

                                <td class="px-5 py-4 text-sm text-gray-600">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-5 py-4 font-semibold text-gray-900">
                                    {{ $designationItem->item->item_name }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $designationItem->item->label ?: '—' }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    {{ $designationItem->item->unit_of_measurement }}
                                </td>

                                <td class="px-5 py-4 text-center text-lg font-bold text-gray-900">
                                    {{ number_format($designationItem->quantity) }}
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    class="px-6 py-12 text-center text-gray-500"
                                >
                                    No designated PPE items found.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                    <tfoot class="bg-gray-100">

                        <tr>

                            <td
                                colspan="4"
                                class="px-5 py-4 text-right font-semibold text-gray-700"
                            >
                                Total PPE Distributed
                            </td>

                            <td class="px-5 py-4 text-center text-lg font-bold text-red-900">
                                {{ number_format($totalPpe) }}
                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    </div>

</x-po_dashboard_layout>