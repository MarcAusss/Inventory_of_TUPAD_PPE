<x-po_dashboard_layout title="Create Project PPE Designation">

    @php
        $deliveryReceipts =
            $deliveryReceipts ?? collect();

        $selectedDeliveryReceipt =
            $selectedDeliveryReceipt ?? null;

        $selectedDeliveryReceiptId = (int) (
            $selectedDeliveryReceiptId ?? 0
        );

        $balances =
            $balances ?? [];

        $allocation =
            $selectedDeliveryReceipt
                ?->provinceDistribution;

        $batch =
            $allocation
                ?->distributionBatch;

        $callOff =
            $batch
                ?->callOff;

        $purchaseOrder =
            $batch
                ?->purchaseOrder;

        $supplier =
            $purchaseOrder
                ?->supplier;

        $actualReceivedTotal =
            collect($balances)
                ->sum('actual_received');

        $previouslyDistributedTotal =
            collect($balances)
                ->sum('previously_distributed');

        $availableTotal =
            collect($balances)
                ->sum('available_for_projects');
    @endphp

    <div class="mx-auto max-w-[1600px] space-y-6">

        {{-- Header --}}
        <section
            class="relative overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b from-[#075985]
                       via-[#0284C7] to-[#38BDF8]"
            ></div>

            <div
                class="flex flex-col gap-5 px-6 py-7
                       sm:px-8 lg:flex-row
                       lg:items-center lg:justify-between"
            >
                <div>
                    <span
                        class="rounded-full bg-[#7DD3FC]/20
                               px-3 py-1 text-xs font-bold
                               uppercase tracking-[0.16em]
                               text-[#0284C7]
                               ring-1 ring-[#7DD3FC]"
                    >
                        Provincial Office
                    </span>

                    <h1
                        class="mt-4 text-2xl font-bold
                               tracking-tight text-slate-950
                               sm:text-3xl"
                    >
                        Create Project PPE Designation
                    </h1>

                    <p
                        class="mt-2 max-w-3xl text-sm
                               leading-6 text-slate-600"
                    >
                        Select one Delivery Receipt. PPE from other
                        Delivery Receipts will not be combined with the
                        selected receipt.
                    </p>
                </div>

                <a
                    href="{{ route(
                        'provincial.project-designations.index'
                    ) }}"
                    class="inline-flex items-center justify-center
                           rounded-xl border border-slate-300
                           bg-white px-5 py-3 text-sm font-bold
                           text-slate-700 transition
                           hover:bg-slate-50"
                >
                    Back to Project Designations
                </a>
            </div>
        </section>

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

        {{-- Delivery Receipt selector --}}
        <section
            class="overflow-hidden rounded-3xl
                   border border-slate-200 bg-white shadow-sm"
        >
            <div class="bg-[#0284C7] px-6 py-5 sm:px-7">
                <h2 class="text-xl font-bold text-white">
                    Select Source Delivery Receipt
                </h2>

                <p class="mt-1 text-sm text-sky-100">
                    Every Delivery Receipt is treated as a separate
                    project inventory source.
                </p>
            </div>

            <form
                method="GET"
                action="{{ route(
                    'provincial.project-designations.create'
                ) }}"
                class="p-6 sm:p-7"
            >
                <div
                    class="flex flex-col gap-4
                           lg:flex-row lg:items-end"
                >
                    <div class="flex-1">
                        <label
                            for="delivery_receipt_id"
                            class="mb-2 block text-sm
                                   font-bold text-slate-700"
                        >
                            Delivery Receipt
                        </label>

                        <select
                            id="delivery_receipt_id"
                            name="delivery_receipt_id"
                            required
                            class="w-full rounded-xl border-slate-300
                                   focus:border-[#0284C7]
                                   focus:ring-[#0284C7]"
                        >
                            <option value="">
                                Select a Delivery Receipt
                            </option>

                            @foreach(
                                $deliveryReceipts as $receipt
                            )
                                @php
                                    $receiptAllocation =
                                        $receipt
                                            ->provinceDistribution;

                                    $receiptBatch =
                                        $receiptAllocation
                                            ?->distributionBatch;

                                    $receiptCallOff =
                                        $receiptBatch
                                            ?->callOff;

                                    $receiptPo =
                                        $receiptBatch
                                            ?->purchaseOrder;

                                    $receiptSupplier =
                                        $receiptPo
                                            ?->supplier;

                                    $receiptAvailable = (int) (
                                        $receipt
                                            ->available_for_projects_total
                                        ?? 0
                                    );
                                @endphp

                                <option
                                    value="{{ $receipt->id }}"
                                    @selected(
                                        $selectedDeliveryReceiptId
                                        === (int) $receipt->id
                                    )
                                >
                                    {{ $receipt->dr_number }}
                                    —
                                    {{
                                        $receiptCallOff
                                            ?->call_off_number
                                        ?? 'No Call-Off'
                                    }}
                                    —
                                    {{
                                        $receipt
                                            ->delivery_date
                                            ?->format('M d, Y')
                                        ?? 'No date'
                                    }}
                                    —
                                    {{
                                        $receiptSupplier
                                            ?->supplier_name
                                        ?? 'Supplier unavailable'
                                    }}
                                    —
                                    {{
                                        number_format(
                                            $receiptAvailable
                                        )
                                    }}
                                    PPE available
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center
                               rounded-xl bg-slate-900 px-6 py-3
                               font-bold text-white transition
                               hover:bg-slate-800"
                    >
                        Load Delivery Receipt
                    </button>
                </div>
            </form>
        </section>

        @if($deliveryReceipts->isEmpty())
            <section
                class="rounded-3xl border border-amber-200
                       bg-amber-50 px-6 py-12 text-center"
            >
                <h2 class="text-xl font-bold text-amber-900">
                    No Delivery Receipt inventory is available
                </h2>

                <p
                    class="mx-auto mt-2 max-w-2xl text-sm
                           leading-6 text-amber-800"
                >
                    A received Delivery Receipt will appear here when
                    it belongs to your Provincial Office and still has
                    PPE available for project distribution.
                </p>
            </section>
        @endif

        @if($selectedDeliveryReceipt)

            {{-- Selected receipt summary --}}
            <section
                class="grid grid-cols-1 gap-4
                       sm:grid-cols-2 xl:grid-cols-6"
            >
                @foreach([
                    [
                        'label' => 'Delivery Receipt',
                        'value' => $selectedDeliveryReceipt->dr_number,
                    ],
                    [
                        'label' => 'Call-Off Number',
                        'value' => $callOff?->call_off_number ?? '—',
                    ],
                    [
                        'label' => 'Purchase Order',
                        'value' => $purchaseOrder?->po_number ?? '—',
                    ],
                    [
                        'label' => 'Actual Received',
                        'value' => number_format($actualReceivedTotal),
                    ],
                    [
                        'label' => 'Previously Distributed',
                        'value' => number_format($previouslyDistributedTotal),
                    ],
                    [
                        'label' => 'Available from this DR',
                        'value' => number_format($availableTotal),
                    ],
                ] as $card)
                    <article
                        class="rounded-2xl border border-slate-200
                               bg-white p-5 shadow-sm"
                    >
                        <p
                            class="text-xs font-bold uppercase
                                   tracking-wider text-slate-400"
                        >
                            {{ $card['label'] }}
                        </p>

                        <p
                            class="mt-3 text-lg font-bold
                                   text-[#075985]"
                        >
                            {{ $card['value'] }}
                        </p>
                    </article>
                @endforeach
            </section>

            <form
                id="projectDesignationForm"
                action="{{ route(
                    'provincial.project-designations.store'
                ) }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-6"
            >
                @csrf

                <input
                    type="hidden"
                    name="delivery_receipt_id"
                    value="{{ $selectedDeliveryReceipt->id }}"
                >

                {{-- Project information --}}
                <section
                    class="overflow-hidden rounded-3xl
                           border border-slate-200
                           bg-white shadow-sm"
                >
                    <div class="bg-[#0284C7] px-6 py-5 sm:px-7">
                        <h2 class="text-xl font-bold text-white">
                            Project Information
                        </h2>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-6 p-6
                               sm:p-7 lg:grid-cols-2"
                    >
                        <div>
                            <label
                                for="project_code"
                                class="mb-2 block text-sm
                                       font-bold text-slate-700"
                            >
                                Project Code
                            </label>

                            <input
                                type="text"
                                id="project_code"
                                name="project_code"
                                value="{{ old('project_code') }}"
                                required
                                maxlength="255"
                                class="w-full rounded-xl
                                       border-slate-300 uppercase
                                       focus:border-[#0284C7]
                                       focus:ring-[#0284C7]"
                            >
                        </div>

                        <div>
                            <label
                                for="designation_date"
                                class="mb-2 block text-sm
                                       font-bold text-slate-700"
                            >
                                Designation Date
                            </label>

                            <input
                                type="date"
                                id="designation_date"
                                name="designation_date"
                                value="{{ old(
                                    'designation_date',
                                    now()->format('Y-m-d')
                                ) }}"
                                required
                                class="w-full rounded-xl
                                       border-slate-300
                                       focus:border-[#0284C7]
                                       focus:ring-[#0284C7]"
                            >
                        </div>

                        <div class="lg:col-span-2">
                            <label
                                for="project_title"
                                class="mb-2 block text-sm
                                       font-bold text-slate-700"
                            >
                                Project Title
                            </label>

                            <input
                                type="text"
                                id="project_title"
                                name="project_title"
                                value="{{ old('project_title') }}"
                                required
                                maxlength="255"
                                class="w-full rounded-xl
                                       border-slate-300
                                       focus:border-[#0284C7]
                                       focus:ring-[#0284C7]"
                            >
                        </div>

                        <div class="lg:col-span-2">
                            <label
                                for="location"
                                class="mb-2 block text-sm
                                       font-bold text-slate-700"
                            >
                                Location
                            </label>

                            <input
                                type="text"
                                id="location"
                                name="location"
                                value="{{ old('location') }}"
                                required
                                maxlength="255"
                                class="w-full rounded-xl
                                       border-slate-300
                                       focus:border-[#0284C7]
                                       focus:ring-[#0284C7]"
                            >
                        </div>

                        <div>
                            <label
                                for="number_of_days"
                                class="mb-2 block text-sm
                                       font-bold text-slate-700"
                            >
                                Number of Days
                            </label>

                            <input
                                type="number"
                                id="number_of_days"
                                name="number_of_days"
                                value="{{ old('number_of_days', 1) }}"
                                required
                                min="1"
                                class="w-full rounded-xl
                                       border-slate-300
                                       focus:border-[#0284C7]
                                       focus:ring-[#0284C7]"
                            >
                        </div>

                        <div>
                            <label
                                for="number_of_beneficiaries"
                                class="mb-2 block text-sm
                                       font-bold text-slate-700"
                            >
                                Number of Beneficiaries
                            </label>

                            <input
                                type="number"
                                id="number_of_beneficiaries"
                                name="number_of_beneficiaries"
                                value="{{ old(
                                    'number_of_beneficiaries',
                                    1
                                ) }}"
                                required
                                min="1"
                                class="w-full rounded-xl
                                       border-slate-300
                                       focus:border-[#0284C7]
                                       focus:ring-[#0284C7]"
                            >
                        </div>
                    </div>
                </section>

                {{-- PPE table --}}
                <section
                    class="overflow-hidden rounded-3xl
                           border border-slate-200
                           bg-white shadow-sm"
                >
                    <div
                        class="flex flex-col gap-3 bg-[#0284C7]
                               px-6 py-5 sm:flex-row
                               sm:items-center
                               sm:justify-between sm:px-7"
                    >
                        <div>
                            <h2 class="text-xl font-bold text-white">
                                PPE to Distribute
                            </h2>

                            <p class="mt-1 text-sm text-sky-100">
                                Quantities are limited to the selected
                                Delivery Receipt only.
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-white/10
                                   px-4 py-2 text-white"
                        >
                            <span class="text-xs font-bold uppercase">
                                Total for this project
                            </span>

                            <strong
                                id="projectTotal"
                                class="ml-2 text-xl"
                            >
                                0
                            </strong>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-[1200px] w-full">
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

                                    <th class="px-5 py-4 text-left">
                                        Unit
                                    </th>

                                    <th class="px-5 py-4 text-center">
                                        Actual Received in DR
                                    </th>

                                    <th class="px-5 py-4 text-center">
                                        Previously Distributed
                                    </th>

                                    <th class="px-5 py-4 text-center">
                                        Available from DR
                                    </th>

                                    <th class="px-5 py-4 text-center">
                                        Project Quantity
                                    </th>

                                    <th class="px-5 py-4 text-center">
                                        Remaining After
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @foreach($balances as $itemId => $balance)
                                    @php
                                        $item = $balance['item'];

                                        $available = (int) (
                                            $balance[
                                                'available_for_projects'
                                            ] ?? 0
                                        );

                                        $oldQuantity = (int) old(
                                            "items.{$itemId}",
                                            0
                                        );
                                    @endphp

                                    <tr class="hover:bg-slate-50">
                                        <td
                                            class="px-5 py-4 font-semibold
                                                   text-slate-900"
                                        >
                                            {{ $item?->item_name ?? 'PPE Item' }}
                                        </td>

                                        <td class="px-5 py-4 text-slate-600">
                                            {{ $item?->label ?: '—' }}
                                        </td>

                                        <td class="px-5 py-4 text-slate-600">
                                            {{
                                                $item
                                                    ?->unit_of_measurement
                                                ?? '—'
                                            }}
                                        </td>

                                        <td
                                            class="px-5 py-4 text-center
                                                   font-bold text-blue-700"
                                        >
                                            {{
                                                number_format(
                                                    $balance[
                                                        'actual_received'
                                                    ] ?? 0
                                                )
                                            }}
                                        </td>

                                        <td
                                            class="px-5 py-4 text-center
                                                   font-semibold
                                                   text-amber-700"
                                        >
                                            {{
                                                number_format(
                                                    $balance[
                                                        'previously_distributed'
                                                    ] ?? 0
                                                )
                                            }}
                                        </td>

                                        <td
                                            class="px-5 py-4 text-center
                                                   font-bold text-green-700"
                                        >
                                            {{ number_format($available) }}
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <input
                                                type="number"
                                                name="items[{{ $itemId }}]"
                                                value="{{ $oldQuantity }}"
                                                min="0"
                                                max="{{ $available }}"
                                                data-project-quantity
                                                data-available="{{ $available }}"
                                                data-remaining-target="remaining-{{ $itemId }}"
                                                class="w-28 rounded-xl
                                                       border-slate-300
                                                       text-center
                                                       focus:border-[#0284C7]
                                                       focus:ring-[#0284C7]"
                                            >

                                            @error("items.{$itemId}")
                                                <p
                                                    class="mt-2 max-w-xs
                                                           text-xs font-semibold
                                                           text-red-600"
                                                >
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </td>

                                        <td
                                            id="remaining-{{ $itemId }}"
                                            class="px-5 py-4 text-center
                                                   font-bold text-[#075985]"
                                        >
                                            {{
                                                number_format(
                                                    max(
                                                        0,
                                                        $available
                                                            - $oldQuantity
                                                    )
                                                )
                                            }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Document --}}
                <section
                    class="overflow-hidden rounded-3xl
                           border border-slate-200
                           bg-white shadow-sm"
                >
                    <div class="grid gap-6 p-6 sm:p-7 lg:grid-cols-2">
                        <div>
                            <label
                                for="are_document"
                                class="mb-2 block text-sm
                                       font-bold text-slate-700"
                            >
                                ARE PDF Document
                            </label>

                            <input
                                type="file"
                                id="are_document"
                                name="are_document"
                                accept="application/pdf"
                                required
                                class="w-full rounded-xl
                                       border border-slate-300
                                       bg-white p-3"
                            >
                        </div>

                        <div>
                            <label
                                for="remarks"
                                class="mb-2 block text-sm
                                       font-bold text-slate-700"
                            >
                                Remarks
                            </label>

                            <textarea
                                id="remarks"
                                name="remarks"
                                rows="4"
                                maxlength="2000"
                                class="w-full rounded-xl
                                       border-slate-300
                                       focus:border-[#0284C7]
                                       focus:ring-[#0284C7]"
                            >{{ old('remarks') }}</textarea>
                        </div>
                    </div>
                </section>

                <div class="flex justify-end gap-3">
                    <a
                        href="{{ route(
                            'provincial.project-designations.index'
                        ) }}"
                        class="rounded-xl border border-slate-300
                               bg-white px-6 py-3 font-bold
                               text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="rounded-xl bg-[#0284C7]
                               px-6 py-3 font-bold text-white
                               transition hover:bg-[#075985]"
                    >
                        Save Project Designation
                    </button>
                </div>
            </form>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputs = Array.from(
                document.querySelectorAll(
                    '[data-project-quantity]'
                )
            );

            const totalElement =
                document.getElementById('projectTotal');

            const updateTotals = () => {
                let total = 0;

                inputs.forEach((input) => {
                    const available = Number(
                        input.dataset.available || 0
                    );

                    let quantity = Number(
                        input.value || 0
                    );

                    if (quantity < 0) {
                        quantity = 0;
                    }

                    if (quantity > available) {
                        quantity = available;
                    }

                    input.value = quantity;

                    total += quantity;

                    const remainingElement =
                        document.getElementById(
                            input.dataset.remainingTarget
                        );

                    if (remainingElement) {
                        remainingElement.textContent =
                            new Intl.NumberFormat().format(
                                Math.max(
                                    0,
                                    available - quantity
                                )
                            );
                    }
                });

                if (totalElement) {
                    totalElement.textContent =
                        new Intl.NumberFormat().format(
                            total
                        );
                }
            };

            inputs.forEach((input) => {
                input.addEventListener(
                    'input',
                    updateTotals
                );
            });

            updateTotals();
        });
    </script>

</x-po_dashboard_layout>