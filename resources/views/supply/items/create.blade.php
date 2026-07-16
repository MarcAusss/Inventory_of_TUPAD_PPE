<x-po_dashboard_layout title="Add PPE Item">

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
                            PPE Item Management
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold tracking-tight
                            text-[#143A52] sm:text-3xl"
                    >
                        Add PPE Item
                    </h1>

                    <p
                        class="mt-2 max-w-3xl text-sm leading-6 text-[#36566E]"
                    >
                        Register a new PPE item, size or variation, unit of
                        measurement, and availability for future Purchase
                        Orders.
                    </p>
                </div>

                <a
                    href="{{ route('supply.items.index') }}"
                    class="inline-flex items-center justify-center rounded-xl
                        border border-[#B7D6E6] bg-white px-5 py-3
                        text-sm font-bold text-[#36566E] transition
                        hover:bg-[#F3FAFD] hover:text-[#143A52]"
                >
                    Back to PPE Items
                </a>
            </div>
        </section>

        @include('supply.items._form')

    </div>

</x-po_dashboard_layout>