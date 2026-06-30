@php
    $purchaseOrder = $purchaseOrder ?? new \App\Models\PurchaseOrder();
    $editing = $purchaseOrder->exists;
@endphp

<form action="{{ $editing
    ? route('supply.purchase-orders.update', $purchaseOrder)
    : route('supply.purchase-orders.store') }}" method="POST" enctype="multipart/form-data">

    @csrf

    @if($editing)
        @method('PUT')
    @endif


    <div class="max-w-7xl mx-auto py-8 space-y-8">

        @if ($errors->any())

            <div class="bg-red-50 border border-red-200 rounded-xl p-5">

                <h3 class="text-red-700 font-semibold mb-2">
                    Please fix the following errors:
                </h3>

                <ul class="list-disc list-inside text-red-600 text-sm space-y-1">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif

        <!-- Purchase Order Information -->
        <div class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden">

            <div class="px-8 py-6 bg-red-900">

                <h3 class="text-2xl font-semibold text-white">

                    Purchase Order Information

                </h3>

                <p class="text-indigo-100 mt-1 text-sm">

                    Enter the purchase order details before adding the PPE inventory.

                </p>

            </div>

            <div class="p-8">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- PO Number -->
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">

                            PO Number

                        </label>

                        <input type="text" name="po_number" value="{{ old('po_number', $purchaseOrder->po_number) }}"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

                        @error('po_number')

                            <p class="text-red-500 text-sm mt-1">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                    <!-- PO Date -->
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">

                            PO Date

                        </label>

                        <input type="date" name="po_date"
                            value="{{ old('po_date', optional($purchaseOrder->po_date)->format('Y-m-d') ?? $purchaseOrder->po_date) }}"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

                        @error('po_date')

                            <p class="text-red-500 text-sm mt-1">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                    <!-- NEFA -->
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">

                            NEFA Number

                        </label>

                        <input type="text" name="nefa_number"
                            value="{{ old('nefa_number', $purchaseOrder->nefa_number) }}"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

                        @error('nefa_number')

                            <p class="text-red-500 text-sm mt-1">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

                    <!-- Supplier -->
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">

                            Supplier

                        </label>

                        <select name="supplier_id"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

                            <option value="">

                                Select Supplier

                            </option>

                            @foreach($suppliers as $supplier)

                                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id)>

                                    {{ $supplier->supplier_name }}

                                </option>

                            @endforeach

                        </select>

                        @error('supplier_id')

                            <p class="text-red-500 text-sm mt-1">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                    <!-- Total -->
                    <div>

                        <label class="block text-sm font-semibold text-gray-700 mb-2">

                            Grand Total

                        </label>

                        <div id="grandTotal"
                            class="h-11 flex items-center justify-end px-4 rounded-xl border border-gray-300 bg-gray-100 text-xl font-bold text-red-900">

                            ₱{{ number_format(old('total_amount', $purchaseOrder->total_amount ?? 0), 2) }}

                        </div>

                    </div>

                </div>

                <!-- Remarks -->
                <div class="mt-6">

                    <label class="block text-sm font-semibold text-gray-700 mb-2">

                        Remarks

                    </label>

                    <textarea name="remarks" rows="4"
                        class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('remarks', $purchaseOrder->remarks) }}</textarea>

                    @error('remarks')

                        <p class="text-red-500 text-sm mt-1">

                            {{ $message }}

                        </p>

                    @enderror

                </div>

            </div>

        </div>

        <!-- SECTION 2 (PPE Items) GOES HERE -->

    </div>


    <!-- PPE Inventory -->
    <div class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden">

        <div class="px-8 py-6 bg-red-900 flex items-center justify-between">

            <div>

                <h3 class="text-2xl font-semibold text-white">
                    PPE Inventory
                </h3>

                <p class="text-indigo-100 text-sm mt-1">
                    Enter the quantity and unit cost for every PPE item.
                </p>

            </div>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-gray-100">

                    <tr class="text-sm uppercase tracking-wider text-gray-700">

                        <th class="px-6 py-4 text-left">
                            Description
                        </th>

                        <th class="px-6 py-4 text-center">
                            Size
                        </th>

                        <th class="px-6 py-4 text-center">
                            Quantity
                        </th>

                        <th class="px-6 py-4 text-center">
                            Unit Cost
                        </th>

                        <th class="px-6 py-4 text-center">
                            Total
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">

                    @php
                        $index = 0;

                        $poItems = $editing
                            ? $purchaseOrder->items->keyBy('item_id')
                            : collect();
                    @endphp

                    @foreach($items as $item)
                        @php
                            $poItem = $poItems->get($item->id);
                        @endphp
                        {{-- LONGSLEEVES --}}
                        @if(strtolower($item->item_name) == 'longsleeves')

                        @elseif(strtolower($item->item_name) == 'long sleeve')

                            <tr class="hover:bg-gray-50 transition">

                                <td class="px-6 py-4 font-medium text-gray-800">

                                    {{ $item->item_name }}

                                    <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->id }}">

                                </td>

                                <td class="text-center">

                                    <span class="inline-flex px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm">

                                        {{ $item->label ?? '—' }}

                                    </span>

                                    <input type="hidden" name="items[{{ $index }}][size]" value="{{ $item->label }}">

                                </td>

                                <td class="px-4 py-3">

                                    <input type="number" min="0" value="{{ old("items.$index.quantity", $poItem->quantity ?? 0) }}" name="items[{{ $index }}][quantity]"
                                        class="qty w-28 mx-auto rounded-lg border-gray-300 text-center">

                                </td>

                                <td class="px-4 py-3">

                                    <input type="number" min="0" step="0.01" value="{{ old("items.$index.unit_cost", $poItem->unit_cost ?? 0) }}" name="items[{{ $index }}][unit_cost]"
                                        class="cost w-36 mx-auto rounded-lg border-gray-300 text-center">

                                </td>

                                <td class="text-center font-semibold text-red-900">

                                    ₱<span class="line-total">0.00</span>

                                </td>

                            </tr>


                            {{-- RUBBER BOOTS --}}
                        @elseif(strtolower($item->item_name) == 'rubber boots')

                            <tr class="hover:bg-gray-50">

                                <td class="px-6 py-4 font-medium">

                                    {{ $item->item_name }}

                                    <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->id }}">

                                </td>

                                <td class="text-center">

                                    <span class="inline-flex px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm">

                                        {{ $item->label }}

                                    </span>

                                    <input type="hidden" name="items[{{ $index }}][size]" value="{{ $item->label }}">

                                </td>

                                <td class="px-4 py-3">

                                    <input type="number" min="0" value="{{ old("items.$index.quantity", $poItem->quantity ?? 0) }}" name="items[{{ $index }}][quantity]"
                                        class="qty w-28 mx-auto rounded-lg border-gray-300 text-center">

                                </td>

                                <td class="px-4 py-3">

                                    <input type="number" min="0" step="0.01" value="{{ old("items.$index.unit_cost", $poItem->unit_cost ?? 0) }}" name="items[{{ $index }}][unit_cost]"
                                        class="cost w-36 mx-auto rounded-lg border-gray-300 text-center">

                                </td>

                                <td class="text-center font-semibold text-red-900">

                                    ₱<span class="line-total">0.00</span>

                                </td>

                            </tr>

                        @else

                            <tr class="hover:bg-gray-50">

                                <td class="px-6 py-4 font-medium text-gray-800">

                                    {{ $item->item_name }}

                                    <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->id }}">

                                </td>

                                <td class="text-center text-gray-400">

                                    —

                                </td>

                                <td class="px-4 py-3">

                                    <input type="number" min="0" value="{{ old("items.$index.quantity", $poItem->quantity ?? 0) }}" name="items[{{ $index }}][quantity]"
                                        class="qty w-28 mx-auto rounded-lg border-gray-300 text-center">

                                </td>

                                <td class="px-4 py-3">

                                    <input type="number" min="0" step="0.01" value="{{ old("items.$index.unit_cost", $poItem->unit_cost ?? 0) }}" name="items[{{ $index }}][unit_cost]"
                                        class="cost w-36 mx-auto rounded-lg border-gray-300 text-center">

                                </td>

                                <td class="text-center font-semibold text-red-900">

                                    ₱<span class="line-total">0.00</span>

                                </td>

                            </tr>

                           
                        @endif
                           @php
                                $index++;
                            @endphp

                    @endforeach

                </tbody>

                <tfoot class="bg-gray-50">

                    <tr>

                        <td colspan="4" class="px-6 py-5 text-right text-lg font-bold">

                            GRAND TOTAL

                        </td>

                        <td class="px-6 py-5 text-center text-2xl font-bold text-red-900">

                            ₱<span id="grandTotalDisplay">0.00</span>

                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>

    </div>

    <!-- Supporting Document -->
    <div class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden">

        <div class="px-8 py-6 bg-red-900">

            <h3 class="text-2xl font-semibold text-white">
                Supporting Document
            </h3>

            <p class="text-indigo-100 text-sm mt-1">
                Upload the Purchase Order document (PDF, DOC, DOCX).
            </p>

        </div>

        <div class="p-8">

            <label for="document"
                class="relative flex flex-col items-center justify-center w-full h-56 border-2 border-dashed border-gray-300 rounded-2xl cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 transition">

                <div class="flex flex-col items-center justify-center">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-indigo-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16V8m0 0L3 12m4-4l4 4m6 4V8m0 0l-4 4m4-4l4 4" />

                    </svg>

                    <p class="mt-4 text-lg font-semibold text-gray-700">

                        Click to upload

                    </p>

                    <p class="text-sm text-gray-500">

                        PDF, DOC, DOCX (Maximum 10MB)

                    </p>

                    <p id="fileName" class="mt-4 text-red-900 font-medium">

                    </p>

                </div>

                <input id="document" type="file" name="document" accept=".pdf,.doc,.docx" class="hidden">

            </label>

            @error('document')

                <p class="text-red-500 text-sm mt-3">

                    {{ $message }}

                </p>

            @enderror

        </div>

    </div>


    <!-- Action Buttons -->
    <div class="flex flex-col-reverse md:flex-row justify-end gap-4">

        <a href="{{ route('supply.purchase-orders.index') }}"
            class="px-8 py-3 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-center font-medium transition">

            Cancel

        </a>

        <button type="submit"
            class="px-10 py-3 rounded-xl bg-red-900 hover:bg-indigo-700 text-white font-semibold shadow-lg transition">

            {{ $editing ? 'Update Purchase Order' : 'Save Purchase Order' }}

        </button>

    </div>

    </div>

</form>

<script>

    function formatNumber(number) {
        return parseFloat(number || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function calculateRow(row) {

        const qtyInput = row.querySelector('.qty');
        const costInput = row.querySelector('.cost');
        const totalEl = row.querySelector('.line-total');

        let qty = parseFloat(qtyInput.value) || 0;
        let cost = parseFloat(costInput.value) || 0;

        let total = qty * cost;

        totalEl.innerText = formatNumber(total);

        return total;
    }

    function calculateGrandTotal() {

        let grandTotal = 0;

        document.querySelectorAll('tbody tr').forEach(row => {

            // only rows that have qty + cost
            if (row.querySelector('.qty') && row.querySelector('.cost')) {
                grandTotal += calculateRow(row);
            }

        });

        document.getElementById('grandTotalDisplay').innerText = formatNumber(grandTotal);

        // also sync header total (top card)
        const topTotal = document.getElementById('grandTotal');
        if (topTotal) {
            topTotal.innerText = '₱' + formatNumber(grandTotal);
        }

        return grandTotal;
    }

    // EVENT: input changes (qty or cost)
    document.addEventListener('input', function (e) {

        if (e.target.classList.contains('qty') ||
            e.target.classList.contains('cost')) {

            calculateGrandTotal();

        }

    });

    // INITIAL CALCULATION ON LOAD
    window.addEventListener('DOMContentLoaded', function () {

        calculateGrandTotal();

    });

</script>