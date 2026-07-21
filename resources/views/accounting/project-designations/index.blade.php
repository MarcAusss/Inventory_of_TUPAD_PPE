<x-po_dashboard_layout title="Project Designation Summary">

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- Page Header --}}
        <x-accounting-summary-header
            title="Project Designation Summary"
            description="Read-only monitoring of PPE allocations designated to provincial projects."
        />

        {{-- Summary Cards --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <div
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm">

                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.14em] text-slate-500">

                    Total Designations

                </p>

                <p
                    class="mt-3 text-3xl font-extrabold
                           text-[#143A52]">

                    {{ number_format($totalDesignations) }}

                </p>

            </div>

            <div
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm">

                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.14em] text-slate-500">

                    Designated PPE

                </p>

                <p
                    class="mt-3 text-3xl font-extrabold
                           text-[#247BA0]">

                    {{ number_format($totalDesignatedPpe) }}

                </p>

            </div>

            <div
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm">

                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.14em] text-slate-500">

                    Completed / Approved

                </p>

                <p
                    class="mt-3 text-3xl font-extrabold
                           text-emerald-700">

                    {{ number_format($completedCount) }}

                </p>

            </div>

            <div
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm">

                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.14em] text-slate-500">

                    Pending / Draft

                </p>

                <p
                    class="mt-3 text-3xl font-extrabold
                           text-amber-700">

                    {{ number_format($pendingCount) }}

                </p>

            </div>

        </section>

        {{-- Main Table --}}
        <section
            class="overflow-hidden rounded-3xl
                   border border-slate-200
                   bg-white shadow-sm">

            {{-- Filters --}}
            <form
                method="GET"
                action="{{ route('accounting.project-designations.index') }}"
                class="grid gap-3 border-b border-slate-200 p-5
                       md:grid-cols-4">

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search designation, project, Call-Off..."
                    class="rounded-xl border-slate-300
                           focus:border-[#339DCB]
                           focus:ring-[#339DCB]">

                <select
                    name="province_id"
                    class="rounded-xl border-slate-300
                           focus:border-[#339DCB]
                           focus:ring-[#339DCB]">

                    <option value="">
                        All provinces
                    </option>

                    @foreach ($provinces as $province)

                        <option
                            value="{{ $province->id }}"
                            @selected(
                                (int) $provinceId
                                === (int) $province->id
                            )>

                            {{ $province->name }}

                        </option>

                    @endforeach

                </select>

                <select
                    name="status"
                    class="rounded-xl border-slate-300
                           focus:border-[#339DCB]
                           focus:ring-[#339DCB]">

                    <option value="">
                        All statuses
                    </option>

                    @foreach ($statuses as $statusOption)

                        <option
                            value="{{ $statusOption }}"
                            @selected($status === $statusOption)>

                            {{ $statusOption }}

                        </option>

                    @endforeach

                </select>

                <div class="flex gap-2">

                    <button
                        type="submit"
                        class="flex-1 rounded-xl
                               bg-[#339DCB] px-4 py-2.5
                               text-sm font-bold text-white
                               transition hover:bg-[#247BA0]">

                        Apply

                    </button>

                    <a
                        href="{{ route(
                            'accounting.project-designations.index'
                        ) }}"
                        class="rounded-xl border
                               border-[#B7D6E6]
                               px-4 py-2.5 text-sm
                               font-bold text-[#247BA0]
                               transition hover:bg-[#F7FBFD]">

                        Reset

                    </a>

                </div>

            </form>

            {{-- Table --}}
            <div class="overflow-x-auto">

                <table
                    class="w-full min-w-[1450px]
                           divide-y divide-slate-200">

                    <thead
                        class="bg-[#247BA0] text-xs
                               font-bold uppercase
                               tracking-wide text-white">

                        <tr>

                            <th class="px-5 py-4 text-left">
                                Designation No.
                            </th>

                            <th class="px-5 py-4 text-left">
                                Call-Off
                            </th>

                            <th class="px-5 py-4 text-left">
                                Province
                            </th>

                            <th class="px-5 py-4 text-left">
                                Supplier
                            </th>

                            <th class="px-5 py-4 text-left">
                                Project
                            </th>

                            <th class="px-5 py-4 text-left">
                                Designation Date
                            </th>

                            <th class="px-5 py-4 text-center">
                                PPE Quantity
                            </th>

                            <th class="px-5 py-4 text-center">
                                Status
                            </th>

                            <th class="px-5 py-4 text-left">
                                Remarks
                            </th>

                        </tr>

                    </thead>

                    <tbody
                        class="divide-y divide-slate-100
                               text-sm text-slate-700">

                        @forelse ($designations as $designation)

                            @php
                                $allocation =
                                    $designation->provinceDistribution;

                                $province =
                                    $allocation?->province
                                    ?? $designation->province;

                                $batch =
                                    $allocation?->distributionBatch;

                                $callOff =
                                    $batch?->callOff;

                                $purchaseOrder =
                                    $batch?->purchaseOrder;

                                $supplier =
                                    $purchaseOrder?->supplier;

                                $designationStatus =
                                    (string) (
                                        $designation->status
                                        ?: 'Pending'
                                    );

                                $normalizedStatus =
                                    strtolower($designationStatus);

                                $statusClasses = match (true) {
                                    in_array(
                                        $normalizedStatus,
                                        [
                                            'approved',
                                            'completed',
                                            'designated',
                                        ],
                                        true
                                    )
                                        => 'bg-emerald-50 text-emerald-700 ring-emerald-200',

                                    in_array(
                                        $normalizedStatus,
                                        [
                                            'rejected',
                                            'cancelled',
                                            'canceled',
                                        ],
                                        true
                                    )
                                        => 'bg-red-50 text-red-700 ring-red-200',

                                    in_array(
                                        $normalizedStatus,
                                        [
                                            'pending',
                                            'pending approval',
                                            'for approval',
                                        ],
                                        true
                                    )
                                        => 'bg-amber-50 text-amber-700 ring-amber-200',

                                    default
                                        => 'bg-slate-100 text-slate-700 ring-slate-200',
                                };
                            @endphp

                            <tr class="transition hover:bg-[#F7FBFD]">

                                <td
                                    class="px-5 py-4 font-bold
                                           text-slate-900">

                                    {{ $designation->designation_number
                                        ?: '—' }}

                                </td>

                                <td
                                    class="px-5 py-4 font-semibold
                                           text-[#247BA0]">

                                    {{ $callOff?->call_off_number
                                        ?? '—' }}

                                </td>

                                <td class="px-5 py-4">

                                    {{ $province?->name ?? '—' }}

                                </td>

                                <td class="px-5 py-4">

                                    {{ $supplier?->supplier_name
                                        ?? '—' }}

                                </td>

                                <td class="px-5 py-4">

                                    <p
                                        class="font-semibold
                                               text-slate-900">

                                        {{ $designation->project_name
                                            ?: '—' }}

                                    </p>

                                </td>

                                <td class="px-5 py-4">

                                    @if ($designation->designation_date)

                                        {{ \Illuminate\Support\Carbon::parse(
                                            $designation->designation_date
                                        )->format('M d, Y') }}

                                    @else

                                        —

                                    @endif

                                </td>

                                <td
                                    class="px-5 py-4 text-center
                                           text-lg font-bold
                                           text-[#247BA0]">

                                    {{ number_format(
                                        $designation->items->sum(
                                            'quantity'
                                        )
                                    ) }}

                                </td>

                                <td class="px-5 py-4 text-center">

                                    <span
                                        class="inline-flex rounded-full
                                               px-3 py-1 text-xs
                                               font-bold ring-1
                                               {{ $statusClasses }}">

                                        {{ $designationStatus }}

                                    </span>

                                </td>

                                <td
                                    class="max-w-[300px]
                                           px-5 py-4">

                                    <p
                                        class="line-clamp-2
                                               text-slate-600">

                                        {{ $designation->remarks
                                            ?: '—' }}

                                    </p>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="9"
                                    class="px-6 py-14
                                           text-center text-sm
                                           text-slate-500">

                                    No project designation records found.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- Pagination --}}
            @if ($designations->hasPages())

                <div
                    class="border-t border-slate-200
                           bg-white p-5">

                    {{ $designations->links() }}

                </div>

            @endif

        </section>

    </div>

</x-po_dashboard_layout>
