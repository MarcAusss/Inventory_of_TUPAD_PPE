@php
    $purchaseOrder = $purchaseOrder ?? new \App\Models\PurchaseOrder();
    $editing = $purchaseOrder->exists;

    $poItems = $editing ? $purchaseOrder->items->keyBy('item_id') : collect();
@endphp

<form id="purchaseOrderForm"
    action="{{ $editing ? route('supply.purchase-orders.update', $purchaseOrder) : route('supply.purchase-orders.store') }}"
    method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf

    @if ($editing)
        @method('PUT')
    @endif

    {{-- =========================================================
        VALIDATION SUMMARY
    ========================================================== --}}
    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
            <p class="font-bold text-red-800">
                Please correct the following fields:
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- =========================================================
        PURCHASE ORDER INFORMATION
    ========================================================== --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">
                Procurement information
            </p>

            <h3 class="mt-1 text-lg font-bold text-slate-950">
                Purchase Order Information
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Enter the Purchase Order references, supplier, date, and remarks before adding PPE quantities.
            </p>
        </div>

        <div class="p-6 sm:p-7">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- PO NUMBER --}}
                <div>
                    <label for="po_number" class="mb-2 block text-sm font-bold text-slate-700">
                        PO Number <span class="text-red-600">*</span>
                    </label>

                    <input type="text" id="po_number" name="po_number"
                        value="{{ old('po_number', $purchaseOrder->po_number) }}" placeholder="Enter PO number"
                        class="w-full rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]">

                    @error('po_number')
                        <p class="mt-1 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- PO DATE --}}
                <div>
                    <label for="po_date" class="mb-2 block text-sm font-bold text-slate-700">
                        PO Date <span class="text-red-600">*</span>
                    </label>

                    <input type="date" id="po_date" name="po_date"
                        value="{{ old('po_date', optional($purchaseOrder->po_date)->format('Y-m-d') ?? $purchaseOrder->po_date) }}"
                        class="w-full rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]">

                    @error('po_date')
                        <p class="mt-1 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- NEFA NUMBER --}}
                <div>
                    <label for="nefa_number" class="mb-2 block text-sm font-bold text-slate-700">
                        NEFA Number
                    </label>

                    <input type="text" id="nefa_number" name="nefa_number"
                        value="{{ old('nefa_number', $purchaseOrder->nefa_number) }}" placeholder="Enter NEFA number"
                        class="w-full rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]">

                    @error('nefa_number')
                        <p class="mt-1 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- SUPPLIER --}}
                <div>
                    <label for="supplier_id" class="mb-2 block text-sm font-bold text-slate-700">
                        Supplier <span class="text-red-600">*</span>
                    </label>

                    <select id="supplier_id" name="supplier_id"
                        class="w-full rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]">
                        <option value="">Select supplier</option>

                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id)>
                                {{ $supplier->supplier_name }}
                            </option>
                        @endforeach
                    </select>

                    @error('supplier_id')
                        <p class="mt-1 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- GRAND TOTAL --}}
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">
                        Calculated Grand Total
                    </label>

                    <div id="grandTotal"
                        class="flex min-h-11 items-center justify-end rounded-xl border border-[#B7D6E6] bg-[#F3FAFD] px-4 text-xl font-bold text-[#143A52]">
                        ₱{{ number_format(old('total_amount', $purchaseOrder->total_amount ?? 0), 2) }}
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        Automatically calculated from the PPE quantities and unit costs below.
                    </p>
                </div>
            </div>

            {{-- REMARKS --}}
            <div class="mt-6">
                <label for="remarks" class="mb-2 block text-sm font-bold text-slate-700">
                    Remarks
                </label>

                <textarea id="remarks" name="remarks" rows="4" placeholder="Enter optional Purchase Order remarks..."
                    class="w-full rounded-xl border-slate-300 focus:border-[#339DCB] focus:ring-[#339DCB]">{{ old('remarks', $purchaseOrder->remarks) }}</textarea>

                @error('remarks')
                    <p class="mt-1 text-sm font-medium text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>
    </section>

    {{-- =========================================================
        PPE INVENTORY
    ========================================================== --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">
                Ordered inventory
            </p>

            <h3 class="mt-1 text-lg font-bold text-slate-950">
                PPE Inventory
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Enter the ordered quantity and unit cost for each PPE item. Leave unused items at zero.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full divide-y divide-slate-200">
                <thead class="bg-slate-100">
                    <tr class="text-xs font-bold uppercase tracking-wide text-slate-600">
                        <th class="px-6 py-4 text-left">No.</th>
                        <th class="px-6 py-4 text-left">Description</th>
                        <th class="px-6 py-4 text-center">Size / Label</th>
                        <th class="px-6 py-4 text-center">Quantity</th>
                        <th class="px-6 py-4 text-center">Unit Cost</th>
                        <th class="px-6 py-4 text-right">Line Total</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($items as $index => $item)
                        @php
                            $poItem = $poItems->get($item->id);
                            $itemLabel = $item->label ?: null;
                        @endphp

                        <tr class="transition hover:bg-slate-50">
                            <td class="px-6 py-5 text-sm text-slate-500">
                                {{ $loop->iteration }}
                            </td>

                            <td class="px-6 py-5">
                                <div class="font-semibold text-slate-900">
                                    {{ $item->item_name }}
                                </div>

                                <input type="hidden" name="items[{{ $index }}][item_id]"
                                    value="{{ $item->id }}">
                            </td>

                            <td class="px-6 py-5 text-center">
                                @if ($itemLabel)
                                    <span
                                        class="inline-flex rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold text-[#2D94BE] ring-1 ring-[#90C4DD]">
                                        {{ $itemLabel }}
                                    </span>

                                    <input type="hidden" name="items[{{ $index }}][size]"
                                        value="{{ $itemLabel }}">
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-5 text-center">
                                <input type="number" min="0" step="1"
                                    name="items[{{ $index }}][quantity]"
                                    value="{{ old("items.$index.quantity", $poItem->quantity ?? 0) }}"
                                    class="qty w-28 rounded-xl border-slate-300 text-center font-semibold focus:border-[#339DCB] focus:ring-[#339DCB]">

                                @error("items.$index.quantity")
                                    <p class="mt-1 text-xs font-medium text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </td>

                            <td class="px-6 py-5 text-center">
                                <div class="relative mx-auto w-40">
                                    <span
                                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-500">
                                        ₱
                                    </span>

                                    <input type="number" min="0" step="0.01"
                                        name="items[{{ $index }}][unit_cost]"
                                        value="{{ old("items.$index.unit_cost", $poItem->unit_cost ?? 0) }}"
                                        class="cost w-full rounded-xl border-slate-300 pl-8 text-right font-semibold focus:border-[#339DCB] focus:ring-[#339DCB]">
                                </div>

                                @error("items.$index.unit_cost")
                                    <p class="mt-1 text-xs font-medium text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </td>

                            <td class="px-6 py-5 text-right text-base font-bold text-[#2D94BE]">
                                ₱<span class="line-total">0.00</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">
                                No PPE reference items are available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot class="bg-slate-100">
                    <tr>
                        <td colspan="5"
                            class="px-6 py-5 text-right text-sm font-bold uppercase tracking-wide text-slate-700">
                            Grand Total
                        </td>

                        <td class="px-6 py-5 text-right text-2xl font-bold text-[#2D94BE]">
                            ₱<span id="grandTotalDisplay">0.00</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

    {{-- =========================================================
        SUPPORTING DOCUMENT
    ========================================================== --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#2D94BE]">
                File attachment
            </p>

            <h3 class="mt-1 text-lg font-bold text-slate-950">
                Supporting Document
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Upload the official Purchase Order document in PDF, DOC, or DOCX format.
            </p>
        </div>

        <div class="p-6 sm:p-7">
            @if ($editing && $purchaseOrder->document)
                <div
                    class="mb-5 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-bold text-slate-900">
                            Existing supporting document
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Uploading another file will replace the current document.
                        </p>
                    </div>

                    <a href="{{ Storage::url($purchaseOrder->document) }}" target="_blank" rel="noopener"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                        View Current Document
                    </a>
                </div>
            @endif

            <label for="document"
                class="group relative flex min-h-56 w-full cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 text-center transition hover:border-[#970C13] hover:bg-[#DF979B]/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-[#2D94BE]" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 16V4m0 0L7 9m5-5 5 5M5 20h14" />
                </svg>

                <p class="mt-4 text-lg font-bold text-slate-800">
                    Click to select a document
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    PDF, DOC, or DOCX — maximum file size of 10 MB
                </p>

                <p id="fileName" class="mt-4 text-sm font-bold text-[#2D94BE]"></p>

                <input id="document" type="file" name="document" accept=".pdf,.doc,.docx" class="hidden">
            </label>

            @error('document')
                <p class="mt-3 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>
    </section>

    {{-- =========================================================
        ACTION BUTTONS
    ========================================================== --}}
    <section
        class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-end">
        <a href="{{ route('supply.purchase-orders.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
            Cancel
        </a>

        <button type="submit"
            class="inline-flex items-center justify-center rounded-xl bg-[#339DCB] px-8 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#2D94BE]">
            {{ $editing ? 'Update Purchase Order' : 'Save Purchase Order' }}
        </button>
    </section>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('purchaseOrderForm');

        if (!form) {
            console.error('Purchase Order form was not found.');
            return;
        }

        const grandTotalDisplay =
            document.getElementById('grandTotalDisplay');

        const grandTotalCard =
            document.getElementById('grandTotal');

        const documentInput =
            document.getElementById('document');

        const fileName =
            document.getElementById('fileName');

        function parseNumber(value) {
            const number = Number.parseFloat(value);

            return Number.isFinite(number) ?
                Math.max(0, number) :
                0;
        }

        function formatCurrency(value) {
            return Number(value).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }

        function calculateGrandTotal() {
            let grandTotal = 0;

            const rows = form.querySelectorAll(
                'tbody tr'
            );

            rows.forEach(function(row) {
                const quantityInput =
                    row.querySelector('.qty');

                const costInput =
                    row.querySelector('.cost');

                const lineTotalElement =
                    row.querySelector('.line-total');

                /*
                 * Ignore the empty-state table row.
                 */
                if (
                    !quantityInput ||
                    !costInput ||
                    !lineTotalElement
                ) {
                    return;
                }

                const quantity = parseNumber(
                    quantityInput.value
                );

                const unitCost = parseNumber(
                    costInput.value
                );

                const lineTotal =
                    quantity * unitCost;

                lineTotalElement.textContent =
                    formatCurrency(lineTotal);

                grandTotal += lineTotal;
            });

            if (grandTotalDisplay) {
                grandTotalDisplay.textContent =
                    formatCurrency(grandTotal);
            }

            if (grandTotalCard) {
                grandTotalCard.textContent =
                    '₱' + formatCurrency(grandTotal);
            }
        }

        /*
         * Recalculate while typing or using number controls.
         */
        form.addEventListener(
            'input',
            function(event) {
                if (
                    event.target.matches(
                        '.qty, .cost'
                    )
                ) {
                    calculateGrandTotal();
                }
            }
        );

        /*
         * Also support browser number-input changes.
         */
        form.addEventListener(
            'change',
            function(event) {
                if (
                    event.target.matches(
                        '.qty, .cost'
                    )
                ) {
                    calculateGrandTotal();
                }
            }
        );

        if (documentInput && fileName) {
            documentInput.addEventListener(
                'change',
                function() {
                    const selectedFile =
                        documentInput.files?.[0];

                    fileName.textContent =
                        selectedFile ?
                        selectedFile.name :
                        '';
                }
            );
        }

        /*
         * Calculate values already present when the page loads.
         */
        calculateGrandTotal();
    });
</script>
