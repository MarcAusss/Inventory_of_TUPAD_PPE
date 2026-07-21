<x-po_dashboard_layout title="Call-Off Request Letters">

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- Header --}}
        <section
            class="relative overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm">

            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b
                       from-[#143A52]
                       via-[#247BA0]
                       to-[#55B7D9]">
            </div>

            <div
                class="flex flex-col gap-5 px-6 py-7
                       sm:px-8 lg:flex-row
                       lg:items-center
                       lg:justify-between">

                <div>

                    <div class="flex flex-wrap items-center gap-3">

                        <span
                            class="rounded-full
                                   bg-[#B7D6E6]/35
                                   px-3 py-1 text-xs
                                   font-bold uppercase
                                   tracking-[0.16em]
                                   text-[#247BA0]
                                   ring-1 ring-[#B7D6E6]">

                            TSSD Unit

                        </span>

                        <span
                            class="rounded-full bg-slate-100
                                   px-3 py-1 text-xs
                                   font-semibold text-slate-600">

                            Automated Print Preparation

                        </span>

                    </div>

                    <h1
                        class="mt-4 text-2xl font-extrabold
                               tracking-tight text-[#143A52]
                               sm:text-3xl">

                        Call-Off Request Letters

                    </h1>

                    <p
                        class="mt-2 max-w-3xl
                               text-sm leading-6 text-slate-600">

                        Prepare and print TSSD Call-Off request
                        letters using approved Call-Off,
                        NEFA, Purchase Order and provincial
                        distribution information.

                    </p>

                </div>

            </div>

        </section>

        {{-- Success message --}}
        @if (session('success'))

            <div
                class="rounded-2xl border
                       border-emerald-200
                       bg-emerald-50 px-5 py-4
                       text-sm font-semibold
                       text-emerald-700">

                {{ session('success') }}

            </div>

        @endif

        {{-- Filters and table --}}
        <section
            class="overflow-hidden rounded-3xl
                   border border-slate-200
                   bg-white shadow-sm">

            <form
                method="GET"
                action="{{ route(
                    'tssd.call-off-letters.index'
                ) }}"
                class="grid gap-3 border-b
                       border-slate-200 p-5
                       md:grid-cols-[1fr_220px_auto]">

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search Call-Off, NEFA, PO or supplier..."
                    class="rounded-xl border-slate-300
                           focus:border-[#339DCB]
                           focus:ring-[#339DCB]">

                <select
                    name="status"
                    class="rounded-xl border-slate-300
                           focus:border-[#339DCB]
                           focus:ring-[#339DCB]">

                    <option value="">
                        All statuses
                    </option>

                    @foreach (
                        [
                            'Pending Approval',
                            'Approved',
                            'Rejected',
                        ] as $statusOption
                    )

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
                        class="rounded-xl bg-[#339DCB]
                               px-5 py-2.5 text-sm
                               font-bold text-white
                               transition hover:bg-[#247BA0]">

                        Apply

                    </button>

                    <a
                        href="{{ route(
                            'tssd.call-off-letters.index'
                        ) }}"
                        class="rounded-xl border
                               border-[#B7D6E6]
                               px-5 py-2.5 text-sm
                               font-bold text-[#247BA0]
                               transition hover:bg-[#F7FBFD]">

                        Reset

                    </a>

                </div>

            </form>

            <div class="overflow-x-auto">

                <table
                    class="w-full min-w-[1400px]
                           divide-y divide-slate-200">

                    <thead
                        class="bg-[#247BA0]
                               text-xs font-bold uppercase
                               tracking-wide text-white">

                        <tr>

                            <th class="px-5 py-4 text-left">
                                Call-Off Number
                            </th>

                            <th class="px-5 py-4 text-left">
                                NEFA Number
                            </th>

                            <th class="px-5 py-4 text-left">
                                NEFA Project Title
                            </th>

                            <th class="px-5 py-4 text-left">
                                Batch
                            </th>

                            <th class="px-5 py-4 text-left">
                                Purchase Order
                            </th>

                            <th class="px-5 py-4 text-left">
                                Supplier
                            </th>

                            <th class="px-5 py-4 text-left">
                                Call-Off Date
                            </th>

                            <th class="px-5 py-4 text-center">
                                Status
                            </th>

                            <th class="px-5 py-4 text-center">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody
                        class="divide-y divide-slate-100
                               text-sm text-slate-700">

                        @forelse ($callOffs as $callOff)

                            @php
                                $batch =
                                    $callOff->distributionBatch;

                                $purchaseOrder =
                                    $batch?->purchaseOrder;

                                $supplier =
                                    $purchaseOrder?->supplier;

                                $currentTitle =
                                    $callOff->nefa_title
                                    ?: $defaultNefaTitle;

                                $statusClasses = match (
                                    strtolower(
                                        (string) $callOff->status
                                    )
                                ) {
                                    'approved' =>
                                        'bg-emerald-50 text-emerald-700 ring-emerald-200',

                                    'rejected' =>
                                        'bg-red-50 text-red-700 ring-red-200',

                                    default =>
                                        'bg-amber-50 text-amber-700 ring-amber-200',
                                };
                            @endphp

                            <tr
                                class="transition
                                       hover:bg-[#F7FBFD]">

                                <td
                                    class="px-5 py-4
                                           font-bold
                                           text-slate-900">

                                    {{ $callOff->call_off_number
                                        ?: '—' }}

                                </td>

                                <td
                                    class="px-5 py-4
                                           font-semibold
                                           text-[#247BA0]">

                                    {{ $purchaseOrder?->nefa_number
                                        ?: '—' }}

                                </td>

                                <td
                                    class="max-w-[430px]
                                           px-5 py-4">

                                    <p class="line-clamp-3">

                                        {{ $currentTitle }}

                                    </p>

                                </td>

                                <td class="px-5 py-4">

                                    Batch #{{ $batch?->id ?? '—' }}

                                </td>

                                <td class="px-5 py-4">

                                    {{ $purchaseOrder?->po_number
                                        ?: '—' }}

                                </td>

                                <td class="px-5 py-4">

                                    {{ $supplier?->supplier_name
                                        ?: '—' }}

                                </td>

                                <td class="px-5 py-4">

                                    {{ $callOff->call_off_date
                                        ?->format('M d, Y')
                                        ?? '—' }}

                                </td>

                                <td class="px-5 py-4 text-center">

                                    <span
                                        class="inline-flex
                                               rounded-full px-3 py-1
                                               text-xs font-bold
                                               ring-1
                                               {{ $statusClasses }}">

                                        {{ $callOff->status
                                            ?: 'Pending Approval' }}

                                    </span>

                                </td>

                                <td class="px-5 py-4">

                                    <div
                                        class="flex items-center
                                               justify-center gap-2">

                                        <a
                                            href="{{ route(
                                                'tssd.call-off-letters.edit',
                                                $callOff
                                            ) }}"
                                            class="rounded-xl
                                                   border border-[#B7D6E6]
                                                   px-4 py-2
                                                   text-xs font-bold
                                                   text-[#247BA0]
                                                   transition
                                                   hover:bg-[#F7FBFD]">

                                            Prepare

                                        </a>

                                        <a
                                            href="{{ route(
                                                'tssd.call-off-letters.print',
                                                $callOff
                                            ) }}"
                                            target="_blank"
                                            class="rounded-xl
                                                   bg-[#339DCB]
                                                   px-4 py-2
                                                   text-xs font-bold
                                                   text-white
                                                   transition
                                                   hover:bg-[#247BA0]">

                                            Print

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="9"
                                    class="px-6 py-14
                                           text-center
                                           text-sm text-slate-500">

                                    No Call-Off records found.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if ($callOffs->hasPages())

                <div
                    class="border-t border-slate-200
                           p-5">

                    {{ $callOffs->links() }}

                </div>

            @endif

        </section>

    </div>

</x-po_dashboard_layout>