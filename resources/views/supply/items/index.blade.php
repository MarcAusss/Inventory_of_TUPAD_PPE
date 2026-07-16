<x-po_dashboard_layout title="PPE Items">

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- Page header --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-[#E4EEF5] bg-white shadow-sm"
        >
            <div
                class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b
                    from-[#143A52] via-[#2D94BE] to-[#339DCB]"
            ></div>

            <div
                class="flex flex-col gap-6 px-6 py-7 sm:px-8
                    lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-[#B7D6E6]/35 px-3 py-1
                                text-xs font-bold uppercase tracking-[0.16em]
                                text-[#143A52] ring-1 ring-[#90C4DD]"
                        >
                            Supply Unit
                        </span>

                        <span
                            class="rounded-full bg-slate-100 px-3 py-1
                                text-xs font-semibold text-slate-700
                                ring-1 ring-slate-200"
                        >
                            PPE Item Management
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold tracking-tight
                            text-[#143A52] sm:text-3xl"
                    >
                        PPE Items
                    </h1>

                    <p
                        class="mt-2 max-w-3xl text-sm leading-6 text-[#36566E]"
                    >
                        Add, update, activate, or deactivate PPE items used in
                        Purchase Orders and the inventory workflow.
                    </p>
                </div>

                <a
                    href="{{ route('supply.items.create') }}"
                    class="inline-flex items-center justify-center rounded-xl
                        bg-[#339DCB] px-5 py-3 text-sm font-bold text-white
                        transition hover:bg-[#2D94BE]"
                >
                    + Add PPE Item
                </a>
            </div>
        </section>

        {{-- Messages --}}
        @if(session('success'))
            <div
                class="rounded-2xl border border-green-200 bg-green-50
                    px-5 py-4 text-sm font-semibold text-green-800 shadow-sm"
            >
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div
                class="rounded-2xl border border-red-200 bg-red-50
                    px-5 py-4 text-sm font-semibold text-red-800 shadow-sm"
            >
                {{ session('error') }}
            </div>
        @endif

        {{-- Summary cards --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">

            <article
                class="rounded-2xl border border-[#E4EEF5] bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider text-[#70879A]"
                >
                    Total PPE Items
                </p>

                <p class="mt-3 text-3xl font-bold text-[#143A52]">
                    {{ number_format($summary['total'] ?? 0) }}
                </p>

                <p class="mt-1 text-xs text-[#70879A]">
                    All registered item variations
                </p>
            </article>

            <article
                class="rounded-2xl border border-green-200 bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider text-green-700"
                >
                    Available Items
                </p>

                <p class="mt-3 text-3xl font-bold text-green-800">
                    {{ number_format($summary['active'] ?? 0) }}
                </p>

                <p class="mt-1 text-xs text-[#70879A]">
                    Available for new Purchase Orders
                </p>
            </article>

            <article
                class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider text-red-700"
                >
                    Unavailable Items
                </p>

                <p class="mt-3 text-3xl font-bold text-red-800">
                    {{ number_format($summary['inactive'] ?? 0) }}
                </p>

                <p class="mt-1 text-xs text-[#70879A]">
                    Hidden from new Purchase Orders
                </p>
            </article>

        </section>

        {{-- Search and filter --}}
        <section
            class="rounded-2xl border border-[#E4EEF5] bg-white p-5 shadow-sm"
        >
            <form
                method="GET"
                action="{{ route('supply.items.index') }}"
                class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_220px_auto_auto]"
            >
                <div>
                    <label for="search" class="sr-only">
                        Search PPE items
                    </label>

                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search item name, label, or unit..."
                        class="w-full rounded-xl border-[#B7D6E6]
                            focus:border-[#339DCB] focus:ring-[#339DCB]"
                    >
                </div>

                <div>
                    <label for="status" class="sr-only">
                        Filter by status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-xl border-[#B7D6E6]
                            focus:border-[#339DCB] focus:ring-[#339DCB]"
                    >
                        <option value="">
                            All statuses
                        </option>

                        <option value="active" @selected($status === 'active')>
                            Available
                        </option>

                        <option value="inactive" @selected($status === 'inactive')>
                            Unavailable
                        </option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl
                        bg-[#339DCB] px-6 py-3 text-sm font-bold text-white
                        transition hover:bg-[#2D94BE]"
                >
                    Filter
                </button>

                @if($search !== '' || $status !== '')
                    <a
                        href="{{ route('supply.items.index') }}"
                        class="inline-flex items-center justify-center rounded-xl
                            border border-[#B7D6E6] bg-white px-6 py-3
                            text-sm font-bold text-[#36566E] transition
                            hover:bg-[#F3FAFD] hover:text-[#143A52]"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </section>

        {{-- Items table --}}
        <section
            class="overflow-hidden rounded-3xl border border-[#E4EEF5]
                bg-white shadow-sm"
        >
            <div
                class="border-b border-[#E4EEF5] px-6 py-5 sm:px-7"
            >
                <p
                    class="text-xs font-bold uppercase tracking-[0.16em]
                        text-[#2D94BE]"
                >
                    Registered PPE
                </p>

                <h2 class="mt-1 text-lg font-bold text-[#143A52]">
                    PPE Item Records
                </h2>

                <p class="mt-1 text-sm text-[#70879A]">
                    Review item variations, units of measurement, transaction
                    usage, and availability.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table
                    class="min-w-[1100px] w-full divide-y divide-[#E4EEF5]"
                >
                    <thead class="bg-[#B7D6E6]/35">
                        <tr
                            class="text-xs font-bold uppercase tracking-wide
                                text-[#36566E]"
                        >
                            <th class="px-6 py-4 text-left">
                                No.
                            </th>

                            <th class="px-6 py-4 text-left">
                                PPE Item
                            </th>

                            <th class="px-6 py-4 text-left">
                                Size / Label
                            </th>

                            <th class="px-6 py-4 text-left">
                                Unit
                            </th>

                            <th class="px-6 py-4 text-center">
                                Transaction Usage
                            </th>

                            <th class="px-6 py-4 text-center">
                                Status
                            </th>

                            <th class="px-6 py-4 text-center">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#E4EEF5]">
                        @forelse($items as $item)

                            @php
                                $usageCount =
                                    (int) ($item->purchase_order_items_count ?? 0)
                                    + (int) ($item->tssd_distributions_count ?? 0)
                                    + (int) ($item->delivery_receipt_items_count ?? 0)
                                    + (int) ($item->supply_designation_items_count ?? 0)
                                    + (int) ($item->provincial_inventories_count ?? 0);
                            @endphp

                            <tr class="transition hover:bg-[#F3FAFD]">

                                <td
                                    class="whitespace-nowrap px-6 py-5
                                        text-sm text-[#70879A]"
                                >
                                    {{ $items->firstItem() + $loop->index }}
                                </td>

                                <td class="px-6 py-5">
                                    <a
                                        href="{{ route('supply.items.show', $item) }}"
                                        class="font-bold text-[#143A52]
                                            hover:text-[#2D94BE] hover:underline"
                                    >
                                        {{ $item->item_name }}
                                    </a>
                                </td>

                                <td class="px-6 py-5 text-sm text-[#36566E]">
                                    @if($item->label)
                                        <span
                                            class="inline-flex rounded-full
                                                bg-[#B7D6E6]/35 px-3 py-1
                                                text-xs font-bold text-[#143A52]
                                                ring-1 ring-[#90C4DD]"
                                        >
                                            {{ $item->label }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-6 py-5 text-sm text-[#36566E]">
                                    {{ $item->unit_of_measurement }}
                                </td>

                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="inline-flex min-w-10
                                            justify-center rounded-full
                                            bg-slate-100 px-3 py-1
                                            text-xs font-bold text-slate-700
                                            ring-1 ring-slate-200"
                                    >
                                        {{ number_format($usageCount) }}
                                    </span>
                                </td>

                                <td class="px-6 py-5 text-center">
                                    @if($item->is_active)
                                        <span
                                            class="inline-flex rounded-full
                                                bg-green-100 px-3 py-1
                                                text-xs font-bold text-green-800
                                                ring-1 ring-green-200"
                                        >
                                            Available
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full
                                                bg-red-100 px-3 py-1
                                                text-xs font-bold text-red-800
                                                ring-1 ring-red-200"
                                        >
                                            Unavailable
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-5">
                                    <div
                                        class="flex flex-wrap items-center
                                            justify-center gap-2"
                                    >
                                        <a
                                            href="{{ route('supply.items.show', $item) }}"
                                            class="inline-flex items-center
                                                justify-center rounded-lg
                                                border border-[#B7D6E6] bg-white
                                                px-4 py-2 text-sm font-bold
                                                text-[#36566E] transition
                                                hover:bg-[#F3FAFD]
                                                hover:text-[#143A52]"
                                        >
                                            View
                                        </a>

                                        <a
                                            href="{{ route('supply.items.edit', $item) }}"
                                            class="inline-flex items-center
                                                justify-center rounded-lg
                                                bg-[#339DCB] px-4 py-2
                                                text-sm font-bold text-white
                                                transition hover:bg-[#2D94BE]"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'supply.items.toggle-status',
                                                $item
                                            ) }}"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="inline-flex items-center
                                                    justify-center rounded-lg
                                                    border px-4 py-2
                                                    text-sm font-bold transition
                                                    {{ $item->is_active
                                                        ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                                        : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}"
                                            >
                                                {{ $item->is_active
                                                    ? 'Set Unavailable'
                                                    : 'Set Available' }}
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'supply.items.destroy',
                                                $item
                                            ) }}"
                                            class="inline"
                                            onsubmit="return confirm(
                                                'Delete this PPE item?'
                                            )"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="inline-flex items-center
                                                    justify-center rounded-lg
                                                    border border-red-200
                                                    bg-red-50 px-4 py-2
                                                    text-sm font-bold text-red-700
                                                    transition hover:bg-red-100"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="px-6 py-14 text-center"
                                >
                                    <p class="font-semibold text-[#143A52]">
                                        No PPE items found
                                    </p>

                                    <p class="mt-1 text-sm text-[#70879A]">
                                        Add a PPE item or adjust your search
                                        and filter criteria.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
                <div class="border-t border-[#E4EEF5] px-6 py-4">
                    {{ $items->links() }}
                </div>
            @endif
        </section>

    </div>

</x-po_dashboard_layout>