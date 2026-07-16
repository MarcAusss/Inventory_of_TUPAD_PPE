<x-po_dashboard_layout title="PPE Item Details">

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- Page header --}}
        <section
            class="relative overflow-hidden rounded-3xl border
                border-[#E4EEF5] bg-white shadow-sm"
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
                            PPE Item Details
                        </span>

                        @if($item->is_active)
                            <span
                                class="rounded-full bg-green-100 px-3 py-1
                                    text-xs font-bold text-green-800
                                    ring-1 ring-green-200"
                            >
                                Available
                            </span>
                        @else
                            <span
                                class="rounded-full bg-red-100 px-3 py-1
                                    text-xs font-bold text-red-800
                                    ring-1 ring-red-200"
                            >
                                Unavailable
                            </span>
                        @endif
                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold tracking-tight
                            text-[#143A52] sm:text-3xl"
                    >
                        {{ $item->item_name }}
                    </h1>

                    <p
                        class="mt-2 max-w-3xl text-sm leading-6 text-[#36566E]"
                    >
                        Review this PPE item’s details, availability,
                        and transaction usage.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">

                    <a
                        href="{{ route('supply.items.index') }}"
                        class="inline-flex items-center justify-center rounded-xl
                            border border-[#B7D6E6] bg-white px-5 py-3
                            text-sm font-bold text-[#36566E] transition
                            hover:bg-[#F3FAFD] hover:text-[#143A52]"
                    >
                        Back to PPE Items
                    </a>

                    <a
                        href="{{ route('supply.items.edit', $item) }}"
                        class="inline-flex items-center justify-center rounded-xl
                            bg-[#339DCB] px-5 py-3 text-sm font-bold
                            text-white transition hover:bg-[#2D94BE]"
                    >
                        Edit PPE Item
                    </a>

                </div>
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

        {{-- Main details --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <article
                class="rounded-2xl border border-[#E4EEF5]
                    bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                        text-[#70879A]"
                >
                    Item Name
                </p>

                <p class="mt-3 text-xl font-bold text-[#143A52]">
                    {{ $item->item_name }}
                </p>
            </article>

            <article
                class="rounded-2xl border border-[#E4EEF5]
                    bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                        text-[#70879A]"
                >
                    Size / Label
                </p>

                <p class="mt-3 text-xl font-bold text-[#143A52]">
                    {{ $item->label ?: 'No label' }}
                </p>
            </article>

            <article
                class="rounded-2xl border border-[#E4EEF5]
                    bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                        text-[#70879A]"
                >
                    Unit of Measurement
                </p>

                <p class="mt-3 text-xl font-bold text-[#143A52]">
                    {{ $item->unit_of_measurement }}
                </p>
            </article>

            <article
                class="rounded-2xl border
                    {{ $item->is_active
                        ? 'border-green-200'
                        : 'border-red-200' }}
                    bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                        {{ $item->is_active
                            ? 'text-green-700'
                            : 'text-red-700' }}"
                >
                    Availability
                </p>

                <p
                    class="mt-3 text-xl font-bold
                        {{ $item->is_active
                            ? 'text-green-800'
                            : 'text-red-800' }}"
                >
                    {{ $item->is_active
                        ? 'Available'
                        : 'Unavailable' }}
                </p>
            </article>

        </section>

        {{-- Usage statistics --}}
        <section
            class="overflow-hidden rounded-3xl border
                border-[#E4EEF5] bg-white shadow-sm"
        >
            <div
                class="border-b border-[#E4EEF5] px-6 py-5 sm:px-7"
            >
                <p
                    class="text-xs font-bold uppercase tracking-[0.16em]
                        text-[#2D94BE]"
                >
                    Transaction usage
                </p>

                <h2 class="mt-1 text-lg font-bold text-[#143A52]">
                    System Usage Summary
                </h2>

                <p class="mt-1 text-sm text-[#70879A]">
                    Number of records currently connected to this PPE item.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 xl:grid-cols-5 sm:p-7">

                @php
                    $usageCards = [
                        [
                            'label' => 'Purchase Orders',
                            'value' => $usage['purchase_orders'] ?? 0,
                        ],
                        [
                            'label' => 'TSSD Distributions',
                            'value' => $usage['tssd_distributions'] ?? 0,
                        ],
                        [
                            'label' => 'Delivery Receipts',
                            'value' => $usage['delivery_receipts'] ?? 0,
                        ],
                        [
                            'label' => 'Project Designations',
                            'value' => $usage['project_designations'] ?? 0,
                        ],
                        [
                            'label' => 'Provincial Inventories',
                            'value' => $usage['provincial_inventories'] ?? 0,
                        ],
                    ];
                @endphp

                @foreach($usageCards as $card)
                    <article
                        class="rounded-2xl border border-[#E4EEF5]
                            bg-[#F7FBFD] p-5"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wider
                                text-[#70879A]"
                        >
                            {{ $card['label'] }}
                        </p>

                        <p class="mt-3 text-3xl font-bold text-[#143A52]">
                            {{ number_format($card['value']) }}
                        </p>
                    </article>
                @endforeach

            </div>
        </section>

        {{-- Availability action --}}
        <section
            class="rounded-3xl border border-[#E4EEF5]
                bg-white p-6 shadow-sm"
        >
            <div
                class="flex flex-col gap-5 lg:flex-row
                    lg:items-center lg:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-[0.16em]
                            text-[#2D94BE]"
                    >
                        Availability control
                    </p>

                    <h2 class="mt-1 text-lg font-bold text-[#143A52]">
                        {{ $item->is_active
                            ? 'Make this item unavailable'
                            : 'Make this item available' }}
                    </h2>

                    <p class="mt-1 max-w-3xl text-sm leading-6 text-[#70879A]">
                        @if($item->is_active)
                            Unavailable items will no longer appear in new
                            Purchase Orders, but all historical records will
                            remain unchanged.
                        @else
                            Available items can be selected again in new
                            Purchase Orders.
                        @endif
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route(
                        'supply.items.toggle-status',
                        $item
                    ) }}"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center
                            rounded-xl border px-6 py-3
                            text-sm font-bold transition
                            {{ $item->is_active
                                ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}"
                    >
                        {{ $item->is_active
                            ? 'Set as Unavailable'
                            : 'Set as Available' }}
                    </button>
                </form>
            </div>
        </section>

        {{-- Delete section --}}
        <section
            class="rounded-3xl border border-red-200
                bg-red-50/50 p-6 shadow-sm"
        >
            <div
                class="flex flex-col gap-5 lg:flex-row
                    lg:items-center lg:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-[0.16em]
                            text-red-700"
                    >
                        Delete PPE Item
                    </p>

                    <h2 class="mt-1 text-lg font-bold text-red-900">
                        Permanently remove this item
                    </h2>

                    <p class="mt-1 max-w-3xl text-sm leading-6 text-red-700">
                        This item can only be deleted when it has no Purchase
                        Orders, distributions, receipts, project designations,
                        or provincial inventory records.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('supply.items.destroy', $item) }}"
                    onsubmit="return confirm(
                        'Permanently delete this PPE item?'
                    )"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center
                            rounded-xl bg-red-600 px-6 py-3
                            text-sm font-bold text-white transition
                            hover:bg-red-700"
                    >
                        Delete PPE Item
                    </button>
                </form>
            </div>
        </section>

    </div>

</x-po_dashboard_layout>