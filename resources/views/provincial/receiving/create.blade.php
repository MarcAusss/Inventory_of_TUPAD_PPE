<x-po_dashboard_layout title="Receive PPE Delivery">

    @php
        $batch = $provinceDistribution->distributionBatch;
        $callOff = $batch?->callOff;
        $purchaseOrder = $batch?->purchaseOrder;
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Receive PPE Delivery
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Confirm the actual quantities physically received under
                    <span class="font-semibold">
                        {{ $callOff?->call_off_number }}
                    </span>.
                </p>
            </div>

            <a
                href="{{ route('provincial.receiving.show', $provinceDistribution) }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Back to Allocation
            </a>

        </div>

        @if($errors->any())

            <div class="rounded-xl border border-red-200 bg-red-50 px-6 py-5">

                <h2 class="font-semibold text-red-800">
                    Please correct the following:
                </h2>

                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-red-900 px-7 py-5">
                <h2 class="text-xl font-semibold text-white">
                    Allocation Information
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 p-7 sm:grid-cols-2 lg:grid-cols-4">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Call-Off Number
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $callOff?->call_off_number ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Source Purchase Order
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $purchaseOrder?->po_number ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Supplier
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $purchaseOrder?->supplier?->supplier_name ?? 'Not available' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Province
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $provinceDistribution->province->name }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Scheduled Delivery
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $provinceDistribution->scheduled_delivery_date?->format('F d, Y') ?? 'Not set' }}
                    </p>
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <p class="text-sm font-medium text-gray-500">
                        Place of Delivery
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $provinceDistribution->place_of_delivery ?: 'No delivery address recorded' }}
                    </p>
                </div>

            </div>

        </div>

        <form
            id="receivingForm"
            action="{{ route('provincial.receiving.store', $provinceDistribution) }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6"
        >

            @csrf

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

                <div class="bg-gray-900 px-7 py-5">
                    <h2 class="text-xl font-semibold text-white">
                        Delivery Receipt Information
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-6 p-7 lg:grid-cols-2">

                    <div>
                        <label
                            for="dr_number"
                            class="mb-2 block text-sm font-semibold text-gray-700"
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
                            placeholder="Example: DR-2026-0001"
                            class="w-full rounded-xl border-gray-300 uppercase focus:border-red-900 focus:ring-red-900"
                        >

                        @error('dr_number')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="delivery_date"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Actual Delivery Date
                        </label>

                        <input
                            type="date"
                            id="delivery_date"
                            name="delivery_date"
                            value="{{ old('delivery_date', now()->format('Y-m-d')) }}"
                            required
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                        >

                        @error('delivery_date')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="physical_receiver_name"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Physical Receiver Name
                        </label>

                        <input
                            type="text"
                            id="physical_receiver_name"
                            name="physical_receiver_name"
                            value="{{ old('physical_receiver_name', auth()->user()->name) }}"
                            required
                            maxlength="255"
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                        >

                        @error('physical_receiver_name')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="document"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Delivery Receipt PDF
                        </label>

                        <input
                            type="file"
                            id="document"
                            name="document"
                            required
                            accept="application/pdf,.pdf"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3"
                        >

                        <p class="mt-2 text-xs text-gray-500">
                            PDF only. Maximum file size: 10 MB.
                        </p>

                        @error('document')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label
                            for="remarks"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Remarks
                        </label>

                        <textarea
                            id="remarks"
                            name="remarks"
                            rows="4"
                            maxlength="5000"
                            placeholder="Example: Complete delivery, damaged package, or quantity shortage."
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                        >{{ old('remarks') }}</textarea>

                        @error('remarks')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

                <div class="bg-red-900 px-7 py-5">
                    <h2 class="text-xl font-semibold text-white">
                        Confirm Received PPE Quantities
                    </h2>

                    <p class="mt-1 text-sm text-red-100">
                        Received quantities cannot exceed the assigned quantities.
                    </p>
                </div>

                <div
                    id="receivingWarning"
                    class="hidden border-b border-red-200 bg-red-50 px-7 py-4 text-sm font-semibold text-red-700"
                >
                    One or more received quantities exceed the assigned allocation.
                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-100">

                            <tr class="text-xs font-semibold uppercase tracking-wide text-gray-700">
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
                                    Assigned
                                </th>

                                <th class="px-5 py-4 text-center">
                                    Received
                                </th>

                                <th class="px-5 py-4 text-center">
                                    Shortage
                                </th>
                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-100">

                            @foreach($provinceDistribution->items as $allocationItem)

                                @php
                                    $oldReceived = (int) old(
                                        'items.'.$allocationItem->id,
                                        $allocationItem->quantity
                                    );

                                    $shortage = max(
                                        0,
                                        $allocationItem->quantity - $oldReceived
                                    );

                                    $displayName = $allocationItem->item->label
                                        ? $allocationItem->item->item_name
                                            .' ('
                                            .$allocationItem->item->label
                                            .')'
                                        : $allocationItem->item->item_name;
                                @endphp

                                <tr data-receiving-row>

                                    <td class="px-5 py-4 font-medium text-gray-900">
                                        {{ $allocationItem->item->item_name }}
                                    </td>

                                    <td class="px-5 py-4 text-gray-700">
                                        {{ $allocationItem->item->label ?: '—' }}
                                    </td>

                                    <td class="px-5 py-4 text-gray-700">
                                        {{ $allocationItem->item->unit_of_measurement }}
                                    </td>

                                    <td class="px-5 py-4 text-center font-semibold text-gray-900">
                                        {{ number_format($allocationItem->quantity) }}
                                    </td>

                                    <td class="px-5 py-4 text-center">

                                        <input
                                            type="number"
                                            name="items[{{ $allocationItem->id }}]"
                                            value="{{ $oldReceived }}"
                                            min="0"
                                            max="{{ $allocationItem->quantity }}"
                                            step="1"
                                            required
                                            inputmode="numeric"
                                            data-receiving-input
                                            data-assigned="{{ $allocationItem->quantity }}"
                                            data-item-name="{{ $displayName }}"
                                            class="w-28 rounded-lg border-gray-300 text-center focus:border-red-900 focus:ring-red-900"
                                        >

                                        <p
                                            data-receiving-error
                                            class="mt-2 hidden text-xs font-semibold text-red-600"
                                        ></p>

                                        @error('items.'.$allocationItem->id)
                                            <p class="mt-2 text-xs font-semibold text-red-600">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <span
                                            data-shortage-output
                                            class="{{ $shortage > 0
                                                ? 'font-semibold text-yellow-700'
                                                : 'font-semibold text-green-700' }}"
                                        >
                                            {{ number_format($shortage) }}
                                        </span>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                        <tfoot class="bg-gray-100">
                            <tr>
                                <td
                                    colspan="4"
                                    class="px-5 py-4 text-right font-semibold text-gray-700"
                                >
                                    Total Received
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span
                                        id="totalReceivedQuantity"
                                        class="text-lg font-bold text-green-700"
                                    >
                                        0
                                    </span>
                                </td>

                                <td></td>
                            </tr>
                        </tfoot>

                    </table>

                </div>

                @error('items')
                    <p class="px-7 pb-5 text-sm font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <a
                    href="{{ route('provincial.receiving.show', $provinceDistribution) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    id="confirmReceiptButton"
                    class="inline-flex items-center justify-center rounded-xl bg-green-600 px-7 py-3 font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    Confirm Receipt
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
                        'receivingForm'
                    );

                if (!form) {
                    return;
                }

                const inputs = Array.from(
                    document.querySelectorAll(
                        '[data-receiving-input]'
                    )
                );

                const warning =
                    document.getElementById(
                        'receivingWarning'
                    );

                const totalOutput =
                    document.getElementById(
                        'totalReceivedQuantity'
                    );

                const submitButton =
                    document.getElementById(
                        'confirmReceiptButton'
                    );

                function validateReceiving() {
                    let total = 0;
                    let valid = true;
                    let hasExcess = false;

                    inputs.forEach(input => {
                        const assigned = Number(
                            input.dataset.assigned || 0
                        );

                        const itemName =
                            input.dataset.itemName
                            || 'PPE item';

                        let received = Number(
                            input.value || 0
                        );

                        if (
                            !Number.isFinite(received)
                            || received < 0
                        ) {
                            received = 0;
                        }

                        received =
                            Math.floor(received);

                        input.value =
                            received;

                        const row =
                            input.closest(
                                '[data-receiving-row]'
                            );

                        const errorOutput =
                            row?.querySelector(
                                '[data-receiving-error]'
                            );

                        const shortageOutput =
                            row?.querySelector(
                                '[data-shortage-output]'
                            );

                        const exceeds =
                            received > assigned;

                        const shortage =
                            Math.max(
                                0,
                                assigned - received
                            );

                        if (shortageOutput) {
                            shortageOutput.textContent =
                                shortage.toLocaleString();

                            shortageOutput.className =
                                shortage > 0
                                    ? 'font-semibold text-yellow-700'
                                    : 'font-semibold text-green-700';
                        }

                        if (exceeds) {
                            valid = false;
                            hasExcess = true;

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
                                    `${itemName} has an assigned quantity of `
                                    +`${assigned.toLocaleString()}, but `
                                    +`${received.toLocaleString()} was entered.`;

                                errorOutput.classList.remove(
                                    'hidden'
                                );
                            }
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

                        total += received;
                    });

                    if (total <= 0) {
                        valid = false;
                    }

                    totalOutput.textContent =
                        total.toLocaleString();

                    warning.classList.toggle(
                        'hidden',
                        !hasExcess
                    );

                    submitButton.disabled =
                        !valid;

                    return {
                        valid,
                        total,
                        hasExcess,
                    };
                }

                inputs.forEach(input => {
                    input.addEventListener(
                        'input',
                        validateReceiving
                    );

                    input.addEventListener(
                        'blur',
                        validateReceiving
                    );
                });

                form.addEventListener(
                    'submit',
                    function (event) {
                        const result =
                            validateReceiving();

                        if (!result.valid) {
                            event.preventDefault();

                            const invalidInput =
                                inputs.find(
                                    input =>
                                        Number(
                                            input.value || 0
                                        )
                                        > Number(
                                            input.dataset.assigned
                                            || 0
                                        )
                                );

                            if (invalidInput) {
                                invalidInput.focus();

                                alert(
                                    `${invalidInput.dataset.itemName} exceeds its assigned quantity.`
                                );

                                return;
                            }

                            alert(
                                'Enter at least one received PPE quantity greater than zero.'
                            );

                            return;
                        }

                        submitButton.disabled =
                            true;

                        submitButton.textContent =
                            'Submitting Receipt...';
                    }
                );

                validateReceiving();
            }
        );
    </script>

</x-po_dashboard_layout>