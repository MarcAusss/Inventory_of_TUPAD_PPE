@php
    $item = $item ?? new \App\Models\Item();

    $editing = $item->exists;

    $formAction = $editing
        ? route('supply.items.update', $item)
        : route('supply.items.store');
@endphp

<form
    action="{{ $formAction }}"
    method="POST"
    class="space-y-6"
>
    @csrf

    @if($editing)
        @method('PUT')
    @endif

    {{-- Validation summary --}}
    @if($errors->any())
        <div
            class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 shadow-sm"
        >
            <p class="font-bold text-red-800">
                Please correct the following fields:
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Item information --}}
    <section
        class="overflow-hidden rounded-3xl border border-[#E4EEF5] bg-white shadow-sm"
    >
        <div
            class="border-b border-[#E4EEF5] px-6 py-5 sm:px-7"
        >
            <p
                class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]"
            >
                PPE information
            </p>

            <h2
                class="mt-1 text-lg font-bold text-[#143A52]"
            >
                Item Details
            </h2>

            <p class="mt-1 text-sm text-[#70879A]">
                Enter the PPE name, size or variant, unit of measurement,
                and availability.
            </p>
        </div>

        <div class="p-6 sm:p-7">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- Item name --}}
                <div>
                    <label
                        for="item_name"
                        class="mb-2 block text-sm font-bold text-[#36566E]"
                    >
                        Item Name

                        <span class="text-red-600">
                            *
                        </span>
                    </label>

                    <input
                        type="text"
                        id="item_name"
                        name="item_name"
                        value="{{ old('item_name', $item->item_name) }}"
                        placeholder="Example: Long Sleeve"
                        required
                        autofocus
                        class="w-full rounded-xl border-[#B7D6E6] shadow-sm
                            focus:border-[#339DCB] focus:ring-[#339DCB]"
                    >

                    <p class="mt-2 text-xs text-[#70879A]">
                        Use a general PPE name. Put the size or variation in
                        the label field.
                    </p>

                    @error('item_name')
                        <p class="mt-2 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Label --}}
                <div>
                    <label
                        for="label"
                        class="mb-2 block text-sm font-bold text-[#36566E]"
                    >
                        Size or Label
                    </label>

                    <input
                        type="text"
                        id="label"
                        name="label"
                        value="{{ old('label', $item->label) }}"
                        placeholder="Example: Medium, Large, US 9"
                        class="w-full rounded-xl border-[#B7D6E6] shadow-sm
                            focus:border-[#339DCB] focus:ring-[#339DCB]"
                    >

                    <p class="mt-2 text-xs text-[#70879A]">
                        Leave this blank when the item has no size or
                        variation.
                    </p>

                    @error('label')
                        <p class="mt-2 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Unit of measurement --}}
                <div>
                    <label
                        for="unit_of_measurement"
                        class="mb-2 block text-sm font-bold text-[#36566E]"
                    >
                        Unit of Measurement

                        <span class="text-red-600">
                            *
                        </span>
                    </label>

                    <input
                        type="text"
                        id="unit_of_measurement"
                        name="unit_of_measurement"
                        value="{{ old(
                            'unit_of_measurement',
                            $item->unit_of_measurement
                        ) }}"
                        placeholder="Example: Piece, Pair, Box"
                        required
                        class="w-full rounded-xl border-[#B7D6E6] shadow-sm
                            focus:border-[#339DCB] focus:ring-[#339DCB]"
                    >

                    @error('unit_of_measurement')
                        <p class="mt-2 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Availability --}}
                <div>
                    <p
                        class="mb-2 block text-sm font-bold text-[#36566E]"
                    >
                        Availability
                    </p>

                    <label
                        for="is_active"
                        class="flex min-h-[106px] cursor-pointer items-center
                            gap-4 rounded-2xl border border-[#B7D6E6]
                            bg-[#F7FBFD] px-5 py-4 transition
                            hover:border-[#90C4DD] hover:bg-[#F3FAFD]"
                    >
                        <input
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            value="1"
                            @checked(
                                old(
                                    'is_active',
                                    $item->exists
                                        ? $item->is_active
                                        : true
                                )
                            )
                            class="rounded border-[#90C4DD] text-[#339DCB]
                                focus:ring-[#339DCB]"
                        >

                        <span>
                            <span
                                class="block text-sm font-bold text-[#143A52]"
                            >
                                Available for new transactions
                            </span>

                            <span
                                class="mt-1 block text-xs leading-5 text-[#70879A]"
                            >
                                Active items are available in new Purchase
                                Orders. Inactive items remain visible in old
                                records.
                            </span>
                        </span>
                    </label>

                    @error('is_active')
                        <p class="mt-2 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>
        </div>
    </section>

    {{-- Information notice --}}
    <section
        class="rounded-2xl border border-[#90C4DD] bg-[#B7D6E6]/20 p-5"
    >
        <div class="flex items-start gap-4">
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center
                    rounded-xl bg-[#339DCB] text-sm font-bold text-white"
            >
                i
            </div>

            <div>
                <h3 class="font-bold text-[#143A52]">
                    Item availability behavior
                </h3>

                <p class="mt-1 text-sm leading-6 text-[#36566E]">
                    Marking an item unavailable does not remove it from old
                    Purchase Orders, distributions, Delivery Receipts,
                    provincial inventory, or project designations. It only
                    prevents the item from being selected in new Purchase
                    Orders.
                </p>
            </div>
        </div>
    </section>

    {{-- Actions --}}
    <section
        class="flex flex-col-reverse gap-3 rounded-2xl border
            border-[#E4EEF5] bg-white p-5 shadow-sm
            sm:flex-row sm:justify-end"
    >
        <a
            href="{{ route('supply.items.index') }}"
            class="inline-flex items-center justify-center rounded-xl
                border border-[#B7D6E6] bg-white px-6 py-3
                text-sm font-bold text-[#36566E] transition
                hover:bg-[#F3FAFD] hover:text-[#143A52]"
        >
            Cancel
        </a>

        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-xl
                bg-[#339DCB] px-7 py-3 text-sm font-bold text-white
                transition hover:bg-[#2D94BE]
                focus:outline-none focus:ring-2 focus:ring-[#339DCB]
                focus:ring-offset-2"
        >
            {{ $editing ? 'Update PPE Item' : 'Save PPE Item' }}
        </button>
    </section>
</form>