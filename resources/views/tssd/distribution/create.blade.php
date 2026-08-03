<x-po_dashboard_layout title="Create TSSD Distribution">
    @php
        $activeItemData = $activeItems->map(fn ($item) => [
            'id' => (int) $item->id,
            'item_name' => $item->item_name,
            'label' => $item->label,
            'unit_of_measurement' => $item->unit_of_measurement,
            'display_name' => $item->item_name . ($item->label ? ' (' . $item->label . ')' : ''),
        ])->values();

        $tableMinimumWidth = max(1100, 620 + ($activeItems->count() * 145));
    @endphp

    <form id="distributionForm" action="{{ route('tssd.distributions.store') }}" method="POST">
        @csrf
        <input type="hidden" id="distributionsInput" name="distributions">

        <div class="mx-auto max-w-[1900px] space-y-6">
            <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#2D94BE] to-[#339DCB]"></div>

                <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#143A52] ring-1 ring-[#90C4DD]">
                                TSSD Unit
                            </span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                Dynamic Provincial Allocation
                            </span>
                        </div>

                        <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                            Create TSSD Distribution
                        </h1>

                        <p class="mt-2 max-w-3xl text-sm leading-6 text-[#36566E]">
                            Active PPE items are loaded automatically from the Supply Unit's PPE Items module. Disabled or deleted items are excluded from new allocations.
                        </p>
                    </div>

                    <a href="{{ route('tssd.distributions.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-[#F3FAFD]">
                        Back to Distribution
                    </a>
                </div>
            </section>

            @if ($activeItems->isEmpty())
                <section class="rounded-2xl border border-amber-200 bg-amber-50 px-6 py-5 text-sm leading-6 text-amber-800">
                    <strong>No active PPE items are available.</strong> Ask the Supply Unit to create or enable at least one PPE item before creating a provincial allocation.
                </section>
            @endif

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">Purchase Order</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-950">Purchase Order Information</h2>
                    <p class="mt-1 text-sm text-slate-500">Select the Purchase Order that will be used as the source of this distribution.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 p-6 sm:p-7 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label for="purchase_order" class="mb-2 block text-sm font-bold text-slate-700">Purchase Order Number</label>
                        <select id="purchase_order" name="purchase_order_id" required
                            class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]">
                            <option value="">Select Purchase Order Number</option>
                            @foreach ($purchaseOrders as $po)
                                <option value="{{ $po->id }}" @selected((int) $purchaseOrderId === (int) $po->id)>
                                    {{ $po->po_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="po_date" class="mb-2 block text-sm font-bold text-slate-700">PO Date</label>
                        <input id="po_date" readonly class="w-full rounded-xl border-slate-200 bg-slate-100 text-black shadow-sm">
                    </div>

                    <div>
                        <label for="supplier" class="mb-2 block text-sm font-bold text-slate-700">Supplier</label>
                        <input id="supplier" readonly class="w-full rounded-xl border-slate-200 bg-slate-100 text-black shadow-sm">
                    </div>

                    <div>
                        <label for="nefa" class="mb-2 block text-sm font-bold text-slate-700">NEFA Number</label>
                        <input id="nefa" readonly class="w-full rounded-xl border-slate-200 bg-slate-100 text-black shadow-sm">
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">Purchased Inventory</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-950">Active PPE Availability</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Every currently active PPE item is listed. An item not included in the selected Purchase Order will have zero remaining quantity and cannot be allocated.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[820px] w-full divide-y divide-slate-200">
                        <thead>
                            <tr class="bg-[#2E628D] text-xs font-bold uppercase tracking-wide text-white">
                                <th class="px-6 py-4 text-left">PPE Item</th>
                                <th class="px-6 py-4 text-center">Size / Label</th>
                                <th class="px-6 py-4 text-center">Unit</th>
                                <th class="px-6 py-4 text-center">Purchased Qty</th>
                                <th class="px-6 py-4 text-center">Remaining Qty</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseSummary" class="divide-y divide-slate-100 text-black">
                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center text-sm text-slate-500">Select a Purchase Order.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:px-7 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">Provincial Allocations</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-950">Province Distribution Summary</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            The columns below are generated from the active PPE Items list and require no code change when Supply adds another item.
                        </p>
                    </div>

                    <button type="button" id="openModal" @disabled($activeItems->isEmpty())
                        class="inline-flex items-center justify-center rounded-xl bg-[#339DCB] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#2D94BE] disabled:cursor-not-allowed disabled:opacity-50">
                        Assign PPE to Province
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-separate border-spacing-0" style="min-width: {{ $tableMinimumWidth }}px">
                        <thead>
                            <tr class="bg-[#2E628D] text-xs font-bold uppercase tracking-wide text-white">
                                <th class="border-b border-r border-white/20 px-5 py-4 text-left">Province</th>
                                <th class="border-b border-r border-white/20 px-5 py-4 text-left">Delivery Date</th>
                                <th class="border-b border-r border-white/20 px-5 py-4 text-left">Place of Delivery</th>
                                @foreach ($activeItems as $item)
                                    <th class="border-b border-r border-white/20 px-4 py-4 text-center">
                                        {{ $item->item_name }}
                                        @if ($item->label)
                                            <span class="block text-[10px] font-semibold normal-case opacity-90">{{ $item->label }}</span>
                                        @endif
                                    </th>
                                @endforeach
                                <th class="border-b border-r border-white/20 px-4 py-4 text-center">Total PPE</th>
                                <th class="border-b border-white/20 px-5 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="distributionSummary" class="text-black">
                            <tr>
                                <td colspan="{{ 5 + $activeItems->count() }}" class="px-6 py-14 text-center text-sm text-slate-500">
                                    No province assigned yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">Notes</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-950">Distribution Remarks</h2>
                </div>
                <div class="p-6 sm:p-7">
                    <label for="remarks" class="mb-2 block text-sm font-bold text-slate-700">Remarks</label>
                    <textarea id="remarks" name="remarks" rows="3"
                        class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]">{{ old('remarks') }}</textarea>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-sky-200 bg-white shadow-sm">
                <div class="border-b border-sky-200 bg-sky-50 px-6 py-5 sm:px-7">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#247BA0]">Call-Off Number Request</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-950">Call-Off Request Letter Settings</h2>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">
                        Saving this distribution automatically generates the request letter and submits it to the Supply Unit. The unsaved preview uses the same dynamic PPE allocation data shown above.
                    </p>
                </div>

                <div class="space-y-6 p-6 sm:p-7">
                    <div class="grid gap-5 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <label for="nefa_title" class="mb-2 block text-sm font-bold text-slate-700">NEFA Project Title</label>
                            <textarea id="nefa_title" name="nefa_title" rows="3" required maxlength="1000"
                                class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]">{{ old('nefa_title', $defaultNefaTitle) }}</textarea>
                        </div>

                        <div>
                            <label for="print_total_amount" class="mb-2 block text-sm font-bold text-slate-700">Printed Total PO Amount</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 font-bold text-slate-500">₱</span>
                                <input id="print_total_amount" type="text" name="print_total_amount" inputmode="decimal"
                                    value="{{ old('print_total_amount') !== null && old('print_total_amount') !== '' ? number_format((float) old('print_total_amount'), 2, '.', ',') : '' }}"
                                    placeholder="0.00" required autocomplete="off"
                                    class="w-full rounded-xl border-slate-300 pl-9 shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]">
                            </div>
                        </div>

                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800">
                            <strong>Preview only:</strong> Opening the letter preview does not save or submit the distribution.
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800">A4 Paper Margins</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ([
                                ['print_margin_top', 'Top Margin', 9, 0, 50],
                                ['print_margin_right', 'Right Margin', 11, 0, 50],
                                ['print_margin_bottom', 'Bottom Margin', 28, 27, 70],
                                ['print_margin_left', 'Left Margin', 11, 0, 50],
                            ] as [$field, $label, $default, $minimum, $maximum])
                                <div>
                                    <label for="{{ $field }}" class="mb-2 block text-sm font-bold text-slate-700">{{ $label }}</label>
                                    <div class="relative">
                                        <input id="{{ $field }}" type="number" name="{{ $field }}"
                                            value="{{ old($field, $default) }}" min="{{ $minimum }}" max="{{ $maximum }}" step="0.5" required
                                            class="w-full rounded-xl border-slate-300 pr-12 shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]">
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-xs font-bold text-slate-500">mm</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" id="openCallOffLetterPreview"
                            class="inline-flex items-center justify-center rounded-xl border border-[#339DCB] bg-white px-6 py-3 text-sm font-bold text-[#247BA0] transition hover:bg-sky-50 disabled:cursor-not-allowed disabled:opacity-50">
                            Open Call-Off Letter Preview
                        </button>
                    </div>
                </div>
            </section>

            <section class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:justify-end">
                <a href="{{ route('tssd.distributions.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-[#F3FAFD]">
                    Cancel
                </a>
                <button type="submit" id="submitDistributionButton" disabled
                    class="inline-flex items-center justify-center rounded-xl bg-[#339DCB] px-7 py-3 text-sm font-bold text-white transition hover:bg-[#2D94BE] disabled:cursor-not-allowed disabled:opacity-60">
                    Save Distribution & Submit Letter
                </button>
            </section>
        </div>
    </form>

    <div id="assignModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/35 p-4 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-5xl overflow-hidden rounded-3xl border border-[#E4EEF5] bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-[#E4EEF5] px-6 py-5 sm:px-7">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">Provincial Allocation</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-950">Assign Active PPE Items</h2>
                </div>
                <button type="button" id="closeModal" title="Close"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#E4EEF5] text-2xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900">
                    &times;
                </button>
            </div>

            <div class="max-h-[68vh] overflow-y-auto p-6 sm:p-7">
                <div class="mb-6">
                    <label for="provinceSelect" class="mb-2 block text-sm font-bold text-slate-700">Province</label>
                    <select id="provinceSelect" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]">
                        <option value="">Select Province</option>
                        @foreach ($provinces as $province)
                            <option value="{{ $province->id }}" data-name="{{ $province->name }}"
                                data-address="{{ $province->deliveryLocation() }}">
                                {{ $province->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div>
                        <label for="scheduledDeliveryDate" class="mb-2 block text-sm font-bold text-slate-700">Delivery Date</label>
                        <input type="date" id="scheduledDeliveryDate"
                            class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]">
                    </div>
                    <div>
                        <label for="placeOfDelivery" class="mb-2 block text-sm font-bold text-slate-700">Place of Delivery</label>
                        <textarea id="placeOfDelivery" rows="2" readonly
                            class="w-full resize-none rounded-xl border-slate-200 bg-slate-100 text-black shadow-sm"
                            placeholder="Select a province to load its office address."></textarea>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-[#E4EEF5]">
                    <table class="w-full divide-y divide-slate-200">
                        <thead>
                            <tr class="bg-[#2E628D] text-xs font-bold uppercase tracking-wide text-white">
                                <th class="px-5 py-4 text-left">PPE Item</th>
                                <th class="px-5 py-4 text-center">Size / Label</th>
                                <th class="px-5 py-4 text-center">Available</th>
                                <th class="px-5 py-4 text-center">Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-black">
                            @forelse ($activeItems as $item)
                                <tr class="hover:bg-[#F3FAFD]">
                                    <td class="px-5 py-4 font-semibold">{{ $item->item_name }}</td>
                                    <td class="px-5 py-4 text-center text-sm">{{ $item->label ?: '—' }}</td>
                                    <td class="px-5 py-4 text-center text-sm font-bold" id="item_available_{{ $item->id }}">0</td>
                                    <td class="px-5 py-4 text-center">
                                        <input type="number" id="item_quantity_{{ $item->id }}" data-item-id="{{ $item->id }}"
                                            value="0" min="0" step="1"
                                            class="ppe-quantity-input w-28 rounded-xl border-slate-300 text-center text-black shadow-sm focus:border-[#339DCB] focus:ring-[#339DCB]">
                                        <p data-quantity-warning class="mt-1 hidden text-xs font-semibold text-red-600"></p>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">No active PPE items.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end sm:px-7">
                <button type="button" id="cancelAssign"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                    Cancel
                </button>
                <button type="button" id="saveAssign" disabled
                    class="rounded-xl bg-[#339DCB] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#2D94BE] disabled:cursor-not-allowed disabled:opacity-50">
                    Add Province
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const purchaseOrders = @json($purchaseOrders);
            const activeItems = @json($activeItemData);
            const initialPurchaseOrderId = @json($purchaseOrderId ?: null);
            const remainingUrlTemplate = @json(route('tssd.purchase-orders.remaining', ['poId' => '__PO_ID__']));
            const previewUrl = @json(route('tssd.distributions.call-off-letter-preview'));

            const form = document.getElementById('distributionForm');
            const purchaseOrderSelect = document.getElementById('purchase_order');
            const purchaseSummary = document.getElementById('purchaseSummary');
            const distributionSummary = document.getElementById('distributionSummary');
            const distributionsInput = document.getElementById('distributionsInput');
            const submitButton = document.getElementById('submitDistributionButton');
            const printTotalAmountInput = document.getElementById('print_total_amount');
            const previewButton = document.getElementById('openCallOffLetterPreview');
            const modal = document.getElementById('assignModal');
            const provinceSelect = document.getElementById('provinceSelect');
            const deliveryDateInput = document.getElementById('scheduledDeliveryDate');
            const placeInput = document.getElementById('placeOfDelivery');
            const saveAssignButton = document.getElementById('saveAssign');
            const quantityInputs = Array.from(document.querySelectorAll('.ppe-quantity-input'));

            const itemIds = activeItems.map(item => String(item.id));
            let selectedPO = null;
            let distributions = [];
            let baseStock = emptyStock();
            let remainingStock = emptyStock();
            let editingIndex = null;

            function emptyStock() {
                return Object.fromEntries(itemIds.map(itemId => [itemId, 0]));
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
                if (!value) return '—';
                const date = new Date(`${value}T00:00:00`);
                return Number.isNaN(date.getTime())
                    ? escapeHtml(value)
                    : date.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: '2-digit' });
            }

            function itemQuantity(distribution, itemId) {
                return Number(distribution?.items?.[String(itemId)] ?? distribution?.items?.[Number(itemId)] ?? 0);
            }

            function calculateAllocatedTotals(exceptIndex = null) {
                const totals = emptyStock();

                distributions.forEach((distribution, index) => {
                    if (exceptIndex !== null && index === exceptIndex) return;
                    itemIds.forEach(itemId => {
                        totals[itemId] += itemQuantity(distribution, itemId);
                    });
                });

                return totals;
            }

            function recalculateRemainingStock() {
                const allocated = calculateAllocatedTotals();
                remainingStock = emptyStock();

                itemIds.forEach(itemId => {
                    remainingStock[itemId] = Number(baseStock[itemId] || 0) - Number(allocated[itemId] || 0);
                });

                renderPurchaseSummary();
                updateSubmitState();
            }

            function availableForModal(itemId) {
                const allocatedWithoutCurrent = calculateAllocatedTotals(editingIndex);
                return Number(baseStock[itemId] || 0) - Number(allocatedWithoutCurrent[itemId] || 0);
            }

            function combinedAllocationIsValid() {
                const allocated = calculateAllocatedTotals();
                return itemIds.every(itemId => Number(allocated[itemId] || 0) <= Number(baseStock[itemId] || 0));
            }

            function selectedProvinceOption() {
                return provinceSelect.options[provinceSelect.selectedIndex] || null;
            }

            function clearWarning(input) {
                input.classList.remove('border-red-500', 'bg-red-50');
                const warning = input.parentElement.querySelector('[data-quantity-warning]');
                if (warning) {
                    warning.textContent = '';
                    warning.classList.add('hidden');
                }
            }

            function showWarning(input, message) {
                input.classList.add('border-red-500', 'bg-red-50');
                const warning = input.parentElement.querySelector('[data-quantity-warning]');
                if (warning) {
                    warning.textContent = message;
                    warning.classList.remove('hidden');
                }
            }

            function refreshModalAvailability() {
                quantityInputs.forEach(input => {
                    const itemId = String(input.dataset.itemId);
                    const available = Math.max(0, availableForModal(itemId));
                    const currentValue = Number(input.value || 0);
                    const availableCell = document.getElementById(`item_available_${itemId}`);

                    input.max = String(available);
                    input.disabled = available <= 0 && currentValue <= 0;
                    if (availableCell) availableCell.textContent = available.toLocaleString();
                });
            }

            function validateAssignmentForm() {
                let valid = Boolean(provinceSelect.value && deliveryDateInput.value);
                let total = 0;

                quantityInputs.forEach(input => {
                    const itemId = String(input.dataset.itemId);
                    const value = Number(input.value || 0);
                    const available = Math.max(0, availableForModal(itemId));
                    clearWarning(input);

                    if (!Number.isInteger(value) || value < 0) {
                        showWarning(input, 'Enter a non-negative whole number.');
                        valid = false;
                    } else if (value > available) {
                        showWarning(input, `Only ${available.toLocaleString()} remaining.`);
                        valid = false;
                    }

                    total += Number.isFinite(value) ? value : 0;
                });

                if (total <= 0) valid = false;
                saveAssignButton.disabled = !valid;
                return valid;
            }

            function resetAssignmentForm() {
                quantityInputs.forEach(input => {
                    input.value = 0;
                    clearWarning(input);
                });
                provinceSelect.selectedIndex = 0;
                provinceSelect.disabled = false;
                deliveryDateInput.value = '';
                placeInput.value = '';
                editingIndex = null;
                saveAssignButton.textContent = 'Add Province';
                refreshModalAvailability();
                validateAssignmentForm();
            }

            function refreshProvinceOptions() {
                const assigned = new Set(distributions.map(row => Number(row.province_id)));
                const editingProvince = editingIndex !== null ? Number(distributions[editingIndex]?.province_id) : null;

                Array.from(provinceSelect.options).forEach(option => {
                    if (!option.value) return;
                    const id = Number(option.value);
                    option.disabled = assigned.has(id) && id !== editingProvince;
                });
            }

            function openModal() {
                if (!selectedPO) {
                    alert('Please select a Purchase Order first.');
                    return;
                }
                refreshProvinceOptions();
                refreshModalAvailability();
                validateAssignmentForm();
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                resetAssignmentForm();
            }

            function renderPurchaseSummary() {
                if (!selectedPO) {
                    purchaseSummary.innerHTML = '<tr><td colspan="5" class="px-6 py-14 text-center text-sm text-slate-500">Select a Purchase Order.</td></tr>';
                    return;
                }

                const purchasedByItem = {};
                (selectedPO.items || []).forEach(row => {
                    const itemId = String(row.item_id ?? row.item?.id ?? '');
                    if (!itemId) return;
                    purchasedByItem[itemId] = Number(purchasedByItem[itemId] || 0) + Number(row.quantity || 0);
                });

                purchaseSummary.innerHTML = activeItems.map(item => {
                    const itemId = String(item.id);
                    const purchased = Number(purchasedByItem[itemId] || 0);
                    const remaining = Math.max(0, Number(remainingStock[itemId] || 0));
                    return `
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-semibold text-black">${escapeHtml(item.item_name)}</td>
                            <td class="px-6 py-4 text-center text-black">${escapeHtml(item.label || '—')}</td>
                            <td class="px-6 py-4 text-center text-black">${escapeHtml(item.unit_of_measurement || '—')}</td>
                            <td class="px-6 py-4 text-center font-semibold text-black">${purchased.toLocaleString()}</td>
                            <td class="px-6 py-4 text-center font-bold ${remaining > 0 ? 'text-black' : 'text-red-700'}">${remaining.toLocaleString()}</td>
                        </tr>`;
                }).join('');
            }

            function renderDistributionSummary() {
                if (distributions.length === 0) {
                    distributionSummary.innerHTML = `<tr><td colspan="${5 + activeItems.length}" class="px-6 py-14 text-center text-sm text-slate-500">No province assigned yet.</td></tr>`;
                    recalculateRemainingStock();
                    return;
                }

                distributionSummary.innerHTML = distributions.map((distribution, index) => {
                    const option = Array.from(provinceSelect.options).find(row => Number(row.value) === Number(distribution.province_id));
                    const itemCells = activeItems.map(item => `
                        <td class="border-b border-r border-slate-200 px-4 py-4 text-center text-black">
                            ${itemQuantity(distribution, item.id).toLocaleString()}
                        </td>`).join('');
                    const total = itemIds.reduce((sum, itemId) => sum + itemQuantity(distribution, itemId), 0);

                    return `
                        <tr class="hover:bg-slate-50">
                            <td class="border-b border-r border-slate-200 px-5 py-4 font-bold text-black">${escapeHtml(option?.dataset.name || option?.textContent?.trim() || '—')}</td>
                            <td class="border-b border-r border-slate-200 px-5 py-4 text-black">${formatDate(distribution.scheduled_delivery_date)}</td>
                            <td class="border-b border-r border-slate-200 px-5 py-4 text-black">${escapeHtml(distribution.place_of_delivery || '—')}</td>
                            ${itemCells}
                            <td class="border-b border-r border-slate-200 bg-sky-50 px-4 py-4 text-center font-black text-black">${total.toLocaleString()}</td>
                            <td class="border-b border-slate-200 px-5 py-4">
                                <div class="flex justify-center gap-2">
                                    <button type="button" data-edit-index="${index}" title="Edit allocation" aria-label="Edit allocation"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-800 transition hover:bg-amber-200">
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
                                    </button>
                                    <button type="button" data-remove-index="${index}" title="Remove allocation" aria-label="Remove allocation"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-700 transition hover:bg-red-200">
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                }).join('');

                refreshProvinceOptions();
                recalculateRemainingStock();
            }

            function normalizedAmountValue(value) {
                const normalized = String(value ?? '').replace(/,/g, '').replace(/[^0-9.]/g, '');
                const parts = normalized.split('.');
                return parts.length <= 1
                    ? parts[0]
                    : `${parts.shift()}.${parts.join('').slice(0, 2)}`;
            }

            function formatAmountValue(value) {
                const normalized = normalizedAmountValue(value);
                if (normalized === '') return '';

                const amount = Number(normalized);
                if (!Number.isFinite(amount)) return '';

                return amount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            }

            function prepareAmountForRequest() {
                printTotalAmountInput.value = normalizedAmountValue(printTotalAmountInput.value);
            }

            function restoreFormattedAmount() {
                printTotalAmountInput.value = formatAmountValue(printTotalAmountInput.value);
            }

            function updateSubmitState() {
                const enabled = Boolean(selectedPO)
                    && distributions.length > 0
                    && combinedAllocationIsValid()
                    && distributions.every(row => Boolean(row.scheduled_delivery_date));

                submitButton.disabled = !enabled;
                previewButton.disabled = !enabled;
            }

            async function loadRemaining(poId) {
                const response = await fetch(remainingUrlTemplate.replace('__PO_ID__', poId), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Unable to load remaining PPE quantities.');

                baseStock = emptyStock();
                itemIds.forEach(itemId => {
                    baseStock[itemId] = Number(data.remaining?.[itemId] || 0);
                });
                recalculateRemainingStock();
            }

            async function selectPurchaseOrder() {
                const id = Number(purchaseOrderSelect.value);
                selectedPO = purchaseOrders.find(po => Number(po.id) === id) || null;
                distributions = [];
                baseStock = emptyStock();
                remainingStock = emptyStock();
                renderDistributionSummary();

                document.getElementById('po_date').value = selectedPO?.po_date ? String(selectedPO.po_date).slice(0, 10) : '';
                document.getElementById('supplier').value = selectedPO?.supplier?.supplier_name || '';
                document.getElementById('nefa').value = selectedPO?.nefa_number || '';
                printTotalAmountInput.value = formatAmountValue(selectedPO?.total_amount ?? '');

                if (!selectedPO) {
                    renderPurchaseSummary();
                    return;
                }

                try {
                    await loadRemaining(selectedPO.id);
                } catch (error) {
                    alert(error.message);
                }
            }

            function buildErrorMessage(data) {
                if (data?.errors) return Object.values(data.errors).flat().join('\n');
                return data?.message || 'The request could not be completed.';
            }

            printTotalAmountInput.addEventListener('input', () => {
                printTotalAmountInput.value = normalizedAmountValue(printTotalAmountInput.value);
            });
            printTotalAmountInput.addEventListener('blur', restoreFormattedAmount);

            purchaseOrderSelect.addEventListener('change', selectPurchaseOrder);
            document.getElementById('openModal').addEventListener('click', openModal);
            document.getElementById('closeModal').addEventListener('click', closeModal);
            document.getElementById('cancelAssign').addEventListener('click', closeModal);

            provinceSelect.addEventListener('change', () => {
                placeInput.value = selectedProvinceOption()?.dataset.address || '';
                validateAssignmentForm();
            });
            deliveryDateInput.addEventListener('change', validateAssignmentForm);
            quantityInputs.forEach(input => input.addEventListener('input', validateAssignmentForm));

            saveAssignButton.addEventListener('click', () => {
                if (!validateAssignmentForm()) return;

                const option = selectedProvinceOption();
                const items = {};
                quantityInputs.forEach(input => {
                    items[String(input.dataset.itemId)] = Math.max(0, Math.floor(Number(input.value || 0)));
                });

                const row = {
                    province_id: Number(provinceSelect.value),
                    scheduled_delivery_date: deliveryDateInput.value,
                    place_of_delivery: option?.dataset.address || '',
                    items,
                };

                if (editingIndex !== null) distributions[editingIndex] = row;
                else distributions.push(row);

                renderDistributionSummary();
                closeModal();
            });

            distributionSummary.addEventListener('click', event => {
                const editButton = event.target.closest('[data-edit-index]');
                const removeButton = event.target.closest('[data-remove-index]');

                if (editButton) {
                    editingIndex = Number(editButton.dataset.editIndex);
                    const distribution = distributions[editingIndex];
                    if (!distribution) return;

                    refreshProvinceOptions();
                    provinceSelect.value = String(distribution.province_id);
                    provinceSelect.disabled = true;
                    deliveryDateInput.value = distribution.scheduled_delivery_date || '';
                    placeInput.value = distribution.place_of_delivery || selectedProvinceOption()?.dataset.address || '';
                    quantityInputs.forEach(input => {
                        input.value = itemQuantity(distribution, input.dataset.itemId);
                    });
                    saveAssignButton.textContent = 'Update Province';
                    refreshModalAvailability();
                    validateAssignmentForm();
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    return;
                }

                if (removeButton) {
                    const index = Number(removeButton.dataset.removeIndex);
                    const distribution = distributions[index];
                    if (!distribution) return;
                    const option = Array.from(provinceSelect.options).find(row => Number(row.value) === Number(distribution.province_id));
                    if (!confirm(`Remove the PPE allocation for ${option?.dataset.name || 'this province'}?`)) return;
                    distributions.splice(index, 1);
                    renderDistributionSummary();
                }
            });

            previewButton.addEventListener('click', () => {
                if (previewButton.disabled) return;
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                distributionsInput.value = JSON.stringify(distributions);
                prepareAmountForRequest();
                const previewForm = document.createElement('form');
                previewForm.method = 'POST';
                previewForm.action = previewUrl;
                previewForm.target = '_blank';
                previewForm.className = 'hidden';

                new FormData(form).forEach((value, key) => {
                    if (value instanceof File) return;
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = String(value);
                    previewForm.appendChild(input);
                });
                restoreFormattedAmount();

                document.body.appendChild(previewForm);
                previewForm.submit();
                previewForm.remove();
            });

            form.addEventListener('submit', async event => {
                event.preventDefault();
                if (submitButton.disabled) return;
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                distributionsInput.value = JSON.stringify(distributions);
                prepareAmountForRequest();
                const formData = new FormData(form);
                restoreFormattedAmount();

                submitButton.disabled = true;
                const originalText = submitButton.textContent;
                submitButton.textContent = 'Saving...';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(buildErrorMessage(data));
                    alert(data.message || 'Distribution saved successfully.');
                    window.location.href = data.redirect_url;
                } catch (error) {
                    alert(error.message);
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            });

            modal.addEventListener('click', event => {
                if (event.target === modal) closeModal();
            });

            restoreFormattedAmount();
            renderPurchaseSummary();
            renderDistributionSummary();

            if (initialPurchaseOrderId) {
                purchaseOrderSelect.value = String(initialPurchaseOrderId);
                selectPurchaseOrder();
            }
        });
    </script>
</x-po_dashboard_layout>
