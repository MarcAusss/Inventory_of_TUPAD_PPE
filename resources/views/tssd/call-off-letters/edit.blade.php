<x-po_dashboard_layout title="Prepare Call-Off Letter">

    @php
        $batch = $callOff->distributionBatch;
        $purchaseOrder = $batch?->purchaseOrder;
        $supplier = $purchaseOrder?->supplier;

        $nefaTitle = old('nefa_title', $callOff->nefa_title ?: $defaultNefaTitle);

        $printTotalAmount = old(
            'print_total_amount',
            $callOff->print_total_amount ?? ($purchaseOrder?->total_amount ?? 0),
        );

        $printMarginTop = old('print_margin_top', $callOff->print_margin_top ?? 9);

        $printMarginRight = old('print_margin_right', $callOff->print_margin_right ?? 11);

        $printMarginBottom = old('print_margin_bottom', $callOff->print_margin_bottom ?? 28);

        $printMarginLeft = old('print_margin_left', $callOff->print_margin_left ?? 11);
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">

        <section
            class="relative overflow-hidden rounded-3xl
                   border border-slate-200
                   bg-white shadow-sm">

            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b
                       from-[#143A52]
                       via-[#247BA0]
                       to-[#55B7D9]">
            </div>

            <div class="px-7 py-7 sm:px-9">

                <span
                    class="rounded-full
                           bg-[#B7D6E6]/35
                           px-3 py-1 text-xs
                           font-bold uppercase
                           tracking-[0.16em]
                           text-[#247BA0]
                           ring-1 ring-[#B7D6E6]">

                    TSSD Letter Preparation

                </span>

                <h1 class="mt-4 text-2xl font-extrabold
                           text-[#143A52]">

                    Prepare Call-Off Request Letter

                </h1>

                <p class="mt-2 text-sm
                           leading-6 text-slate-600">

                    The Call-Off, NEFA, Purchase Order,
                    batch and supplier fields are generated
                    automatically. The NEFA title, printed
                    amount, and A4 paper margins can be edited.

                </p>

            </div>

        </section>

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

        <section
            class="rounded-3xl border
                   border-slate-200
                   bg-white p-6 shadow-sm sm:p-8">

            <form method="POST"
                action="{{ route('tssd.call-off-letters.update', $callOff) }}"
                class="space-y-7">

                @csrf
                @method('PUT')

                {{-- Read-only automatic fields --}}
                <div class="grid gap-5
                           md:grid-cols-2">

                    <div>

                        <label class="mb-2 block text-sm
                                   font-bold text-slate-700">

                            Call-Off Number

                        </label>

                        <input type="text"
                            value="{{ $callOff->call_off_number ?: 'Not assigned' }}"
                            readonly
                            class="w-full cursor-not-allowed
                                   rounded-xl border-slate-200
                                   bg-slate-100
                                   text-slate-600
                                   focus:border-slate-200
                                   focus:ring-0">

                    </div>

                    <div>

                        <label class="mb-2 block text-sm
                                   font-bold text-slate-700">

                            Call-Off Date

                        </label>

                        <input type="text"
                            value="{{ $callOff->call_off_date?->format('F d, Y') ?? 'Not assigned' }}"
                            readonly
                            class="w-full cursor-not-allowed
                                   rounded-xl border-slate-200
                                   bg-slate-100
                                   text-slate-600
                                   focus:border-slate-200
                                   focus:ring-0">

                    </div>

                    <div>

                        <label class="mb-2 block text-sm
                                   font-bold text-slate-700">

                            NEFA Number

                        </label>

                        <input type="text"
                            value="{{ $purchaseOrder?->nefa_number ?: 'Not available' }}"
                            readonly
                            class="w-full cursor-not-allowed
                                   rounded-xl border-slate-200
                                   bg-slate-100
                                   text-slate-600
                                   focus:border-slate-200
                                   focus:ring-0">

                    </div>

                    <div>

                        <label class="mb-2 block text-sm
                                   font-bold text-slate-700">

                            Actual Distribution Batch

                        </label>

                        <input type="text" value="Batch #{{ $batch?->id ?? '—' }}" readonly
                            class="w-full cursor-not-allowed
                                   rounded-xl border-slate-200
                                   bg-slate-100
                                   text-slate-600
                                   focus:border-slate-200
                                   focus:ring-0">

                    </div>

                    <div>

                        <label class="mb-2 block text-sm
                                   font-bold text-slate-700">

                            Purchase Order

                        </label>

                        <input type="text"
                            value="{{ $purchaseOrder?->po_number ?: 'Not available' }}"
                            readonly
                            class="w-full cursor-not-allowed
                                   rounded-xl border-slate-200
                                   bg-slate-100
                                   text-slate-600
                                   focus:border-slate-200
                                   focus:ring-0">

                    </div>

                    <div>

                        <label class="mb-2 block text-sm
                                   font-bold text-slate-700">

                            Supplier

                        </label>

                        <input type="text"
                            value="{{ $supplier?->supplier_name ?: 'Not available' }}"
                            readonly
                            class="w-full cursor-not-allowed
                                   rounded-xl border-slate-200
                                   bg-slate-100
                                   text-slate-600
                                   focus:border-slate-200
                                   focus:ring-0">

                    </div>

                    <div>

                        <label class="mb-2 block text-sm
                                   font-bold text-slate-700">

                            Total PO Amount

                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute
                                       inset-y-0 left-0 flex
                                       items-center pl-4
                                       font-bold text-slate-500">
                                ₱
                            </span>

                            <input id="print_total_amount" type="number" name="print_total_amount"
                                value="{{ $printTotalAmount }}" min="0" max="9999999999999.99" step="0.01"
                                required
                                class="w-full rounded-xl
                                       border-slate-300
                                       pl-9 text-slate-800
                                       focus:border-[#339DCB]
                                       focus:ring-[#339DCB]">
                        </div>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            This changes only the amount shown in the
                            printed letter. It does not change the
                            Purchase Order record.
                        </p>

                        <x-input-error :messages="$errors->get('print_total_amount')" class="mt-2" />

                    </div>

                    <div>

                        <label class="mb-2 block text-sm
                                   font-bold text-slate-700">

                            Status

                        </label>

                        <input type="text"
                            value="{{ $callOff->status ?: 'Pending Approval' }}"
                            readonly
                            class="w-full cursor-not-allowed
                                   rounded-xl border-slate-200
                                   bg-slate-100
                                   text-slate-600
                                   focus:border-slate-200
                                   focus:ring-0">

                    </div>

                </div>

                {{-- Editable field --}}
                <div
                    class="rounded-2xl border
                           border-[#B7D6E6]
                           bg-[#F7FBFD] p-5">

                    <label for="nefa_title"
                        class="block text-sm
                               font-extrabold
                               text-[#143A52]">

                        NEFA Project Title

                    </label>

                    <p class="mt-1 text-xs
                               leading-5 text-slate-500">

                        This title will appear automatically
                        in the printed request letter.

                    </p>

                    <textarea id="nefa_title" name="nefa_title" rows="4" required
                        class="mt-4 w-full rounded-xl
                               border-slate-300
                               text-sm leading-6
                               focus:border-[#339DCB]
                               focus:ring-[#339DCB]">{{ $nefaTitle }}</textarea>

                    <x-input-error :messages="$errors->get('nefa_title')" class="mt-2" />

                </div>

                {{-- Editable print margins --}}
                <div
                    class="rounded-2xl border
                           border-[#B7D6E6]
                           bg-[#F7FBFD] p-5">

                    <div>
                        <h2 class="text-sm font-extrabold text-[#143A52]">
                            A4 Paper Margins
                        </h2>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Enter the print margins in millimeters.
                            The bottom margin cannot be lower than
                            27 mm because the footer image occupies
                            the bottom of the page.
                        </p>
                    </div>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                        <div>
                            <label for="print_margin_top"
                                class="mb-2 block text-sm
                                       font-bold text-slate-700">
                                Top Margin
                            </label>

                            <div class="relative">
                                <input id="print_margin_top" type="number" name="print_margin_top"
                                    value="{{ $printMarginTop }}" min="0" max="50" step="0.5" required
                                    class="w-full rounded-xl
                                           border-slate-300 pr-12
                                           focus:border-[#339DCB]
                                           focus:ring-[#339DCB]">

                                <span
                                    class="pointer-events-none absolute
                                           inset-y-0 right-0 flex
                                           items-center pr-4
                                           text-xs font-bold text-slate-500">
                                    mm
                                </span>
                            </div>

                            <x-input-error :messages="$errors->get('print_margin_top')" class="mt-2" />
                        </div>

                        <div>
                            <label for="print_margin_right"
                                class="mb-2 block text-sm
                                       font-bold text-slate-700">
                                Right Margin
                            </label>

                            <div class="relative">
                                <input id="print_margin_right" type="number" name="print_margin_right"
                                    value="{{ $printMarginRight }}" min="0" max="50" step="0.5"
                                    required
                                    class="w-full rounded-xl
                                           border-slate-300 pr-12
                                           focus:border-[#339DCB]
                                           focus:ring-[#339DCB]">

                                <span
                                    class="pointer-events-none absolute
                                           inset-y-0 right-0 flex
                                           items-center pr-4
                                           text-xs font-bold text-slate-500">
                                    mm
                                </span>
                            </div>

                            <x-input-error :messages="$errors->get('print_margin_right')" class="mt-2" />
                        </div>

                        <div>
                            <label for="print_margin_bottom"
                                class="mb-2 block text-sm
                                       font-bold text-slate-700">
                                Bottom Margin
                            </label>

                            <div class="relative">
                                <input id="print_margin_bottom" type="number" name="print_margin_bottom"
                                    value="{{ $printMarginBottom }}" min="27" max="70" step="0.5"
                                    required
                                    class="w-full rounded-xl
                                           border-slate-300 pr-12
                                           focus:border-[#339DCB]
                                           focus:ring-[#339DCB]">

                                <span
                                    class="pointer-events-none absolute
                                           inset-y-0 right-0 flex
                                           items-center pr-4
                                           text-xs font-bold text-slate-500">
                                    mm
                                </span>
                            </div>

                            <x-input-error :messages="$errors->get('print_margin_bottom')" class="mt-2" />
                        </div>

                        <div>
                            <label for="print_margin_left"
                                class="mb-2 block text-sm
                                       font-bold text-slate-700">
                                Left Margin
                            </label>

                            <div class="relative">
                                <input id="print_margin_left" type="number" name="print_margin_left"
                                    value="{{ $printMarginLeft }}" min="0" max="50" step="0.5"
                                    required
                                    class="w-full rounded-xl
                                           border-slate-300 pr-12
                                           focus:border-[#339DCB]
                                           focus:ring-[#339DCB]">

                                <span
                                    class="pointer-events-none absolute
                                           inset-y-0 right-0 flex
                                           items-center pr-4
                                           text-xs font-bold text-slate-500">
                                    mm
                                </span>
                            </div>

                            <x-input-error :messages="$errors->get('print_margin_left')" class="mt-2" />
                        </div>

                    </div>

                </div>

                <div
                    class="flex flex-col gap-3
                           border-t border-slate-200
                           pt-6 sm:flex-row
                           sm:justify-end">

                    <a href="{{ route('tssd.call-off-letters.index') }}"
                        class="rounded-xl border
                               border-slate-300
                               px-5 py-3 text-center
                               text-sm font-bold
                               text-slate-700
                               transition hover:bg-slate-50">

                        Back

                    </a>

                    <button type="submit"
                        class="rounded-xl
                               bg-[#339DCB]
                               px-5 py-3 text-sm
                               font-bold text-white
                               transition hover:bg-[#247BA0]">

                        Save Letter Settings

                    </button>

                    <a href="{{ route('tssd.call-off-letters.print', $callOff) }}"
                        target="_blank"
                        class="rounded-xl
                               bg-[#143A52]
                               px-5 py-3 text-center
                               text-sm font-bold
                               text-white
                               transition hover:bg-[#247BA0]">

                        Open Print Preview

                    </a>

                </div>

            </form>

        </section>

    </div>

</x-po_dashboard_layout>
