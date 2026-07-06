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

                <button class="bg-red-900 hover:bg-red-800 text-white px-8 py-3 rounded-xl">

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

    let selectedPO = null;
    let distributions = [];

    // base stock from purchase order
    let baseStock = {
        lsm: 0,
        lsl: 0,
        bucket: 0,
        us9: 0,
        us10: 0,
        gloves: 0,
        mask: 0,
    };

    // live remaining stock (UPDATED continuously)
    let remainingStock = { ...baseStock };

    // -------------------------------
    // GET KEY HELPER
    // -------------------------------
    function getKey(itemName, label) {
        if (itemName === "Long Sleeve" && label === "Medium") return "lsm";
        if (itemName === "Long Sleeve" && label === "Large") return "lsl";
        if (itemName === "Bucket Hat") return "bucket";
        if (itemName === "Rubber Boots" && label === "US9") return "us9";
        if (itemName === "Rubber Boots" && label === "US10") return "us10";
        if (itemName === "Hand Gloves") return "gloves";
        if (itemName === "Mask") return "mask";
        return null;
    }

    // -------------------------------
    // LOAD DB REMAINING (IMPORTANT FIX)
    // -------------------------------
    function loadRemaining(poId) {
        fetch(`/tssd/purchase-orders/${poId}/remaining`)
            .then(res => res.json())
            .then(data => {

                remainingStock = {
                    lsm: Number(data.remaining.lsm || 0),
                    lsl: Number(data.remaining.lsl || 0),
                    bucket: Number(data.remaining.bucket || 0),
                    us9: Number(data.remaining.us9 || 0),
                    us10: Number(data.remaining.us10 || 0),
                    gloves: Number(data.remaining.gloves || 0),
                    mask: Number(data.remaining.mask || 0),
                };

                baseStock = { ...remainingStock };

                console.log("DB Remaining Loaded:", remainingStock);

                updateRemainingUI();
            });
    }

    // -------------------------------
    // UPDATE UI TABLE
    // -------------------------------
    function updateRemainingUI() {

        document.querySelectorAll(".remainingQty").forEach(cell => {

            const row = cell.closest("tr");

            const name = row.children[0].innerText.trim();
            const label = row.children[1].innerText.trim();

            const key = getKey(name, label);

            if (key) {
                cell.innerText = remainingStock[key];
            }
        });
    }

    // -------------------------------
    // PURCHASE ORDER CHANGE
    // -------------------------------
    document.getElementById("purchase_order").addEventListener("change", function () {

        const id = Number(this.value);

        selectedPO = purchaseOrders.find(po => Number(po.id) === id);

        if (!selectedPO) return;

        document.getElementById("po_date").value = selectedPO.po_date ?? "";
        document.getElementById("supplier").value = selectedPO.supplier?.supplier_name ?? "";
        document.getElementById("nefa").value = selectedPO.nefa_number ?? "";

        // RESET DISTRIBUTIONS
        distributions = [];

        // LOAD DB REMAINING
        loadRemaining(id);

        const tbody = document.getElementById("purchaseSummary");
        tbody.innerHTML = "";

        let items =
            selectedPO.items ||
            selectedPO.purchase_order_items ||
            selectedPO.purchaseOrderItems ||
            [];

        if (!items.length) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-8 text-red-500">No purchased items found.</td></tr>`;
            return;
        }

        // BASE STOCK RESET
        baseStock = {
            lsm: 0,
            lsl: 0,
            bucket: 0,
            us9: 0,
            us10: 0,
            gloves: 0,
            mask: 0,
        };

        items.forEach(item => {

            const name = item.item?.item_name ?? "";
            const label = item.item?.label ?? "";

            const key = getKey(name, label);

            if (key) {
                baseStock[key] = Number(item.quantity);
            }

            tbody.innerHTML += `
        <tr>
            <td class="border px-4 py-2">${name}</td>
            <td class="border px-4 py-2 text-center">${label || "-"}</td>
            <td class="border px-4 py-2 text-center">${item.quantity}</td>
            <td class="border px-4 py-2 text-center remainingQty">${key ? remainingStock[key] : item.quantity}</td>
        </tr>`;
        });
    });

    // -------------------------------
    // MODAL
    // -------------------------------
    const modal = document.getElementById("assignModal");

    document.getElementById("openModal").addEventListener("click", function () {
        if (!selectedPO) {
            alert("Please select a Purchase Order first.");
            return;
        }
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    });

    function closeModal() {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    }

    document.getElementById("closeModal").addEventListener("click", closeModal);
    document.getElementById("cancelAssign").addEventListener("click", closeModal);

    // -------------------------------
    // VALIDATION
    // -------------------------------
    const fields = ["lsm", "lsl", "bucket", "us9", "us10", "gloves", "mask"];

    function validateForm() {
        let valid = true;

        fields.forEach(id => {
            const value = Number(document.getElementById(id).value);
            if (value > remainingStock[id]) valid = false;
        });

        const btn = document.getElementById("saveAssign");
        btn.disabled = !valid;

        btn.className = valid
            ? "bg-red-900 hover:bg-red-800 text-white px-6 py-2 rounded-xl"
            : "bg-gray-400 cursor-not-allowed text-white px-6 py-2 rounded-xl";
    }

    // -------------------------------
    // INPUT VALIDATION (LIVE ALERT FIX)
    // -------------------------------
    fields.forEach(id => {

        const input = document.getElementById(id);

        const warning = document.createElement("p");
        warning.className = "text-xs text-red-600 mt-1 hidden";
        input.parentNode.appendChild(warning);

        input.addEventListener("input", function () {

            const value = Number(this.value);
            const key = id;
            const remaining = remainingStock[key];

            if (value > remaining) {
                warning.innerHTML = `⚠ Only ${remaining} remaining`;
                warning.classList.remove("hidden");
                this.classList.add("border-red-500");
            } else {
                warning.classList.add("hidden");
                this.classList.remove("border-red-500");
            }

            validateForm();
        });
    });

    // -------------------------------
    // SAVE PROVINCE
    // -------------------------------
    document.getElementById("saveAssign").addEventListener("click", function () {

        if (this.disabled) return alert("Invalid quantity");

        const province = document.getElementById("provinceSelect");

        if (!province.value) return alert("Select Province");

        const data = {};
        fields.forEach(id => {
            data[id] = Number(document.getElementById(id).value);
        });

        if (distributions.find(d => d.province_id == province.value)) {
            return alert("Province already assigned");
        }

        distributions.push({
            province_id: Number(province.value),
            long_sleeve_medium: data.lsm,
            long_sleeve_large: data.lsl,
            bucket_hat: data.bucket,
            rubber_boots_us9: data.us9,
            rubber_boots_us10: data.us10,
            hand_gloves: data.gloves,
            mask: data.mask
        });

        // 🔥 DEDUCT FROM LIVE STOCK
        remainingStock.lsm -= data.lsm;
        remainingStock.lsl -= data.lsl;
        remainingStock.bucket -= data.bucket;
        remainingStock.us9 -= data.us9;
        remainingStock.us10 -= data.us10;
        remainingStock.gloves -= data.gloves;
        remainingStock.mask -= data.mask;

        updateRemainingUI();
        validateForm();

        // UI update
        const provinceName = province.options[province.selectedIndex].dataset.name;

        const summary = document.getElementById("distributionSummary");

        if (summary.innerText.includes("No province")) {
            summary.innerHTML = "";
        }

        summary.innerHTML += `
    <tr>
        <td class="border px-4 py-2">${provinceName}</td>
        <td class="text-center">${data.lsm}</td>
        <td class="text-center">${data.lsl}</td>
        <td class="text-center">${data.bucket}</td>
        <td class="text-center">${data.us9}</td>
        <td class="text-center">${data.us10}</td>
        <td class="text-center">${data.gloves}</td>
        <td class="text-center">${data.mask}</td>
    </tr>`;

        province.options[province.selectedIndex].disabled = true;
        province.selectedIndex = 0;

        fields.forEach(id => document.getElementById(id).value = 0);

        closeModal();
    });

    // -------------------------------
    // SUBMIT
    // -------------------------------
    document.getElementById("distributionForm").addEventListener("submit", function (e) {

        e.preventDefault();

        document.getElementById("distributionsInput").value =
            JSON.stringify(distributions);

        fetch(this.action, {
            method: "POST",
            body: new FormData(this),
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                "Accept": "application/json"
            }
        })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return alert(data.message);

                alert(data.message);
                window.location.href = "/tssd/distributions";
            })
            .catch(() => alert("Error"));
    });
</script>