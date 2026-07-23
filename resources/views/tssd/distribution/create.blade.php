<x-po_dashboard_layout title="Create TSSD Distribution">

    <form id="distributionForm" action="{{ route('tssd.distributions.store') }}" method="POST">
        @csrf

        <input type="hidden" id="distributionsInput" name="distributions">

        <div class="mx-auto max-w-[1900px] space-y-6">

            <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#2D94BE] to-[#339DCB]">
                </div>

                <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span
                                class="rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#143A52] ring-1 ring-[#90C4DD]">
                                TSSD Unit
                            </span>

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                Distribution
                            </span>
                        </div>

                        <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                            Create TSSD Distribution
                        </h1>

                        <p class="mt-2 max-w-3xl text-sm leading-6 text-[#36566E]">
                            Select a Purchase Order, review available PPE quantities, and assign items to provincial
                            offices.
                        </p>
                    </div>

                    <a href="{{ route('tssd.distributions.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-[#F3FAFD]">
                        Back to Distribution
                    </a>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">
                        Purchase Order
                    </p>

                    <h2 class="mt-1 text-lg font-bold text-slate-950">
                        Purchase Order Information
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Select the Purchase Order that will be used as the source of this distribution.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 p-6 sm:p-7 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label for="purchase_order" class="mb-2 block text-sm font-bold text-slate-700">
                            Purchase Order Number
                        </label>

                        <select id="purchase_order" name="purchase_order_id"
                            class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]">
                            <option value="">Select Purchase Order Number</option>

                            @foreach ($purchaseOrders as $po)
                                <option value="{{ $po->id }}">
                                    {{ $po->po_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="po_date" class="mb-2 block text-sm font-bold text-slate-700">PO Date</label>
                        <input id="po_date" readonly
                            class="w-full rounded-xl border-slate-200 bg-slate-100 text-[#36566E] shadow-sm">
                    </div>

                    <div>
                        <label for="supplier" class="mb-2 block text-sm font-bold text-slate-700">Supplier</label>
                        <input id="supplier" readonly
                            class="w-full rounded-xl border-slate-200 bg-slate-100 text-[#36566E] shadow-sm">
                    </div>

                    <div>
                        <label for="nefa" class="mb-2 block text-sm font-bold text-slate-700">NEFA Number</label>
                        <input id="nefa" readonly
                            class="w-full rounded-xl border-slate-200 bg-slate-100 text-[#36566E] shadow-sm">
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">
                        Purchased Inventory
                    </p>

                    <h2 class="mt-1 text-lg font-bold text-slate-950">
                        Purchased PPE Summary
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Review the purchased and remaining quantities before assigning PPE to provinces.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[760px] w-full divide-y divide-slate-200">
                        <thead class="bg-[#B7D6E6]/35">
                            <tr class="text-xs font-bold uppercase tracking-wide text-[#36566E]">
                                <th class="px-6 py-4 text-left">PPE Item</th>
                                <th class="px-6 py-4 text-center">Size / Label</th>
                                <th class="px-6 py-4 text-center">Purchased Qty</th>
                                <th class="px-6 py-4 text-center">Remaining Qty</th>
                            </tr>
                        </thead>

                        <tbody id="purchaseSummary" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="4" class="px-6 py-14 text-center text-sm text-slate-500">
                                    Select a Purchase Order.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div
                    class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:px-7 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">
                            Provincial Allocations
                        </p>

                        <h2 class="mt-1 text-lg font-bold text-slate-950">
                            Province Distribution Summary
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Consolidated PPE quantities assigned to every provincial office in this distribution.
                        </p>
                    </div>

                    <button type="button" id="openModal"
                        class="inline-flex items-center justify-center rounded-xl bg-[#339DCB] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#2D94BE]">
                        Assign PPE to Province
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[1500px] w-full divide-y divide-slate-200">
                        <thead class="bg-[#B7D6E6]/35">
                            <tr class="text-xs font-bold uppercase tracking-wide text-[#36566E]">
                                <th class="px-5 py-4 text-left">Province</th>
                                <th class="px-5 py-4 text-left">Delivery Date</th>
                                <th class="px-5 py-4 text-left">Place of Delivery</th>
                                <th class="px-4 py-4 text-center">LS-M</th>
                                <th class="px-4 py-4 text-center">LS-L</th>
                                <th class="px-4 py-4 text-center">Bucket Hat</th>
                                <th class="px-4 py-4 text-center">US9</th>
                                <th class="px-4 py-4 text-center">US10</th>
                                <th class="px-4 py-4 text-center">Gloves</th>
                                <th class="px-4 py-4 text-center">Mask</th>
                                <th class="px-5 py-4 text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody id="distributionSummary" class="divide-y divide-slate-100">
                            @forelse($provinceDistributions as $provinceId => $rows)
                                @php
                                    $province = $rows->first()->province;

                                    $lsm = $rows
                                        ->filter(
                                            fn($r) => $r->item &&
                                                $r->item->item_name == 'Long Sleeve' &&
                                                $r->item->label == 'Medium',
                                        )
                                        ->sum('quantity');

                                    $lsl = $rows
                                        ->filter(
                                            fn($r) => $r->item &&
                                                $r->item->item_name == 'Long Sleeve' &&
                                                $r->item->label == 'Large',
                                        )
                                        ->sum('quantity');

                                    $bucket = $rows
                                        ->filter(fn($r) => $r->item && $r->item->item_name == 'Bucket Hat')
                                        ->sum('quantity');

                                    $us9 = $rows
                                        ->filter(
                                            fn($r) => $r->item &&
                                                $r->item->item_name == 'Rubber Boots' &&
                                                $r->item->label == 'US9',
                                        )
                                        ->sum('quantity');

                                    $us10 = $rows
                                        ->filter(
                                            fn($r) => $r->item &&
                                                $r->item->item_name == 'Rubber Boots' &&
                                                $r->item->label == 'US10',
                                        )
                                        ->sum('quantity');

                                    $gloves = $rows
                                        ->filter(fn($r) => $r->item && $r->item->item_name == 'Hand Gloves')
                                        ->sum('quantity');

                                    $mask = $rows
                                        ->filter(fn($r) => $r->item && $r->item->item_name == 'Mask')
                                        ->sum('quantity');
                                @endphp

                                <tr class="transition hover:bg-[#F3FAFD]">
                                    <td class="px-5 py-4 font-bold text-[#143A52]">{{ $province->name }}</td>
                                    <td class="px-5 py-4 text-sm text-slate-700">—</td>
                                    <td class="px-5 py-4 text-sm text-slate-700">{{ $province->deliveryLocation() }}
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-700">{{ $lsm }}</td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-700">{{ $lsl }}</td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-700">{{ $bucket }}</td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-700">{{ $us9 }}</td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-700">{{ $us10 }}</td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-700">{{ $gloves }}</td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-700">{{ $mask }}</td>
                                    <td class="px-5 py-4 text-center text-sm text-slate-400">—</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-14 text-center text-sm text-slate-500">
                                        No province assigned yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">
                        Notes
                    </p>

                    <h2 class="mt-1 text-lg font-bold text-slate-950">
                        Distribution Remarks
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Delivery dates and delivery locations are recorded separately for every provincial office.
                    </p>
                </div>

                <div class="p-6 sm:p-7">
                    <label for="remarks" class="mb-2 block text-sm font-bold text-slate-700">
                        Remarks
                    </label>

                    <textarea id="remarks" name="remarks" rows="3"
                        class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]"></textarea>
                </div>
            </section>

            <section
                class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:justify-end">
                <a href="{{ route('tssd.distributions.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-[#F3FAFD]">
                    Cancel
                </a>

                <button type="submit" id="submitDistributionButton"
                    class="inline-flex items-center justify-center rounded-xl bg-[#339DCB] px-7 py-3 text-sm font-bold text-white transition hover:bg-[#2D94BE] disabled:cursor-not-allowed disabled:opacity-60">
                    Save Distribution
                </button>
            </section>

        </div>
    </form>



<div id="assignModal"
    class="fixed inset-0 z-50 hidden items-center justify-center border-[#E4EEF5] p-4 backdrop-blur-sm">

    <div class="max-h-[92vh] w-full max-w-5xl overflow-hidden rounded-3xl border border-[#E4EEF5] bg-white shadow-2xl">

        <div class="flex items-center justify-between border-b border-[#E4EEF5] px-6 py-5 sm:px-7">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">
                    Provincial Allocation
                </p>

                <h2 class="mt-1 text-xl font-bold text-slate-950">
                    Assign PPE to Province
                </h2>
            </div>

            <button type="button" id="closeModal"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#E4EEF5] text-2xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900">
                &times;
            </button>
        </div>

        <div class="max-h-[68vh] overflow-y-auto p-6 sm:p-7">
            <div class="mb-6">
                <label for="provinceSelect" class="mb-2 block text-sm font-bold text-slate-700">
                    Province
                </label>

                <select id="provinceSelect"
                    class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]">
                    <option value="">Select Province</option>

                    @foreach ($provinces as $province)
                        <option value="{{ $province->id }}" data-name="{{ $province->name }}"
                            data-office-name="{{ $province->office_name }}"
                            data-address="{{ $province->deliveryLocation() }}">
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div>
                    <label for="scheduledDeliveryDate" class="mb-2 block text-sm font-bold text-slate-700">
                        Delivery Date
                    </label>
                    <input type="date" id="scheduledDeliveryDate"
                        class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]">
                </div>

                <div>
                    <label for="placeOfDelivery" class="mb-2 block text-sm font-bold text-slate-700">
                        Place of Delivery
                    </label>
                    <textarea id="placeOfDelivery" rows="2" readonly
                        class="w-full resize-none rounded-xl border-slate-200 bg-slate-100 text-slate-700 shadow-sm"
                        placeholder="Select a province to load its office address."></textarea>
                </div>
            </div>

            @php
                $modalItems = [
                    ['lsm', 'Long Sleeve', 'Medium'],
                    ['lsl', 'Long Sleeve', 'Large'],
                    ['bucket', 'Bucket Hat', '—'],
                    ['us9', 'Rubber Boots', 'US9'],
                    ['us10', 'Rubber Boots', 'US10'],
                    ['gloves', 'Hand Gloves', '—'],
                    ['mask', 'Mask', '—'],
                ];
            @endphp

            <div class="overflow-hidden rounded-2xl border border-[#E4EEF5]">
                <table class="w-full divide-y divide-bg-[#F7FBFD]">
                    <thead class="bg-[#B7D6E6]/35">
                        <tr class="text-xs font-bold uppercase tracking-wide text-[#70879A]">
                            <th class="px-5 py-4 text-left">PPE Item</th>
                            <th class="px-5 py-4 text-center">Size / Label</th>
                            <th class="px-5 py-4 text-center">Quantity</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($modalItems as [$id, $itemName, $label])
                            <tr class="hover:bg-[#F3FAFD]">
                                <td class="px-5 py-4 font-semibold text-slate-800">{{ $itemName }}</td>
                                <td class="px-5 py-4 text-center text-sm text-[#2D94BE]">{{ $label }}</td>
                                <td class="px-5 py-4 text-center">
                                    <input type="number" id="{{ $id }}" value="0"
                                        class="w-28 rounded-xl border-slate-300 text-center shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div
            class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end sm:px-7">
            <button type="button" id="cancelAssign"
                class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                Cancel
            </button>

            <button type="button" id="saveAssign"
                class="rounded-xl bg-gradient-to-tr from-sky-700 via-sky-600 to-cyan-500 px-6 py-3 text-sm font-bold text-white transition hover:to-cyan-400">
                Add Province
            </button>
        </div>

    </div>
</div>

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {
            const purchaseOrders =
                @json($purchaseOrders);

            const distributionIndexUrl =
                @json(route('tssd.distributions.index'));

            const remainingUrlTemplate =
                @json(route('tssd.purchase-orders.remaining', [
                        'poId' => '__PO_ID__',
                    ]));

            const fieldDefinitions = {
                lsm: {
                    requestField: 'long_sleeve_medium',

                    label: 'Long Sleeve Medium',
                },

                lsl: {
                    requestField: 'long_sleeve_large',

                    label: 'Long Sleeve Large',
                },

                bucket: {
                    requestField: 'bucket_hat',

                    label: 'Bucket Hat',
                },

                us9: {
                    requestField: 'rubber_boots_us9',

                    label: 'Rubber Boots US9',
                },

                us10: {
                    requestField: 'rubber_boots_us10',

                    label: 'Rubber Boots US10',
                },

                gloves: {
                    requestField: 'hand_gloves',

                    label: 'Hand Gloves',
                },

                mask: {
                    requestField: 'mask',

                    label: 'Mask',
                },
            };

            const fields =
                Object.keys(
                    fieldDefinitions
                );

            const form =
                document.getElementById(
                    'distributionForm'
                );

            const purchaseOrderSelect =
                document.getElementById(
                    'purchase_order'
                );

            const purchaseSummary =
                document.getElementById(
                    'purchaseSummary'
                );

            const distributionSummary =
                document.getElementById(
                    'distributionSummary'
                );

            const provinceSelect =
                document.getElementById(
                    'provinceSelect'
                );

            const scheduledDeliveryDateInput =
                document.getElementById(
                    'scheduledDeliveryDate'
                );

            const placeOfDeliveryInput =
                document.getElementById(
                    'placeOfDelivery'
                );

            const distributionsInput =
                document.getElementById(
                    'distributionsInput'
                );

            const submitButton =
                document.getElementById(
                    'submitDistributionButton'
                );

            const saveAssignButton =
                document.getElementById(
                    'saveAssign'
                );

            const modal =
                document.getElementById(
                    'assignModal'
                );

            let selectedPO = null;

            let distributions = [];

            /*
             * The values returned by the backend already account for
             * previous saved distributions under this Purchase Order.
             *
             * This is the starting stock for the new form session.
             */
            let baseStock =
                emptyStock();

            /*
             * Always recalculated from:
             *
             * baseStock - total allocations in distributions[]
             */
            let remainingStock =
                emptyStock();

            /*
             * Null means a new allocation is being added.
             * An integer means an existing row is being edited.
             */
            let editingIndex = null;

            function emptyStock() {
                return {
                    lsm: 0,
                    lsl: 0,
                    bucket: 0,
                    us9: 0,
                    us10: 0,
                    gloves: 0,
                    mask: 0,
                };
            }

            function normaliseLabel(label) {
                const value = String(
                    label ?? ''
                ).trim();

                return value === '-' ?
                    '' :
                    value;
            }

            function getKey(
                itemName,
                label
            ) {
                if (
                    itemName === 'Long Sleeve' &&
                    label === 'Medium'
                ) {
                    return 'lsm';
                }

                if (
                    itemName === 'Long Sleeve' &&
                    label === 'Large'
                ) {
                    return 'lsl';
                }

                if (
                    itemName === 'Bucket Hat'
                ) {
                    return 'bucket';
                }

                if (
                    itemName === 'Rubber Boots' &&
                    label === 'US9'
                ) {
                    return 'us9';
                }

                if (
                    itemName === 'Rubber Boots' &&
                    label === 'US10'
                ) {
                    return 'us10';
                }

                if (
                    itemName === 'Hand Gloves'
                ) {
                    return 'gloves';
                }

                if (itemName === 'Mask') {
                    return 'mask';
                }

                return null;
            }

            function requestValue(
                distribution,
                field
            ) {
                const requestField =
                    fieldDefinitions[field]
                    .requestField;

                return Number(
                    distribution[
                        requestField
                    ] || 0
                );
            }

            function calculateAllocatedTotals(
                exceptIndex = null
            ) {
                const totals =
                    emptyStock();

                distributions.forEach(
                    (
                        distribution,
                        index
                    ) => {
                        if (
                            exceptIndex !== null &&
                            index === exceptIndex
                        ) {
                            return;
                        }

                        fields.forEach(
                            field => {
                                totals[field] +=
                                    requestValue(
                                        distribution,
                                        field
                                    );
                            }
                        );
                    }
                );

                return totals;
            }

            /**
             * Recalculate remaining stock exclusively from source data.
             *
             * remaining = baseStock - allocations
             */
            function recalculateRemainingStock() {
                const allocatedTotals =
                    calculateAllocatedTotals();

                const recalculated =
                    emptyStock();

                fields.forEach(field => {
                    recalculated[field] =
                        Number(
                            baseStock[field] ||
                            0
                        ) -
                        Number(
                            allocatedTotals[field] ||
                            0
                        );
                });

                remainingStock =
                    recalculated;

                updateRemainingUI();
                updateSubmitState();

                return remainingStock;
            }

            /**
             * Available quantity while adding or editing.
             *
             * During edit, the old values of that row are temporarily
             * excluded so the user may reuse them.
             */
            function stockAvailableForModal(
                field
            ) {
                const allocatedWithoutCurrent =
                    calculateAllocatedTotals(
                        editingIndex
                    );

                return Number(
                        baseStock[field] || 0
                    ) -
                    Number(
                        allocatedWithoutCurrent[
                            field
                        ] || 0
                    );
            }

            function combinedAllocationIsValid() {
                const allocatedTotals =
                    calculateAllocatedTotals();

                return fields.every(
                    field =>
                    Number(
                        allocatedTotals[
                            field
                        ] || 0
                    ) <=
                    Number(
                        baseStock[field] ||
                        0
                    )
                );
            }

            function buildValidationMessage(
                data
            ) {
                if (data?.errors) {
                    return Object
                        .values(data.errors)
                        .flat()
                        .join('\n');
                }

                return data?.message ||
                    'The request could not be completed.';
            }

            function openModal() {
                modal.classList.remove(
                    'hidden'
                );

                modal.classList.add(
                    'flex'
                );
            }

            function closeModal() {
                modal.classList.add(
                    'hidden'
                );

                modal.classList.remove(
                    'flex'
                );

                editingIndex = null;

                resetAssignmentInputs();

                provinceSelect.disabled =
                    false;

                saveAssignButton.textContent =
                    'Add Province';
            }

            function clearQuantityWarning(
                input
            ) {
                input.classList.remove(
                    'border-red-500',
                    'bg-red-50'
                );

                const warning =
                    input.parentElement
                    .querySelector(
                        '[data-quantity-warning]'
                    );

                if (warning) {
                    warning.textContent =
                        '';

                    warning.classList.add(
                        'hidden'
                    );
                }
            }

            function showQuantityWarning(
                input,
                message
            ) {
                input.classList.add(
                    'border-red-500',
                    'bg-red-50'
                );

                const warning =
                    input.parentElement
                    .querySelector(
                        '[data-quantity-warning]'
                    );

                if (warning) {
                    warning.textContent =
                        message;

                    warning.classList.remove(
                        'hidden'
                    );
                }
            }

            function resetAssignmentInputs() {
                fields.forEach(field => {
                    const input =
                        document.getElementById(
                            field
                        );

                    input.value = 0;

                    clearQuantityWarning(
                        input
                    );
                });

                provinceSelect.selectedIndex =
                    0;

                scheduledDeliveryDateInput.value = '';
                placeOfDeliveryInput.value = '';

                validateAssignmentForm();
            }

            function enableAllProvinceOptions() {
                Array.from(
                    provinceSelect.options
                ).forEach(option => {
                    option.disabled =
                        false;
                });

                provinceSelect.selectedIndex =
                    0;
            }

            function refreshProvinceOptions() {
                const assignedProvinceIds =
                    new Set(
                        distributions.map(
                            distribution =>
                            Number(
                                distribution
                                .province_id
                            )
                        )
                    );

                Array.from(
                    provinceSelect.options
                ).forEach(option => {
                    if (!option.value) {
                        option.disabled =
                            false;

                        return;
                    }

                    const provinceId =
                        Number(option.value);

                    const editingProvinceId =
                        editingIndex !== null ?
                        Number(
                            distributions[
                                editingIndex
                            ]?.province_id
                        ) :
                        null;

                    option.disabled =
                        assignedProvinceIds
                        .has(provinceId) &&
                        provinceId !==
                        editingProvinceId;
                });
            }

            function resetDistributionSummary() {
                distributionSummary.innerHTML = `
                    <tr id="emptyDistributionRow">
                        <td
                            colspan="11"
                            class="py-8 text-center text-gray-500"
                        >
                            No province assigned yet.
                        </td>
                    </tr>
                `;
            }

            function resetDistributionState() {
                selectedPO =
                    selectedPO;

                distributions = [];

                baseStock =
                    emptyStock();

                remainingStock =
                    emptyStock();

                editingIndex = null;

                enableAllProvinceOptions();
                resetAssignmentInputs();
                resetDistributionSummary();
                updateRemainingUI();
                updateSubmitState();
            }

            function updateRemainingUI() {
                document
                    .querySelectorAll(
                        '.remainingQty'
                    )
                    .forEach(cell => {
                        const row =
                            cell.closest('tr');

                        if (!row) {
                            return;
                        }

                        const name =
                            row.children[0]
                            ?.innerText
                            .trim() ||
                            '';

                        const label =
                            normaliseLabel(
                                row.children[1]
                                ?.innerText
                            );

                        const key =
                            getKey(
                                name,
                                label
                            );

                        if (!key) {
                            return;
                        }

                        const remaining =
                            Number(
                                remainingStock[
                                    key
                                ] || 0
                            );

                        cell.textContent =
                            Math.max(
                                0,
                                remaining
                            ).toLocaleString();

                        cell.className =
                            remaining < 0 ?
                            'remainingQty border px-4 py-2 text-center font-semibold text-red-700' :
                            'remainingQty border px-4 py-2 text-center font-semibold text-green-700';
                    });
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function formatDate(value) {
                if (!value) {
                    return '—';
                }

                const date = new Date(`${value}T00:00:00`);

                if (Number.isNaN(date.getTime())) {
                    return escapeHtml(value);
                }

                return date.toLocaleDateString('en-PH', {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit',
                });
            }

            function selectedProvinceOption() {
                return provinceSelect.options[provinceSelect.selectedIndex] || null;
            }

            function updateAutomaticDeliveryAddress() {
                const option = selectedProvinceOption();
                placeOfDeliveryInput.value = option?.dataset.address || '';
            }

            function renderDistributionSummary() {
                if (
                    distributions.length ===
                    0
                ) {
                    resetDistributionSummary();
                    refreshProvinceOptions();
                    recalculateRemainingStock();

                    return;
                }

                distributionSummary.innerHTML =
                    '';

                distributions.forEach(
                    (
                        distribution,
                        index
                    ) => {
                        const option =
                            Array.from(
                                provinceSelect.options
                            ).find(
                                provinceOption =>
                                Number(
                                    provinceOption.value
                                ) ===
                                Number(
                                    distribution
                                    .province_id
                                )
                            );

                        const provinceName =
                            option?.dataset.name ||
                            option?.textContent
                            ?.trim() ||
                            'Province';

                        distributionSummary
                            .insertAdjacentHTML(
                                'beforeend',
                                `
                                    <tr
                                        class="border-t hover:bg-gray-50"
                                        data-distribution-row="${index}"
                                    >
                                        <td class="px-4 py-3 font-semibold">
                                            ${provinceName}
                                        </td>

                                        <td class="px-4 py-3 text-sm text-slate-700">
                                            ${formatDate(distribution.scheduled_delivery_date)}
                                        </td>

                                        <td class="max-w-xs px-4 py-3 text-sm text-slate-700">
                                            ${escapeHtml(distribution.place_of_delivery || '—')}
                                        </td>

                                        <td class="text-center">
                                            ${requestValue(distribution, 'lsm')}
                                        </td>

                                        <td class="text-center">
                                            ${requestValue(distribution, 'lsl')}
                                        </td>

                                        <td class="text-center">
                                            ${requestValue(distribution, 'bucket')}
                                        </td>

                                        <td class="text-center">
                                            ${requestValue(distribution, 'us9')}
                                        </td>

                                        <td class="text-center">
                                            ${requestValue(distribution, 'us10')}
                                        </td>

                                        <td class="text-center">
                                            ${requestValue(distribution, 'gloves')}
                                        </td>

                                        <td class="text-center">
                                            ${requestValue(distribution, 'mask')}
                                        </td>

                                        <td class="px-3 py-3 text-center">
                                            <div class="flex justify-center gap-2">
                                                <button
                                                    type="button"
                                                    data-edit-index="${index}"
                                                    class="rounded-lg bg-blue-600 px-3 py-1 text-xs font-semibold text-white hover:bg-blue-700"
                                                >
                                                    Edit
                                                </button>

                                                <button
                                                    type="button"
                                                    data-remove-index="${index}"
                                                    class="rounded-lg bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-700"
                                                >
                                                    Remove
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `
                            );
                    }
                );

                refreshProvinceOptions();
                recalculateRemainingStock();
            }

            function readModalValues() {
                const values = {};

                fields.forEach(field => {
                    const input =
                        document.getElementById(
                            field
                        );

                    let value =
                        Number(
                            input.value || 0
                        );

                    if (
                        !Number.isFinite(
                            value
                        ) ||
                        value < 0
                    ) {
                        value = 0;
                    }

                    value =
                        Math.floor(value);

                    input.value =
                        value;

                    values[field] =
                        value;
                });

                return values;
            }

            function validateAssignmentForm() {
                let valid = true;
                let total = 0;

                fields.forEach(field => {
                    const input =
                        document.getElementById(
                            field
                        );

                    const value =
                        Number(
                            input.value || 0
                        );

                    const available =
                        stockAvailableForModal(
                            field
                        );

                    clearQuantityWarning(
                        input
                    );

                    if (
                        !Number.isInteger(
                            value
                        ) ||
                        value < 0
                    ) {
                        showQuantityWarning(
                            input,
                            'Enter a valid non-negative whole number.'
                        );

                        valid = false;
                    } else if (
                        value > available
                    ) {
                        showQuantityWarning(
                            input,
                            `${fieldDefinitions[field].label} has only ${Math.max(0, available).toLocaleString()} remaining.`
                        );

                        valid = false;
                    }

                    total +=
                        Number.isFinite(value) ?
                        value :
                        0;
                });

                if (total <= 0) {
                    valid = false;
                }

                if (!provinceSelect.value) {
                    valid = false;
                }

                if (!scheduledDeliveryDateInput.value) {
                    valid = false;
                }

                saveAssignButton.disabled = !valid;

                saveAssignButton.className =
                    valid ?
                    'bg-gradient-to-tr from-sky-700 via-sky-600 to-cyan-500 text-white px-6 py-2 rounded-xl' :
                    'bg-gray-400 cursor-not-allowed text-white px-6 py-2 rounded-xl';

                return valid;
            }

            function updateSubmitState() {
                const hasSelectedPO =
                    Boolean(selectedPO);

                const hasDistributions =
                    distributions.length > 0;

                const validCombinedTotals =
                    combinedAllocationIsValid();

                const everyProvinceHasDeliveryDate =
                    distributions.every(
                        distribution => Boolean(distribution.scheduled_delivery_date)
                    );

                submitButton.disabled = !hasSelectedPO ||
                    !hasDistributions ||
                    !validCombinedTotals ||
                    !everyProvinceHasDeliveryDate;
            }

            async function loadRemaining(
                poId
            ) {
                const url =
                    remainingUrlTemplate
                    .replace(
                        '__PO_ID__',
                        poId
                    );

                const response =
                    await fetch(
                        url, {
                            method: 'GET',

                            headers: {
                                Accept: 'application/json',

                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        }
                    );

                let data;

                try {
                    data =
                        await response.json();
                } catch (error) {
                    throw new Error(
                        'The server returned an invalid response while loading remaining quantities.'
                    );
                }

                if (!response.ok) {
                    throw new Error(
                        buildValidationMessage(
                            data
                        )
                    );
                }

                baseStock = {
                    lsm: Number(
                        data.remaining?.lsm ||
                        0
                    ),

                    lsl: Number(
                        data.remaining?.lsl ||
                        0
                    ),

                    bucket: Number(
                        data.remaining
                        ?.bucket ||
                        0
                    ),

                    us9: Number(
                        data.remaining?.us9 ||
                        0
                    ),

                    us10: Number(
                        data.remaining
                        ?.us10 ||
                        0
                    ),

                    gloves: Number(
                        data.remaining
                        ?.gloves ||
                        0
                    ),

                    mask: Number(
                        data.remaining?.mask ||
                        0
                    ),
                };

                recalculateRemainingStock();
                validateAssignmentForm();
            }

            purchaseOrderSelect
                .addEventListener(
                    'change',
                    async function() {
                        const id =
                            Number(this.value);

                        selectedPO =
                            purchaseOrders.find(
                                purchaseOrder =>
                                Number(
                                    purchaseOrder.id
                                ) === id
                            ) || null;

                        resetDistributionState();

                        if (!selectedPO) {
                            document
                                .getElementById(
                                    'po_date'
                                )
                                .value = '';

                            document
                                .getElementById(
                                    'supplier'
                                )
                                .value = '';

                            document
                                .getElementById(
                                    'nefa'
                                )
                                .value = '';

                            purchaseSummary.innerHTML = `
                                <tr>
                                    <td
                                        colspan="4"
                                        class="py-8 text-center text-gray-500"
                                    >
                                        Select a Purchase Order.
                                    </td>
                                </tr>
                            `;

                            return;
                        }

                        document
                            .getElementById(
                                'po_date'
                            )
                            .value =
                            selectedPO.po_date ??
                            '';

                        document
                            .getElementById(
                                'supplier'
                            )
                            .value =
                            selectedPO
                            .supplier
                            ?.supplier_name ??
                            '';

                        document
                            .getElementById(
                                'nefa'
                            )
                            .value =
                            selectedPO
                            .nefa_number ??
                            '';

                        const items =
                            selectedPO.items ||
                            selectedPO
                            .purchase_order_items ||
                            selectedPO
                            .purchaseOrderItems || [];

                        purchaseSummary.innerHTML =
                            '';

                        if (!items.length) {
                            purchaseSummary.innerHTML = `
                                <tr>
                                    <td
                                        colspan="4"
                                        class="py-8 text-center text-red-500"
                                    >
                                        No purchased items were found.
                                    </td>
                                </tr>
                            `;

                            return;
                        }

                        items.forEach(item => {
                            const name =
                                item.item
                                ?.item_name ??
                                '';

                            const label =
                                item.item
                                ?.label ??
                                '';

                            purchaseSummary
                                .insertAdjacentHTML(
                                    'beforeend',
                                    `
                                        <tr>
                                            <td class="border px-4 py-2">
                                                ${name}
                                            </td>

                                            <td class="border px-4 py-2 text-center">
                                                ${label || '-'}
                                            </td>

                                            <td class="border px-4 py-2 text-center">
                                                ${Number(item.quantity || 0).toLocaleString()}
                                            </td>

                                            <td class="remainingQty border px-4 py-2 text-center font-semibold text-green-700">
                                                0
                                            </td>
                                        </tr>
                                    `
                                );
                        });

                        try {
                            await loadRemaining(
                                id
                            );
                        } catch (error) {
                            alert(
                                error.message ||
                                'Unable to load remaining quantities.'
                            );
                        }
                    }
                );

            document
                .getElementById(
                    'openModal'
                )
                .addEventListener(
                    'click',
                    function() {
                        if (!selectedPO) {
                            alert(
                                'Please select a Purchase Order first.'
                            );

                            return;
                        }

                        editingIndex = null;

                        resetAssignmentInputs();
                        refreshProvinceOptions();

                        provinceSelect.disabled =
                            false;

                        saveAssignButton
                            .textContent =
                            'Add Province';

                        openModal();
                    }
                );

            document
                .getElementById(
                    'closeModal'
                )
                .addEventListener(
                    'click',
                    closeModal
                );

            document
                .getElementById(
                    'cancelAssign'
                )
                .addEventListener(
                    'click',
                    closeModal
                );

            fields.forEach(field => {
                const input =
                    document.getElementById(
                        field
                    );

                const warning =
                    document.createElement(
                        'p'
                    );

                warning.dataset
                    .quantityWarning =
                    'true';

                warning.className =
                    'mt-1 hidden text-xs font-semibold text-red-600';

                input.parentElement
                    .appendChild(
                        warning
                    );

                input.setAttribute(
                    'min',
                    '0'
                );

                input.setAttribute(
                    'step',
                    '1'
                );

                input.addEventListener(
                    'input',
                    function() {
                        let value =
                            Number(
                                this.value ||
                                0
                            );

                        if (
                            !Number.isFinite(
                                value
                            ) ||
                            value < 0
                        ) {
                            value = 0;
                        }

                        value =
                            Math.floor(value);

                        this.value =
                            value;

                        validateAssignmentForm();
                    }
                );
            });

            provinceSelect
                .addEventListener(
                    'change',
                    function() {
                        updateAutomaticDeliveryAddress();
                        validateAssignmentForm();
                    }
                );

            scheduledDeliveryDateInput
                .addEventListener(
                    'change',
                    validateAssignmentForm
                );

            saveAssignButton
                .addEventListener(
                    'click',
                    function() {
                        if (
                            !validateAssignmentForm()
                        ) {
                            alert(
                                'Select a province, provide its delivery date, and enter valid PPE quantities.'
                            );

                            return;
                        }

                        const provinceId =
                            Number(
                                provinceSelect
                                .value
                            );

                        const values =
                            readModalValues();

                        const provinceOption = selectedProvinceOption();

                        const newDistribution = {
                            province_id: provinceId,

                            scheduled_delivery_date: scheduledDeliveryDateInput.value,

                            place_of_delivery: provinceOption?.dataset.address || '',

                            long_sleeve_medium: values.lsm,

                            long_sleeve_large: values.lsl,

                            bucket_hat: values.bucket,

                            rubber_boots_us9: values.us9,

                            rubber_boots_us10: values.us10,

                            hand_gloves: values.gloves,

                            mask: values.mask,
                        };

                        if (
                            editingIndex !== null
                        ) {
                            distributions[
                                editingIndex
                            ] = newDistribution;
                        } else {
                            distributions.push(
                                newDistribution
                            );
                        }

                        renderDistributionSummary();
                        closeModal();
                    }
                );

            distributionSummary
                .addEventListener(
                    'click',
                    function(event) {
                        const editButton =
                            event.target.closest(
                                '[data-edit-index]'
                            );

                        const removeButton =
                            event.target.closest(
                                '[data-remove-index]'
                            );

                        if (editButton) {
                            const index =
                                Number(
                                    editButton
                                    .dataset
                                    .editIndex
                                );

                            const distribution =
                                distributions[index];

                            if (!distribution) {
                                return;
                            }

                            editingIndex =
                                index;

                            refreshProvinceOptions();

                            provinceSelect.value =
                                String(
                                    distribution
                                    .province_id
                                );

                            /*
                             * Keep province fixed while editing.
                             */
                            provinceSelect.disabled =
                                true;

                            scheduledDeliveryDateInput.value =
                                distribution.scheduled_delivery_date || '';

                            placeOfDeliveryInput.value =
                                distribution.place_of_delivery ||
                                selectedProvinceOption()?.dataset.address || '';

                            fields.forEach(
                                field => {
                                    document
                                        .getElementById(
                                            field
                                        )
                                        .value =
                                        requestValue(
                                            distribution,
                                            field
                                        );
                                }
                            );

                            saveAssignButton
                                .textContent =
                                'Update Province';

                            validateAssignmentForm();
                            openModal();

                            return;
                        }

                        if (removeButton) {
                            const index =
                                Number(
                                    removeButton
                                    .dataset
                                    .removeIndex
                                );

                            const distribution =
                                distributions[index];

                            if (!distribution) {
                                return;
                            }

                            const option =
                                Array.from(
                                    provinceSelect.options
                                ).find(
                                    provinceOption =>
                                    Number(
                                        provinceOption
                                        .value
                                    ) ===
                                    Number(
                                        distribution
                                        .province_id
                                    )
                                );

                            const provinceName =
                                option?.dataset.name ||
                                option?.textContent
                                ?.trim() ||
                                'this province';

                            const confirmed =
                                confirm(
                                    `Remove the PPE allocation for ${provinceName}?`
                                );

                            if (!confirmed) {
                                return;
                            }

                            distributions.splice(
                                index,
                                1
                            );

                            renderDistributionSummary();
                        }
                    }
                );

            form.addEventListener(
                'submit',
                async function(
                    event
                ) {
                    event.preventDefault();

                    if (!selectedPO) {
                        alert(
                            'Please select a Purchase Order.'
                        );

                        return;
                    }

                    if (
                        distributions.length ===
                        0
                    ) {
                        alert(
                            'Please assign PPE to at least one province.'
                        );

                        return;
                    }

                    if (
                        !combinedAllocationIsValid()
                    ) {
                        recalculateRemainingStock();

                        alert(
                            'One or more combined PPE allocations exceed the Purchase Order remaining quantity.'
                        );

                        return;
                    }

                    const missingDeliveryDate = distributions.find(
                        distribution => !distribution.scheduled_delivery_date
                    );

                    if (missingDeliveryDate) {
                        alert('Every provincial allocation must have its own delivery date.');
                        return;
                    }

                    distributionsInput.value =
                        JSON.stringify(
                            distributions
                        );

                    const originalText =
                        submitButton
                        .textContent
                        .trim();

                    submitButton.disabled =
                        true;

                    submitButton.textContent =
                        'Saving Distribution...';

                    try {
                        const response =
                            await fetch(
                                this.action, {
                                    method: 'POST',

                                    body: new FormData(
                                        this
                                    ),

                                    headers: {
                                        Accept: 'application/json',

                                        'X-Requested-With': 'XMLHttpRequest',
                                    },
                                }
                            );

                        const responseText = await response.text();

                        let data = null;

                        try {
                            data = responseText ?
                                JSON.parse(responseText) :
                                {};
                        } catch (error) {
                            console.error('Laravel response:', responseText);

                            throw new Error(
                                responseText.includes('<!DOCTYPE html>') ||
                                responseText.includes('<html') ?
                                'Laravel returned an HTML error page. Open the browser console to see the response.' :
                                responseText || 'Laravel returned an empty or invalid response.'
                            );
                        }
                        if (!response.ok) {
                            throw new Error(
                                buildValidationMessage(
                                    data
                                )
                            );
                        }

                        alert(
                            data.message ||
                            'Distribution saved successfully.'
                        );

                        window.location.href =
                            data.redirect_url ||
                            distributionIndexUrl;
                    } catch (error) {
                        alert(
                            error.message ||
                            'An unexpected error occurred.'
                        );

                        submitButton.disabled =
                            false;

                        submitButton.textContent =
                            originalText;
                    }
                }
            );

            resetDistributionSummary();
            updateSubmitState();
        }
    );
</script>
</x-po_dashboard_layout>