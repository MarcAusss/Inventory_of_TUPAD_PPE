<x-po_dashboard_layout title="Provincial Office Dashboard">

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Project PPE Designations
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Manage PPE distributed to projects under
                    <span class="font-semibold text-gray-900">
                        {{ auth()->user()->provinceName() }}
                    </span>.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('provincial.current-inventory.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Current Inventory
                </a>

                <a
                    href="{{ route('provincial.project-designations.create') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-red-900 px-5 py-3 font-semibold text-white transition hover:bg-red-800"
                >
                    Create Project Designation
                </a>

            </div>

        </div>

        @if(session('success'))

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>

        @endif

        @if(session('error'))

            <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
                {{ session('error') }}
            </div>

        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="flex flex-col gap-4 border-b border-gray-200 bg-red-900 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">

                <div>
                    <h2 class="text-xl font-semibold text-white">
                        Project Records
                    </h2>

                    <p class="mt-1 text-sm text-red-100">
                        Completed PPE distributions to provincial projects.
                    </p>
                </div>

                <form
                    action="{{ route('provincial.project-designations.index') }}"
                    method="GET"
                    class="flex w-full max-w-md gap-2"
                >

                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search code, title, or location..."
                        class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                    >

                    <button
                        type="submit"
                        class="rounded-xl bg-gray-900 px-5 py-2 font-semibold text-white transition hover:bg-gray-800"
                    >
                        Search
                    </button>

                    @if($search)

                        <a
                            href="{{ route('provincial.project-designations.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700"
                        >
                            Clear
                        </a>

                    @endif

                </form>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-100">

                        <tr class="text-xs font-semibold uppercase tracking-wide text-gray-700">

                            <th class="px-5 py-4 text-left">
                                No.
                            </th>

                            <th class="px-5 py-4 text-left">
                                Project Code
                            </th>

                            <th class="px-5 py-4 text-left">
                                Project Title
                            </th>

                            <th class="px-5 py-4 text-left">
                                Location
                            </th>

                            <th class="px-5 py-4 text-center">
                                Beneficiaries
                            </th>

                            <th class="px-5 py-4 text-center">
                                Days
                            </th>

                            <th class="px-5 py-4 text-left">
                                Designation Date
                            </th>

                            <th class="px-5 py-4 text-center">
                                Total PPE
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

                        @forelse($designations as $designation)

                            @php
                                $totalPpe = $designation->items->sum('quantity');

                                $statusClass = match($designation->status) {
                                    'Completed' => 'bg-green-100 text-green-800',
                                    'Cancelled' => 'bg-gray-200 text-gray-700',
                                    default => 'bg-yellow-100 text-yellow-800',
                                };
                            @endphp

                            <tr class="hover:bg-gray-50">

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                    {{ $designations->firstItem() + $loop->index }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">

                                    <div class="font-semibold text-gray-900">
                                        {{ $designation->project_code }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $designation->designation_number }}
                                    </div>

                                </td>

                                <td class="min-w-64 px-5 py-4 font-medium text-gray-900">
                                    {{ $designation->project_title }}
                                </td>

                                <td class="min-w-52 px-5 py-4 text-sm text-gray-700">
                                    {{ $designation->location }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center font-semibold text-gray-900">
                                    {{ number_format($designation->number_of_beneficiaries) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center text-gray-700">
                                    {{ number_format($designation->number_of_days) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                                    {{ $designation->designation_date?->format('F d, Y') }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center font-semibold text-gray-900">
                                    {{ number_format($totalPpe) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center">

                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $designation->status }}
                                    </span>

                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-center">

                                    <a
                                        href="{{ route('provincial.project-designations.show', $designation) }}"
                                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                                    >
                                        View
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="10"
                                    class="px-6 py-12 text-center text-gray-500"
                                >
                                    No project PPE designations found.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($designations->hasPages())

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $designations->links() }}
                </div>

            @endif

        </div>

    </div>

</x-po_dashboard_layout>