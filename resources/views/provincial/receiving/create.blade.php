<x-po_dashboard_layout title="Receive PPE Delivery">

    @php
        $batch =
            $provinceDistribution
                ->distributionBatch;

        $callOff =
            $batch?->callOff;

        $purchaseOrder =
            $batch?->purchaseOrder;

        $supplier =
            $purchaseOrder?->supplier;

        $allocatedTotal =
            (int) $provinceDistribution
                ->items
                ->sum('quantity');

        $previouslyReceivedTotal =
            collect($previouslyReceivedByItem)
                ->sum();

        $remainingTotal =
            collect($remainingByItem)
                ->sum();

        $previousReceiptCount =
            $provinceDistribution
                ->deliveryReceipts
                ->count();
    @endphp

    <div class="mx-auto max-w-[1500px] space-y-6">

        {{-- =========================================================
            PAGE HEADER
        ========================================================== --}}
        <section
            class="relative overflow-hidden rounded-3xl border
                   border-slate-200 bg-white shadow-sm"
        >
            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b from-[#075985]
                       via-[#0284C7] to-[#38BDF8]"
            ></div>

            <div
                class="flex flex-col gap-5 px-6 py-7 sm:px-8
                       lg:flex-row lg:items-center
                       lg:justify-between"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-3">

                        <span
                            class="rounded-full bg-[#7DD3FC]/20
                                   px-3 py-1 text-xs font-bold
                                   uppercase tracking-wider
                                   text-[#0284C7]
                                   ring-1 ring-[#7DD3FC]"
                        >
                            Provincial Receiving
                        </span>

                        <span
                            class="rounded-full bg-slate-100
                                   px-3 py-1 text-xs font-semibold
                                   text-slate-700 ring-1
                                   ring-slate-200"
                        >
                            Delivery Receipt
                            #{{ $previousReceiptCount + 1 }}
                        </span>

                    </div>

                    <h1
                        class="mt-4 text-2xl font-bold
                               tracking-tight text-slate-950
                               sm:text-3xl"
                    >
                        Record PPE Delivery
                    </h1>

                    <p
                        class="mt-2 max-w-3xl text-sm
                               leading-6 text-slate-600"
                    >
                        Record another physical delivery under
                        Call-Off
                        <span class="font-bold text-slate-900">
                            {{ $callOff?->call_off_number ?? 'N/A' }}
                        </span>.
                        Quantities are validated against the remaining
                        receivable PPE balance.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">

                    <a
                        href="{{ route(
                            'provincial.receiving.show',
                            $provinceDistribution
                        ) }}"
                        class="inline-flex items-center justify-center
                               rounded-xl border border-slate-300
                               bg-white px-5 py-3 text-sm
                               font-bold text-slate-700 transition
                               hover:bg-slate-50"
                    >
                        View Allocation
                    </a>

                    <a
                        href="{{ route(
                            'provincial.receiving.index'
                        ) }}"
                        class="inline-flex items-center justify-center
                               rounded-xl bg-slate-900 px-5 py-3
                               text-sm font-bold text-white
                               transition hover:bg-slate-800"
                    >
                        Back to Allocations
                    </a>

                </div>
            </div>
        </section>

        {{-- =========================================================
            VALIDATION ERRORS
        ========================================================== --}}
        @if($errors->any())

            <section
                class="rounded-2xl border border-red-200
                       bg-red-50 px-6 py-5"
            >
                <h2 class="font-bold text-red-800">
                    Please correct the following:
                </h2>

                <ul
                    class="mt-3 list-disc space-y-1 pl-5
                           text-sm text-red-700"
                >
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </section>

        @endif

        {{-- =========================================================
            ALLOCATION SUMMARY CARDS
        ========================================================== --}}
        <section
            class="grid grid-cols-1 gap-4
                   sm:grid-cols-2 xl:grid-cols-4"
        >
            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-slate-400"
                >
                    Call-Off Number
                </p>

                <p
                    class="mt-3 text-xl font-bold
                           text-[#075985]"
                >
                    {{ $callOff?->call_off_number ?? 'Not available' }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Batch #{{ $batch?->id ?? 'N/A' }}
                </p>
            </article>

            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-slate-400"
                >
                    Source Purchase Order
                </p>

                <p
                    class="mt-3 text-xl font-bold
                           text-slate-950"
                >
                    {{ $purchaseOrder?->po_number ?? 'Not available' }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    {{ $supplier?->supplier_name ?? 'Supplier unavailable' }}
                </p>
            </article>

            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-slate-400"
                >
                    Previously Received
                </p>

                <p
                    class="mt-3 text-2xl font-bold
                           text-blue-700"
                >
                    {{ number_format($previouslyReceivedTotal) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Across
                    {{ number_format($previousReceiptCount) }}
                    previous Delivery Receipt(s)
                </p>
            </article>

            <article
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wider text-slate-400"
                >
                    Remaining Receivable
                </p>

                <p
                    class="mt-3 text-2xl font-bold
                           text-amber-700"
                >
                    {{ number_format($remainingTotal) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    From
                    {{ number_format($allocatedTotal) }}
                    allocated PPE
                </p>
            </article>
        </section>

        {{-- =========================================================
            MAIN FORM
        ========================================================== --}}
        <form
            id="deliveryReceiptForm"
            action="{{ route(
                'provincial.receiving.store',
                $provinceDistribution
            ) }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6"
        >
            @csrf

            {{-- Delivery information --}}
            <section
                class="overflow-hidden rounded-3xl border
                       border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="bg-[#0284C7] px-6 py-5 sm:px-7"
                >
                    <h2 class="text-xl font-bold text-white">
                        Delivery Receipt Information
                    </h2>

                    <p class="mt-1 text-sm text-sky-100">
                        Enter the information from the current physical
                        delivery.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 gap-6 p-6
                           sm:p-7 lg:grid-cols-2"
                >
                    <div>
                        <label
                            for="dr_number"
                            class="mb-2 block text-sm
                                   font-bold text-slate-700"
                        >
                            Delivery Receipt Number
                        </label>

                        <input
                            type="text"
                            id="dr_number"
                            name="dr_number"
                            value="{{ old('dr_number') }}"
                            required
                            maxlength="100"
                            autocomplete="off"
                            placeholder="Example: DR-2026-001"
                            class="w-full rounded-xl border-slate-300
                                   uppercase focus:border-[#0284C7]
                                   focus:ring-[#0284C7]"
                        >

                        @error('dr_number')
                            <p
                                class="mt-2 text-sm
                                       font-semibold text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="delivery_date"
                            class="mb-2 block text-sm
                                   font-bold text-slate-700"
                        >
                            Actual Delivery Date
                        </label>

                        <input
                            type="date"
                            id="delivery_date"
                            name="delivery_date"
                            value="{{ old(
                                'delivery_date',
                                now()->format('Y-m-d')
                            ) }}"
                            required
                            class="w-full rounded-xl border-slate-300
                                   focus:border-[#0284C7]
                                   focus:ring-[#0284C7]"
                        >

                        @error('delivery_date')
                            <p
                                class="mt-2 text-sm
                                       font-semibold text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="physical_receiver_name"
                            class="mb-2 block text-sm
                                   font-bold text-slate-700"
                        >
                            Physical Receiver Name
                        </label>

                        <input
                            type="text"
                            id="physical_receiver_name"
                            name="physical_receiver_name"
                            value="{{ old(
                                'physical_receiver_name',
                                auth()->user()->name
                            ) }}"
                            required
                            maxlength="255"
                            placeholder="Full name of the receiver"
                            class="w-full rounded-xl border-slate-300
                                   focus:border-[#0284C7]
                                   focus:ring-[#0284C7]"
                        >

                        @error('physical_receiver_name')
                            <p
                                class="mt-2 text-sm
                                       font-semibold text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="document"
                            class="mb-2 block text-sm
                                   font-bold text-slate-700"
                        >
                            Attach Document (PDF)
                            <span class="ml-1 text-xs font-normal text-slate-500">
                                Optional scanned copy of the
                                Delivery Receipt and Image of damaged items.
                            </span>
                        </label>

                        <input
                            type="file"
                            id="document"
                            name="document"
                            accept="application/pdf,.pdf"
                            required
                            class="w-full rounded-xl border
                                   border-slate-300 bg-white
                                   px-4 py-3 text-sm"
                        >

                        <p class="mt-2 text-xs text-slate-500">
                            PDF only. Maximum file size: 10 MB.
                        </p>

                        @error('document')
                            <p
                                class="mt-2 text-sm
                                       font-semibold text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label
                            for="remarks"
                            class="mb-2 block text-sm
                                   font-bold text-slate-700"
                        >
                            Delivery Remarks
                        </label>

                        <textarea
                            id="remarks"
                            name="remarks"
                            rows="4"
                            maxlength="5000"
                            placeholder="Optional discrepancy, shortage, condition, or delivery remarks."
                            class="w-full rounded-xl border-slate-300
                                   focus:border-[#0284C7]
                                   focus:ring-[#0284C7]"
                        >{{ old('remarks') }}</textarea>

                        @error('remarks')
                            <p
                                class="mt-2 text-sm
                                       font-semibold text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- PPE quantities --}}
            <section
                class="overflow-hidden rounded-3xl border
                       border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 bg-slate-900
                           px-6 py-5 sm:flex-row
                           sm:items-center sm:justify-between
                           sm:px-7"
                >
                    <div>
                        <h2 class="text-xl font-bold text-white">
                            PPE Quantities Received
                        </h2>

                        <p class="mt-1 text-sm text-slate-300">
                            Enter only the quantities received in this
                            Delivery Receipt.
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-white/10
                               px-4 py-3 text-right"
                    >
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wider text-slate-300"
                        >
                            Receiving Now
                        </p>

                        <p
                            id="receivingNowTotal"
                            class="mt-1 text-xl font-bold text-white"
                        >
                            0
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">

                    <table
                        class="min-w-[1050px] w-full
                               divide-y divide-slate-200"
                    >
                        <thead class="bg-slate-100">
                            <tr
                                class="text-xs font-bold uppercase
                                       tracking-wide text-slate-600"
                            >
                                <th class="px-5 py-4 text-left">
                                    PPE Item
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Size / Label
                                </th>

                                <th class="px-5 py-4 text-center">
                                    Allocation
                                </th>

                                <th class="px-5 py-4 text-center">
                                    Previously Received
                                </th>

                                <th class="px-5 py-4 text-center">
                                    Remaining
                                </th>

                                <th class="px-5 py-4 text-center">
                                    Receiving Now
                                </th>

                                <th class="px-5 py-4 text-center">
                                    Remaining After
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @foreach(
                                $provinceDistribution->items
                                as $allocationItem
                            )
                                @php
                                    $previouslyReceived = (int) (
                                        $previouslyReceivedByItem[
                                            $allocationItem->id
                                        ] ?? 0
                                    );

                                    $remaining = (int) (
                                        $remainingByItem[
                                            $allocationItem->id
                                        ] ?? 0
                                    );

                                    $itemName = trim(
                                        $allocationItem
                                            ->item
                                            ->item_name
                                        .' '
                                        .(
                                            $allocationItem
                                                ->item
                                                ->label
                                            ?? ''
                                        )
                                    );

                                    $oldQuantity = (int) old(
                                        'items.'.$allocationItem->id,
                                        0
                                    );
                                @endphp

                                <tr
                                    class="transition hover:bg-slate-50"
                                >
                                    <td
                                        class="px-5 py-4 font-semibold
                                               text-slate-900"
                                    >
                                        {{
                                            $allocationItem
                                                ->item
                                                ->item_name
                                        }}
                                    </td>

                                    <td
                                        class="px-5 py-4
                                               text-slate-600"
                                    >
                                        {{
                                            $allocationItem
                                                ->item
                                                ->label
                                            ?: '—'
                                        }}
                                    </td>

                                    <td
                                        class="px-5 py-4 text-center
                                               font-semibold
                                               text-slate-900"
                                    >
                                        {{
                                            number_format(
                                                $allocationItem
                                                    ->quantity
                                            )
                                        }}
                                    </td>

                                    <td
                                        class="px-5 py-4 text-center
                                               font-semibold
                                               text-blue-700"
                                    >
                                        {{
                                            number_format(
                                                $previouslyReceived
                                            )
                                        }}
                                    </td>

                                    <td
                                        class="px-5 py-4 text-center
                                               font-bold text-amber-700"
                                    >
                                        {{ number_format($remaining) }}
                                    </td>

                                    <td class="px-5 py-4 text-center">

                                        <input
                                            type="number"
                                            name="items[{{ $allocationItem->id }}]"
                                            value="{{ $oldQuantity }}"
                                            min="0"
                                            max="{{ $remaining }}"
                                            step="1"
                                            inputmode="numeric"
                                            data-receiving-input
                                            data-remaining="{{ $remaining }}"
                                            data-item-name="{{ $itemName }}"
                                            @readonly($remaining <= 0)
                                            class="w-28 rounded-lg
                                                   border-slate-300
                                                   text-center
                                                   focus:border-[#0284C7]
                                                   focus:ring-[#0284C7]
                                                   read-only:cursor-not-allowed
                                                   read-only:bg-slate-100
                                                   read-only:text-slate-400"
                                        >

                                        <p
                                            data-receiving-error
                                            class="mt-2 hidden text-xs
                                                   font-semibold
                                                   text-red-600"
                                        ></p>

                                        @error(
                                            'items.'.$allocationItem->id
                                        )
                                            <p
                                                class="mt-2 text-xs
                                                       font-semibold
                                                       text-red-600"
                                            >
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <span
                                            data-remaining-after
                                            class="font-semibold
                                                   text-green-700"
                                        >
                                            {{
                                                number_format(
                                                    max(
                                                        0,
                                                        $remaining
                                                        - $oldQuantity
                                                    )
                                                )
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>

                        <tfoot class="bg-slate-100">
                            <tr>
                                <td
                                    colspan="5"
                                    class="px-5 py-4 text-right
                                           font-bold text-slate-700"
                                >
                                    Total PPE Receiving Now
                                </td>

                                <td
                                    class="px-5 py-4 text-center
                                           text-lg font-bold
                                           text-[#0284C7]"
                                >
                                    <span id="receivingNowTotalFooter">
                                        0
                                    </span>
                                </td>

                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @error('items')
                    <p
                        class="border-t border-red-100
                               bg-red-50 px-7 py-4
                               text-sm font-semibold
                               text-red-700"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </section>

            {{-- Previous DRs --}}
            @if($provinceDistribution->deliveryReceipts->isNotEmpty())

                <section
                    class="overflow-hidden rounded-3xl border
                           border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-200
                               px-6 py-5 sm:px-7"
                    >
                        <h2
                            class="text-lg font-bold text-slate-950"
                        >
                            Previous Delivery Receipts
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Earlier physical deliveries recorded under
                            this Call-Off allocation.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table
                            class="min-w-[750px] w-full
                                   divide-y divide-slate-200"
                        >
                            <thead class="bg-slate-50">
                                <tr
                                    class="text-xs font-bold uppercase
                                           tracking-wide text-slate-500"
                                >
                                    <th class="px-6 py-4 text-left">
                                        DR Number
                                    </th>

                                    <th class="px-6 py-4 text-left">
                                        Delivery Date
                                    </th>

                                    <th class="px-6 py-4 text-left">
                                        Receiver
                                    </th>

                                    <th class="px-6 py-4 text-center">
                                        PPE Received
                                    </th>

                                    <th class="px-6 py-4 text-center">
                                        Document
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @foreach(
                                    $provinceDistribution->deliveryReceipts
                                    as $previousReceipt
                                )
                                    <tr class="hover:bg-slate-50">
                                        <td
                                            class="px-6 py-4
                                                   font-semibold
                                                   text-slate-900"
                                        >
                                            {{ $previousReceipt->dr_number }}
                                        </td>

                                        <td
                                            class="px-6 py-4
                                                   text-slate-600"
                                        >
                                            {{
                                                $previousReceipt
                                                    ->delivery_date
                                                    ?->format('M d, Y')
                                                ?? '—'
                                            }}
                                        </td>

                                        <td
                                            class="px-6 py-4
                                                   text-slate-600"
                                        >
                                            {{
                                                $previousReceipt
                                                    ->physical_receiver_name
                                                ?? $previousReceipt
                                                    ->receivedByUser
                                                    ?->name
                                                ?? '—'
                                            }}
                                        </td>

                                        <td
                                            class="px-6 py-4 text-center
                                                   font-bold
                                                   text-blue-700"
                                        >
                                            {{
                                                number_format(
                                                    $previousReceipt
                                                        ->items
                                                        ->sum(
                                                            'received_quantity'
                                                        )
                                                )
                                            }}
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            @if($previousReceipt->document)
                                                <a
                                                    href="{{ asset(
                                                        'storage/'
                                                        .$previousReceipt
                                                            ->document
                                                    ) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="inline-flex
                                                           rounded-lg
                                                           border
                                                           border-slate-300
                                                           bg-white px-4
                                                           py-2 text-xs
                                                           font-bold
                                                           text-slate-700
                                                           hover:bg-slate-50"
                                                >
                                                    View PDF
                                                </a>
                                            @else
                                                <span
                                                    class="text-sm
                                                           text-slate-400"
                                                >
                                                    —
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

            @endif

            {{-- Form actions --}}
            <div
                class="flex flex-col-reverse gap-3
                       sm:flex-row sm:justify-end"
            >
                <a
                    href="{{ route(
                        'provincial.receiving.show',
                        $provinceDistribution
                    ) }}"
                    class="inline-flex items-center justify-center
                           rounded-xl border border-slate-300
                           bg-white px-6 py-3 font-bold
                           text-slate-700 transition
                           hover:bg-slate-50"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    id="submitDeliveryReceiptButton"
                    disabled
                    class="inline-flex items-center justify-center
                           rounded-xl bg-[#0284C7] px-7 py-3
                           font-bold text-white transition
                           hover:bg-[#075985]
                           disabled:cursor-not-allowed
                           disabled:opacity-50"
                >
                    Save Delivery Receipt
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const form =
                    document.getElementById(
                        'deliveryReceiptForm'
                    );

                if (!form) {
                    return;
                }

                const inputs = Array.from(
                    form.querySelectorAll(
                        '[data-receiving-input]'
                    )
                );

                const totalHeader =
                    document.getElementById(
                        'receivingNowTotal'
                    );

                const totalFooter =
                    document.getElementById(
                        'receivingNowTotalFooter'
                    );

                const submitButton =
                    document.getElementById(
                        'submitDeliveryReceiptButton'
                    );

                function updateReceivingPreview() {
                    let total = 0;
                    let valid = true;
                    let hasQuantity = false;

                    inputs.forEach(input => {
                        const remaining = Number(
                            input.dataset.remaining || 0
                        );

                        const itemName =
                            input.dataset.itemName
                            || 'PPE item';

                        let quantity = Number(
                            input.value || 0
                        );

                        const row =
                            input.closest('tr');

                        const remainingAfterOutput =
                            row?.querySelector(
                                '[data-remaining-after]'
                            );

                        const errorOutput =
                            row?.querySelector(
                                '[data-receiving-error]'
                            );

                        if (
                            !Number.isFinite(quantity)
                            || quantity < 0
                        ) {
                            quantity = 0;
                            input.value = 0;
                        }

                        quantity =
                            Math.floor(quantity);

                        input.value = quantity;

                        const exceeds =
                            quantity > remaining;

                        const remainingAfter =
                            remaining - quantity;

                        if (quantity > 0) {
                            hasQuantity = true;
                        }

                        if (remainingAfterOutput) {
                            remainingAfterOutput.textContent =
                                Math.max(
                                    0,
                                    remainingAfter
                                ).toLocaleString();

                            remainingAfterOutput.className =
                                exceeds
                                    ? 'font-semibold text-red-700'
                                    : 'font-semibold text-green-700';
                        }

                        if (exceeds) {
                            input.classList.add(
                                'border-red-500',
                                'bg-red-50',
                                'text-red-900'
                            );

                            input.setAttribute(
                                'aria-invalid',
                                'true'
                            );

                            if (errorOutput) {
                                errorOutput.textContent =
                                    `${itemName} has only `
                                    +`${remaining.toLocaleString()} `
                                    +'remaining to receive.';

                                errorOutput.classList.remove(
                                    'hidden'
                                );
                            }

                            valid = false;
                        } else {
                            input.classList.remove(
                                'border-red-500',
                                'bg-red-50',
                                'text-red-900'
                            );

                            input.removeAttribute(
                                'aria-invalid'
                            );

                            if (errorOutput) {
                                errorOutput.textContent = '';

                                errorOutput.classList.add(
                                    'hidden'
                                );
                            }
                        }

                        total += quantity;
                    });

                    if (!hasQuantity || total <= 0) {
                        valid = false;
                    }

                    const formattedTotal =
                        total.toLocaleString();

                    if (totalHeader) {
                        totalHeader.textContent =
                            formattedTotal;
                    }

                    if (totalFooter) {
                        totalFooter.textContent =
                            formattedTotal;
                    }

                    if (submitButton) {
                        submitButton.disabled =
                            !valid;
                    }

                    return {
                        valid,
                        total,
                    };
                }

                inputs.forEach(input => {
                    input.addEventListener(
                        'input',
                        updateReceivingPreview
                    );
                });

                form.addEventListener(
                    'submit',
                    function (event) {
                        const result =
                            updateReceivingPreview();

                        if (!result.valid) {
                            event.preventDefault();

                            const invalidInput =
                                inputs.find(input => {
                                    const quantity =
                                        Number(
                                            input.value || 0
                                        );

                                    const remaining =
                                        Number(
                                            input.dataset
                                                .remaining || 0
                                        );

                                    return quantity
                                        > remaining;
                                });

                            if (invalidInput) {
                                invalidInput.focus();

                                return;
                            }

                            alert(
                                'Enter at least one received '
                                +'PPE quantity greater than zero.'
                            );

                            return;
                        }

                        if (submitButton) {
                            submitButton.disabled = true;

                            submitButton.textContent =
                                'Saving Delivery Receipt...';
                        }
                    }
                );

                updateReceivingPreview();
            }
        );
    </script>

</x-po_dashboard_layout>