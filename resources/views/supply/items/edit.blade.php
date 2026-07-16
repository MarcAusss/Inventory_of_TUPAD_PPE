<x-po_dashboard_layout title="Edit PPE Item">

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
                            Edit PPE Item
                        </span>

                        @if($item->is_active)
                            <span
                                class="rounded-full bg-green-50 px-3 py-1
                                    text-xs font-bold text-green-700
                                    ring-1 ring-green-200"
                            >
                                Available
                            </span>
                        @else
                            <span
                                class="rounded-full bg-red-50 px-3 py-1
                                    text-xs font-bold text-red-700
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
                        Edit {{ $item->item_name }}
                    </h1>

                    <p
                        class="mt-2 max-w-3xl text-sm leading-6 text-[#36566E]"
                    >
                        Update the PPE item name, size or variation, unit of
                        measurement, or availability.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route('supply.items.show', $item) }}"
                        class="inline-flex items-center justify-center
                            rounded-xl border border-[#B7D6E6] bg-white
                            px-5 py-3 text-sm font-bold text-[#36566E]
                            transition hover:bg-[#F3FAFD]
                            hover:text-[#143A52]"
                    >
                        View PPE Item
                    </a>

                    <a
                        href="{{ route('supply.items.index') }}"
                        class="inline-flex items-center justify-center
                            rounded-xl bg-[#339DCB] px-5 py-3
                            text-sm font-bold text-white transition
                            hover:bg-[#2D94BE]"
                    >
                        PPE Item List
                    </a>
                </div>
            </div>
        </section>

        @include('supply.items._form', [
            'item' => $item,
        ])

    </div>

</x-po_dashboard_layout>