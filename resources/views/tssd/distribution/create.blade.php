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
                                    <td colspan="8" class="text-center py-8 text-gray-500">
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
    const purchaseOrders = @json($purchaseOrders);

    const distributionIndexUrl =
        @json(route('tssd.distributions.index'));

    const remainingUrlTemplate =
        @json(route('tssd.purchase-orders.remaining', [
            'poId' => '__PO_ID__',
        ]));

    let selectedPO = null;
    let distributions = [];

    let baseStock = {
        lsm: 0,
        lsl: 0,
        bucket: 0,
        us9: 0,
        us10: 0,
        gloves: 0,
        mask: 0,
    };

    let remainingStock = { ...baseStock };

    const fields = [
        'lsm',
        'lsl',
        'bucket',
        'us9',
        'us10',
        'gloves',
        'mask',
    ];

    /*
    |--------------------------------------------------------------------------
    | PPE Key Helper
    |--------------------------------------------------------------------------
    */

    function getKey(itemName, label) {
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

        if (itemName === 'Bucket Hat') {
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

        if (itemName === 'Hand Gloves') {
            return 'gloves';
        }

        if (itemName === 'Mask') {
            return 'mask';
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Utility Helpers
    |--------------------------------------------------------------------------
    */

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
        const value = String(label ?? '').trim();

        return value === '-' ? '' : value;
    }

    function resetAssignmentInputs() {
        fields.forEach(field => {
            const input = document.getElementById(field);

            input.value = 0;
            input.classList.remove('border-red-500');

            const warning = input.parentNode.querySelector(
                '[data-quantity-warning]'
            );

            if (warning) {
                warning.textContent = '';
                warning.classList.add('hidden');
            }
        });

        validateAssignmentForm();
    }

    function enableAllProvinceOptions() {
        const provinceSelect =
            document.getElementById('provinceSelect');

        Array.from(provinceSelect.options).forEach(option => {
            option.disabled = false;
        });

        provinceSelect.selectedIndex = 0;
    }

    function resetDistributionSummary() {
        document.getElementById('distributionSummary').innerHTML = `
            <tr id="emptyDistributionRow">
                <td
                    colspan="8"
                    class="text-center py-8 text-gray-500"
                >
                    No province assigned yet.
                </td>
            </tr>
        `;
    }

    function resetDistributionState() {
        distributions = [];
        remainingStock = emptyStock();
        baseStock = emptyStock();

        enableAllProvinceOptions();
        resetAssignmentInputs();
        resetDistributionSummary();
    }

    function buildValidationMessage(data) {
        if (data?.errors) {
            return Object.values(data.errors)
                .flat()
                .join('\n');
        }

        return data?.message ||
            'The request could not be completed.';
    }

    /*
    |--------------------------------------------------------------------------
    | Remaining Quantity
    |--------------------------------------------------------------------------
    */

    async function loadRemaining(poId) {
        const url = remainingUrlTemplate.replace(
            '__PO_ID__',
            poId
        );

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        let data;

        try {
            data = await response.json();
        } catch (error) {
            throw new Error(
                'The server returned an invalid response while loading remaining quantities.'
            );
        }

        if (!response.ok) {
            throw new Error(buildValidationMessage(data));
        }

        remainingStock = {
            lsm: Number(data.remaining?.lsm || 0),
            lsl: Number(data.remaining?.lsl || 0),
            bucket: Number(data.remaining?.bucket || 0),
            us9: Number(data.remaining?.us9 || 0),
            us10: Number(data.remaining?.us10 || 0),
            gloves: Number(data.remaining?.gloves || 0),
            mask: Number(data.remaining?.mask || 0),
        };

        baseStock = { ...remainingStock };

        updateRemainingUI();
        validateAssignmentForm();
    }

    function updateRemainingUI() {
        document
            .querySelectorAll('.remainingQty')
            .forEach(cell => {
                const row = cell.closest('tr');

                if (!row) {
                    return;
                }

                const name =
                    row.children[0]?.innerText.trim() || '';

                const label = normaliseLabel(
                    row.children[1]?.innerText
                );

                const key = getKey(name, label);

                if (key) {
                    cell.innerText = remainingStock[key];
                }
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Purchase Order Selection
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('purchase_order')
        .addEventListener('change', async function () {
            const id = Number(this.value);

            selectedPO = purchaseOrders.find(
                purchaseOrder =>
                    Number(purchaseOrder.id) === id
            );

            resetDistributionState();

            const purchaseSummary =
                document.getElementById('purchaseSummary');

            if (!selectedPO) {
                document.getElementById('po_date').value = '';
                document.getElementById('supplier').value = '';
                document.getElementById('nefa').value = '';

                purchaseSummary.innerHTML = `
                    <tr>
                        <td
                            colspan="4"
                            class="text-center py-8 text-gray-500"
                        >
                            Select a Purchase Order.
                        </td>
                    </tr>
                `;

                return;
            }

            document.getElementById('po_date').value =
                selectedPO.po_date ?? '';

            document.getElementById('supplier').value =
                selectedPO.supplier?.supplier_name ?? '';

            document.getElementById('nefa').value =
                selectedPO.nefa_number ?? '';

            const items =
                selectedPO.items ||
                selectedPO.purchase_order_items ||
                selectedPO.purchaseOrderItems ||
                [];

            purchaseSummary.innerHTML = '';

            if (!items.length) {
                purchaseSummary.innerHTML = `
                    <tr>
                        <td
                            colspan="4"
                            class="text-center py-8 text-red-500"
                        >
                            No purchased items were found.
                        </td>
                    </tr>
                `;

                return;
            }

            items.forEach(item => {
                const name =
                    item.item?.item_name ?? '';

                const label =
                    item.item?.label ?? '';

                const key = getKey(name, label);

                purchaseSummary.insertAdjacentHTML(
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
                                ${Number(item.quantity || 0)}
                            </td>

                            <td
                                class="border px-4 py-2 text-center remainingQty"
                            >
                                0
                            </td>
                        </tr>
                    `
                );

                if (key) {
                    baseStock[key] =
                        Number(item.quantity || 0);
                }
            });

            try {
                await loadRemaining(id);
            } catch (error) {
                alert(
                    error.message ||
                    'Unable to load remaining quantities.'
                );
            }
        });

    /*
    |--------------------------------------------------------------------------
    | Assignment Modal
    |--------------------------------------------------------------------------
    */

    const modal = document.getElementById('assignModal');

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document
        .getElementById('openModal')
        .addEventListener('click', function () {
            if (!selectedPO) {
                alert(
                    'Please select a Purchase Order first.'
                );

                return;
            }

            resetAssignmentInputs();
            openModal();
        });

    document
        .getElementById('closeModal')
        .addEventListener('click', closeModal);

    document
        .getElementById('cancelAssign')
        .addEventListener('click', closeModal);

    /*
    |--------------------------------------------------------------------------
    | Assignment Validation
    |--------------------------------------------------------------------------
    */

    function validateAssignmentForm() {
        let valid = true;
        let total = 0;

        fields.forEach(field => {
            const input =
                document.getElementById(field);

            const value = Number(input.value || 0);
            const remaining =
                Number(remainingStock[field] || 0);

            if (
                !Number.isInteger(value) ||
                value < 0 ||
                value > remaining
            ) {
                valid = false;
            }

            total += value;
        });

        if (total <= 0) {
            valid = false;
        }

        const provinceSelect =
            document.getElementById('provinceSelect');

        if (!provinceSelect.value) {
            valid = false;
        }

        const button =
            document.getElementById('saveAssign');

        button.disabled = !valid;

        button.className = valid
            ? 'bg-red-900 hover:bg-red-800 text-white px-6 py-2 rounded-xl'
            : 'bg-gray-400 cursor-not-allowed text-white px-6 py-2 rounded-xl';
    }

    fields.forEach(field => {
        const input =
            document.getElementById(field);

        const warning =
            document.createElement('p');

        warning.dataset.quantityWarning = 'true';
        warning.className =
            'text-xs text-red-600 mt-1 hidden';

        input.parentNode.appendChild(warning);

        input.addEventListener('input', function () {
            let value = Number(this.value || 0);

            if (!Number.isFinite(value) || value < 0) {
                value = 0;
                this.value = 0;
            }

            value = Math.floor(value);
            this.value = value;

            const remaining =
                Number(remainingStock[field] || 0);

            if (value > remaining) {
                warning.textContent =
                    `Only ${remaining} remaining.`;

                warning.classList.remove('hidden');
                this.classList.add('border-red-500');
            } else {
                warning.textContent = '';
                warning.classList.add('hidden');
                this.classList.remove('border-red-500');
            }

            validateAssignmentForm();
        });
    });

    document
        .getElementById('provinceSelect')
        .addEventListener(
            'change',
            validateAssignmentForm
        );

    /*
    |--------------------------------------------------------------------------
    | Add Province Allocation
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('saveAssign')
        .addEventListener('click', function () {
            if (this.disabled) {
                alert(
                    'Please select a province and enter valid quantities.'
                );

                return;
            }

            const provinceSelect =
                document.getElementById('provinceSelect');

            const provinceId =
                Number(provinceSelect.value);

            if (!provinceId) {
                alert('Please select a province.');

                return;
            }

            if (
                distributions.some(
                    distribution =>
                        Number(distribution.province_id) ===
                        provinceId
                )
            ) {
                alert(
                    'This province has already been assigned.'
                );

                return;
            }

            const values = {};

            fields.forEach(field => {
                values[field] = Number(
                    document.getElementById(field).value || 0
                );
            });

            const allocationTotal = fields.reduce(
                (total, field) =>
                    total + values[field],
                0
            );

            if (allocationTotal <= 0) {
                alert(
                    'Enter at least one PPE quantity greater than zero.'
                );

                return;
            }

            for (const field of fields) {
                if (
                    values[field] >
                    Number(remainingStock[field] || 0)
                ) {
                    alert(
                        `The ${field} quantity exceeds the remaining stock.`
                    );

                    return;
                }
            }

            distributions.push({
                province_id: provinceId,

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
            });

            remainingStock.lsm -= values.lsm;
            remainingStock.lsl -= values.lsl;
            remainingStock.bucket -= values.bucket;
            remainingStock.us9 -= values.us9;
            remainingStock.us10 -= values.us10;
            remainingStock.gloves -= values.gloves;
            remainingStock.mask -= values.mask;

            updateRemainingUI();

            const selectedOption =
                provinceSelect.options[
                provinceSelect.selectedIndex
                ];

            const provinceName =
                selectedOption.dataset.name ||
                selectedOption.textContent.trim();

            const summary =
                document.getElementById(
                    'distributionSummary'
                );

            const emptyRow =
                document.getElementById(
                    'emptyDistributionRow'
                );

            if (emptyRow) {
                emptyRow.remove();
            }

            const rowIndex =
                distributions.length - 1;

            summary.insertAdjacentHTML(
                'beforeend',
                `
                    <tr
                        class="border-t hover:bg-gray-50"
                        data-distribution-row="${rowIndex}"
                    >
                        <td class="px-4 py-3 font-semibold">
                            ${provinceName}
                        </td>

                        <td class="text-center">
                            ${values.lsm}
                        </td>

                        <td class="text-center">
                            ${values.lsl}
                        </td>

                        <td class="text-center">
                            ${values.bucket}
                        </td>

                        <td class="text-center">
                            ${values.us9}
                        </td>

                        <td class="text-center">
                            ${values.us10}
                        </td>

                        <td class="text-center">
                            ${values.gloves}
                        </td>

                        <td class="text-center">
                            ${values.mask}
                        </td>
                    </tr>
                `
            );

            selectedOption.disabled = true;
            provinceSelect.selectedIndex = 0;

            resetAssignmentInputs();
            closeModal();
        });

    /*
    |--------------------------------------------------------------------------
    | Submit Distribution
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('distributionForm')
        .addEventListener(
            'submit',
            async function (event) {
                event.preventDefault();

                if (!selectedPO) {
                    alert(
                        'Please select a Purchase Order.'
                    );

                    return;
                }

                if (distributions.length === 0) {
                    alert(
                        'Please assign PPE to at least one province.'
                    );

                    return;
                }

                const deliveryDate =
                    document.getElementById(
                        'delivery_date'
                    ).value;

                if (!deliveryDate) {
                    alert(
                        'Please provide the delivery date.'
                    );

                    return;
                }

                document.getElementById(
                    'distributionsInput'
                ).value =
                    JSON.stringify(distributions);

                const submitButton =
                    document.getElementById('submitDistributionButton');

                if (!submitButton) {
                    alert('The Save Distribution button could not be found.');
                    return;
                }

                const originalButtonText =
                    submitButton.textContent.trim();

                submitButton.disabled = true;
                submitButton.textContent =
                    'Saving Distribution...';

                try {
                    const response = await fetch(
                        this.action,
                        {
                            method: 'POST',
                            body: new FormData(this),
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                        }
                    );

                    let data;

                    try {
                        data = await response.json();
                    } catch (error) {
                        throw new Error(
                            'The server returned an invalid response. Check the Laravel log for details.'
                        );
                    }

                    if (!response.ok) {
                        throw new Error(
                            buildValidationMessage(data)
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

                    submitButton.disabled = false;
                    submitButton.textContent =
                        originalButtonText;
                }
            }
        );
</script>