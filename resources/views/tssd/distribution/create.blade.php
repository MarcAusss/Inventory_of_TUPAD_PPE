<x-po_dashboard_layout>

    <form id="distributionForm" action="{{ route('tssd.distributions.store') }}" method="POST">

        @csrf

        <input type="hidden" id="distributionsInput" name="distributions">

        <div class="space-y-8">

            <!-- ================================================= -->
            <!-- HEADER -->
            <!-- ================================================= -->

            <div class="bg-white rounded-2xl shadow border">

                <div class="bg-red-900 px-8 py-6">

                    <h2 class="text-3xl font-bold text-white">

                        Create TSSD Distribution

                    </h2>

                    <p class="text-red-100 mt-1">

                        Select a Purchase Order then distribute PPE to every province.

                    </p>

                </div>

                <div class="p-8">

                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                        <!-- Purchase Order -->

                        <div>

                            <label class="font-semibold">

                                Purchase Order Number

                            </label>

                            <select id="purchase_order" name="purchase_order_id"
                                class="w-full rounded-xl border-gray-300">

                                <option value="">

                                    Select Purchase Order Number

                                </option>

                                @foreach($purchaseOrders as $po)

                                    <option value="{{ $po->id }}">

                                        {{ $po->po_number }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <!-- PO Date -->

                        <div>

                            <label class="font-semibold">

                                PO Date

                            </label>

                            <input id="po_date" readonly class="w-full rounded-xl bg-gray-100">

                        </div>

                        <!-- Supplier -->

                        <div>

                            <label class="font-semibold">

                                Supplier

                            </label>

                            <input id="supplier" readonly class="w-full rounded-xl bg-gray-100">

                        </div>

                        <!-- NEFA -->

                        <div>

                            <label class="font-semibold">

                                NEFA Number

                            </label>

                            <input id="nefa" readonly class="w-full rounded-xl bg-gray-100">

                        </div>

                    </div>

                </div>

            </div>

            <!-- ================================================= -->
            <!-- PURCHASED PPE SUMMARY -->
            <!-- ================================================= -->

            <div class="bg-white rounded-2xl shadow border">

                <div class="bg-red-900 px-8 py-6 flex justify-between items-center">

                    <h3 class="text-2xl font-semibold text-white">

                        Purchased PPE Summary

                    </h3>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-gray-100">

                            <tr>

                                <th class="px-6 py-4 text-left">

                                    PPE Item

                                </th>

                                <th class="px-6 py-4">

                                    Size

                                </th>

                                <th class="px-6 py-4">

                                    Purchased Qty

                                </th>

                                <th class="px-6 py-4">

                                    Remaining Qty

                                </th>

                            </tr>

                        </thead>

                        <tbody id="purchaseSummary">

                            <tr>

                                <td colspan="4" class="text-center py-8 text-gray-500">

                                    Select a Purchase Order.

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

            <!-- ================================================= -->
            <!-- PROVINCE DISTRIBUTION SUMMARY -->
            <!-- ================================================= -->

            <div class="bg-white rounded-2xl shadow border">

                <div class="bg-red-900 px-8 py-6 flex justify-between items-center">

                    <h3 class="text-2xl font-semibold text-white">

                        Province Distribution Summary

                    </h3>

                    <button type="button" id="openModal"
                        class="bg-white text-red-900 font-semibold px-5 py-2 rounded-lg">

                        + Assign PPE to Province

                    </button>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-gray-100">

                            <tr>

                                <th class="px-4 py-3">

                                    Province

                                </th>

                                <th>

                                    LS-M

                                </th>

                                <th>

                                    LS-L

                                </th>

                                <th>

                                    Bucket Hat

                                </th>

                                <th>

                                    US9

                                </th>

                                <th>

                                    US10

                                </th>

                                <th>

                                    Gloves

                                </th>

                                <th>

                                    Mask

                                </th>

                                <th class="px-4 py-3">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody id="distributionSummary">

                            @forelse($provinceDistributions as $provinceId => $rows)

                                @php
                                    $province = $rows->first()->province;

                                    $lsm = $rows->filter(
                                        fn($r) =>
                                        $r->item &&
                                        $r->item->item_name == 'Long Sleeve' &&
                                        $r->item->label == 'Medium'
                                    )->sum('quantity');

                                    $lsl = $rows->filter(
                                        fn($r) =>
                                        $r->item &&
                                        $r->item->item_name == 'Long Sleeve' &&
                                        $r->item->label == 'Large'
                                    )->sum('quantity');

                                    $bucket = $rows->filter(
                                        fn($r) =>
                                        $r->item &&
                                        $r->item->item_name == 'Bucket Hat'
                                    )->sum('quantity');

                                    $us9 = $rows->filter(
                                        fn($r) =>
                                        $r->item &&
                                        $r->item->item_name == 'Rubber Boots' &&
                                        $r->item->label == 'US9'
                                    )->sum('quantity');

                                    $us10 = $rows->filter(
                                        fn($r) =>
                                        $r->item &&
                                        $r->item->item_name == 'Rubber Boots' &&
                                        $r->item->label == 'US10'
                                    )->sum('quantity');

                                    $gloves = $rows->filter(
                                        fn($r) =>
                                        $r->item &&
                                        $r->item->item_name == 'Hand Gloves'
                                    )->sum('quantity');

                                    $mask = $rows->filter(
                                        fn($r) =>
                                        $r->item &&
                                        $r->item->item_name == 'Mask'
                                    )->sum('quantity');
                                @endphp

                                <tr class="border-t hover:bg-gray-50">

                                    <td class="px-4 py-3 font-semibold">
                                        {{ $province->name }}
                                    </td>

                                    <td class="text-center">{{ $lsm }}</td>
                                    <td class="text-center">{{ $lsl }}</td>
                                    <td class="text-center">{{ $bucket }}</td>
                                    <td class="text-center">{{ $us9 }}</td>
                                    <td class="text-center">{{ $us10 }}</td>
                                    <td class="text-center">{{ $gloves }}</td>
                                    <td class="text-center">{{ $mask }}</td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="9" class="text-center py-8 text-gray-500">
                                        No province assigned yet.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

            <!-- Delivery -->

            <div class="bg-white rounded-2xl shadow border">

                <div class="bg-red-900 px-8 py-6">

                    <h3 class="text-2xl text-white">

                        Delivery Information

                    </h3>

                </div>

                <div class="p-8 grid grid-cols-2 gap-6">

                    <div>

                        <label>

                            Delivery Date

                        </label>

                        <input type="date" id="delivery_date" name="delivery_date"
                            class="w-full rounded-xl border-gray-300">

                    </div>

                    <div>

                        <label>

                            Remarks

                        </label>

                        <textarea name="remarks" rows="3" class="w-full rounded-xl border-gray-300"></textarea>

                    </div>

                </div>

            </div>

            <div class="flex justify-end">

                <button type="submit" id="submitDistributionButton"
                    class="bg-red-900 hover:bg-red-800 text-white px-8 py-3 rounded-xl disabled:cursor-not-allowed disabled:opacity-60">
                    Save Distribution
                </button>

            </div>

        </div>

    </form>

</x-po_dashboard_layout>

<!-- Modal -->
<div id="assignModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

    <div class="bg-white rounded-2xl w-11/12 max-w-5xl shadow-xl">

        <div class="flex justify-between items-center border-b px-6 py-4">

            <h2 class="text-xl font-bold">
                Assign PPE to Province
            </h2>

            <button type="button" id="closeModal" class="text-2xl">
                &times;
            </button>

        </div>

        <div class="p-6">

            <div class="mb-6">

                <label class="font-semibold">
                    Province
                </label>

                <select id="provinceSelect" class="w-full rounded-xl border-gray-300">

                    <option value="">
                        Select Province
                    </option>

                    @foreach($provinces as $province)

                        <option value="{{ $province->id }}" data-name="{{ $province->name }}">

                            {{ $province->name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <table class="min-w-full border">

                <thead class="bg-gray-100">

                    <tr>

                        <th>PPE</th>
                        <th>Quantity</th>

                    </tr>

                </thead>

                <tbody>

                    <tr>
                        <td>Long Sleeve Medium</td>
                        <td>
                            <input type="number" id="lsm" value="0" class="w-24 rounded border-gray-300">
                        </td>
                    </tr>

                    <tr>
                        <td>Long Sleeve Large</td>
                        <td>
                            <input type="number" id="lsl" value="0" class="w-24 rounded border-gray-300">
                        </td>
                    </tr>

                    <tr>
                        <td>Bucket Hat</td>
                        <td>
                            <input type="number" id="bucket" value="0" class="w-24 rounded border-gray-300">
                        </td>
                    </tr>

                    <tr>
                        <td>Rubber Boots US9</td>
                        <td>
                            <input type="number" id="us9" value="0" class="w-24 rounded border-gray-300">
                        </td>
                    </tr>

                    <tr>
                        <td>Rubber Boots US10</td>
                        <td>
                            <input type="number" id="us10" value="0" class="w-24 rounded border-gray-300">
                        </td>
                    </tr>

                    <tr>
                        <td>Hand Gloves</td>
                        <td>
                            <input type="number" id="gloves" value="0" class="w-24 rounded border-gray-300">
                        </td>
                    </tr>

                    <tr>
                        <td>Mask</td>
                        <td>
                            <input type="number" id="mask" value="0" class="w-24 rounded border-gray-300">
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

        <div class="border-t px-6 py-4 flex justify-end gap-3">

            <button type="button" id="cancelAssign" class="px-5 py-2 bg-gray-300 rounded-xl">

                Cancel

            </button>

            <button type="button" id="saveAssign" class="px-6 py-2 bg-red-900 text-white rounded-xl">

                Add Province

            </button>

        </div>

    </div>

</div>

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {
            const purchaseOrders =
                @json($purchaseOrders);

            const distributionIndexUrl =
                @json(
                    route(
                        'tssd.distributions.index'
                    )
                );

            const remainingUrlTemplate =
                @json(
                    route(
                        'tssd.purchase-orders.remaining',
                        [
                            'poId' => '__PO_ID__',
                        ]
                    )
                );

            const fieldDefinitions = {
                lsm: {
                    requestField:
                        'long_sleeve_medium',

                    label:
                        'Long Sleeve Medium',
                },

                lsl: {
                    requestField:
                        'long_sleeve_large',

                    label:
                        'Long Sleeve Large',
                },

                bucket: {
                    requestField:
                        'bucket_hat',

                    label:
                        'Bucket Hat',
                },

                us9: {
                    requestField:
                        'rubber_boots_us9',

                    label:
                        'Rubber Boots US9',
                },

                us10: {
                    requestField:
                        'rubber_boots_us10',

                    label:
                        'Rubber Boots US10',
                },

                gloves: {
                    requestField:
                        'hand_gloves',

                    label:
                        'Hand Gloves',
                },

                mask: {
                    requestField:
                        'mask',

                    label:
                        'Mask',
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

            const deliveryDateInput =
                document.getElementById(
                    'delivery_date'
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

                return value === '-'
                    ? ''
                    : value;
            }

            function getKey(
                itemName,
                label
            ) {
                if (
                    itemName === 'Long Sleeve'
                    && label === 'Medium'
                ) {
                    return 'lsm';
                }

                if (
                    itemName === 'Long Sleeve'
                    && label === 'Large'
                ) {
                    return 'lsl';
                }

                if (
                    itemName === 'Bucket Hat'
                ) {
                    return 'bucket';
                }

                if (
                    itemName === 'Rubber Boots'
                    && label === 'US9'
                ) {
                    return 'us9';
                }

                if (
                    itemName === 'Rubber Boots'
                    && label === 'US10'
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
                            exceptIndex !== null
                            && index === exceptIndex
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
                            baseStock[field]
                            || 0
                        )
                        - Number(
                            allocatedTotals[field]
                            || 0
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
                )
                    - Number(
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
                        )
                        <= Number(
                            baseStock[field]
                            || 0
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

                return data?.message
                    || 'The request could not be completed.';
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
                        editingIndex !== null
                            ? Number(
                                distributions[
                                    editingIndex
                                ]?.province_id
                            )
                            : null;

                    option.disabled =
                        assignedProvinceIds
                            .has(provinceId)
                        && provinceId
                        !== editingProvinceId;
                });
            }

            function resetDistributionSummary() {
                distributionSummary.innerHTML = `
                    <tr id="emptyDistributionRow">
                        <td
                            colspan="9"
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
                                .trim()
                            || '';

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
                            remaining < 0
                                ? 'remainingQty border px-4 py-2 text-center font-semibold text-red-700'
                                : 'remainingQty border px-4 py-2 text-center font-semibold text-green-700';
                    });
            }

            function renderDistributionSummary() {
                if (
                    distributions.length
                    === 0
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
                                    )
                                    === Number(
                                        distribution
                                            .province_id
                                    )
                            );

                        const provinceName =
                            option?.dataset.name
                            || option?.textContent
                                ?.trim()
                            || 'Province';

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
                        )
                        || value < 0
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
                        )
                        || value < 0
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
                        Number.isFinite(value)
                            ? value
                            : 0;
                });

                if (total <= 0) {
                    valid = false;
                }

                if (!provinceSelect.value) {
                    valid = false;
                }

                saveAssignButton.disabled =
                    !valid;

                saveAssignButton.className =
                    valid
                        ? 'bg-red-900 hover:bg-red-800 text-white px-6 py-2 rounded-xl'
                        : 'bg-gray-400 cursor-not-allowed text-white px-6 py-2 rounded-xl';

                return valid;
            }

            function updateSubmitState() {
                const hasSelectedPO =
                    Boolean(selectedPO);

                const hasDistributions =
                    distributions.length > 0;

                const validCombinedTotals =
                    combinedAllocationIsValid();

                const hasDeliveryDate =
                    Boolean(
                        deliveryDateInput.value
                    );

                submitButton.disabled =
                    !hasSelectedPO
                    || !hasDistributions
                    || !validCombinedTotals
                    || !hasDeliveryDate;
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
                        url,
                        {
                            method: 'GET',

                            headers: {
                                Accept:
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
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
                        data.remaining?.lsm
                        || 0
                    ),

                    lsl: Number(
                        data.remaining?.lsl
                        || 0
                    ),

                    bucket: Number(
                        data.remaining
                            ?.bucket
                        || 0
                    ),

                    us9: Number(
                        data.remaining?.us9
                        || 0
                    ),

                    us10: Number(
                        data.remaining
                            ?.us10
                        || 0
                    ),

                    gloves: Number(
                        data.remaining
                            ?.gloves
                        || 0
                    ),

                    mask: Number(
                        data.remaining?.mask
                        || 0
                    ),
                };

                recalculateRemainingStock();
                validateAssignmentForm();
            }

            purchaseOrderSelect
                .addEventListener(
                    'change',
                    async function () {
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
                            selectedPO.po_date
                            ?? '';

                        document
                            .getElementById(
                                'supplier'
                            )
                            .value =
                            selectedPO
                                .supplier
                                ?.supplier_name
                            ?? '';

                        document
                            .getElementById(
                                'nefa'
                            )
                            .value =
                            selectedPO
                                .nefa_number
                            ?? '';

                        const items =
                            selectedPO.items
                            || selectedPO
                                .purchase_order_items
                            || selectedPO
                                .purchaseOrderItems
                            || [];

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
                                    ?.item_name
                                ?? '';

                            const label =
                                item.item
                                    ?.label
                                ?? '';

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
                                error.message
                                || 'Unable to load remaining quantities.'
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
                    function () {
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
                    function () {
                        let value =
                            Number(
                                this.value
                                || 0
                            );

                        if (
                            !Number.isFinite(
                                value
                            )
                            || value < 0
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
                    validateAssignmentForm
                );

            saveAssignButton
                .addEventListener(
                    'click',
                    function () {
                        if (
                            !validateAssignmentForm()
                        ) {
                            alert(
                                'Select a province and enter valid PPE quantities.'
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

                        const newDistribution = {
                            province_id:
                                provinceId,

                            long_sleeve_medium:
                                values.lsm,

                            long_sleeve_large:
                                values.lsl,

                            bucket_hat:
                                values.bucket,

                            rubber_boots_us9:
                                values.us9,

                            rubber_boots_us10:
                                values.us10,

                            hand_gloves:
                                values.gloves,

                            mask:
                                values.mask,
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
                    function (event) {
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
                                        )
                                        === Number(
                                            distribution
                                                .province_id
                                        )
                                );

                            const provinceName =
                                option?.dataset.name
                                || option?.textContent
                                    ?.trim()
                                || 'this province';

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

            deliveryDateInput
                .addEventListener(
                    'change',
                    updateSubmitState
                );

            form.addEventListener(
                'submit',
                async function (
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
                        distributions.length
                        === 0
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

                    if (
                        !deliveryDateInput
                            .value
                    ) {
                        alert(
                            'Please provide the delivery date.'
                        );

                        deliveryDateInput
                            .focus();

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
                                this.action,
                                {
                                    method:
                                        'POST',

                                    body:
                                        new FormData(
                                            this
                                        ),

                                    headers: {
                                        Accept:
                                            'application/json',

                                        'X-Requested-With':
                                            'XMLHttpRequest',
                                    },
                                }
                            );

                        let data;

                        try {
                            data =
                                await response
                                    .json();
                        } catch (error) {
                            throw new Error(
                                'The server returned an invalid response. Check the Laravel log for details.'
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
                            data.message
                            || 'Distribution saved successfully.'
                        );

                        window.location.href =
                            data.redirect_url
                            || distributionIndexUrl;
                    } catch (error) {
                        alert(
                            error.message
                            || 'An unexpected error occurred.'
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