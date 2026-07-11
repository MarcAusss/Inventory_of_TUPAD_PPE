<x-po_dashboard_layout title="Provincial Office Dashboard">

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Create Project PPE Designation
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Distribute available provincial PPE to a specific project.
                </p>
            </div>

            <a
                href="{{ route('provincial.project-designations.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Back to Projects
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

        @if($inventories->isEmpty())

            <div class="rounded-2xl border border-yellow-200 bg-yellow-50 px-6 py-10 text-center">

                <h2 class="text-xl font-semibold text-yellow-900">
                    No available provincial inventory
                </h2>

                <p class="mt-2 text-sm text-yellow-800">
                    Receive PPE under an approved Call-Off before creating a project designation.
                </p>

                <a
                    href="{{ route('provincial.receiving.index') }}"
                    class="mt-5 inline-flex rounded-xl bg-red-900 px-5 py-3 font-semibold text-white transition hover:bg-red-800"
                >
                    View Call-Off Allocations
                </a>

            </div>

        @else

            <form
                id="projectDesignationForm"
                action="{{ route('provincial.project-designations.store') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-6"
            >

                @csrf

                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

                    <div class="bg-red-900 px-7 py-5">

                        <h2 class="text-xl font-semibold text-white">
                            Project Information
                        </h2>

                    </div>

                    <div class="grid grid-cols-1 gap-6 p-7 lg:grid-cols-2">

                        <div>

                            <label
                                for="project_code"
                                class="mb-2 block text-sm font-semibold text-gray-700"
                            >
                                Project Code
                            </label>

                            <input
                                type="text"
                                id="project_code"
                                name="project_code"
                                value="{{ old('project_code') }}"
                                required
                                maxlength="100"
                                placeholder="Example: TUPAD-ALB-2026-001"
                                class="w-full rounded-xl border-gray-300 uppercase focus:border-red-900 focus:ring-red-900"
                            >

                            @error('project_code')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <div>

                            <label
                                for="designation_date"
                                class="mb-2 block text-sm font-semibold text-gray-700"
                            >
                                Designation Date
                            </label>

                            <input
                                type="date"
                                id="designation_date"
                                name="designation_date"
                                value="{{ old('designation_date', now()->format('Y-m-d')) }}"
                                required
                                class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                            >

                            @error('designation_date')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <div class="lg:col-span-2">

                            <label
                                for="project_title"
                                class="mb-2 block text-sm font-semibold text-gray-700"
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
                                placeholder="Enter the official project title"
                                class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                            >

                            @error('project_title')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <div class="lg:col-span-2">

                            <label
                                for="location"
                                class="mb-2 block text-sm font-semibold text-gray-700"
                            >
                                Project Location
                            </label>

                            <input
                                type="text"
                                id="location"
                                name="location"
                                value="{{ old('location') }}"
                                required
                                maxlength="500"
                                placeholder="Barangay, municipality, or complete project location"
                                class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                            >

                            @error('location')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <div>

                            <label
                                for="number_of_days"
                                class="mb-2 block text-sm font-semibold text-gray-700"
                            >
                                Number of Days
                            </label>

                            <input
                                type="number"
                                id="number_of_days"
                                name="number_of_days"
                                value="{{ old('number_of_days', 1) }}"
                                min="1"
                                required
                                class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                            >

                            @error('number_of_days')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <div>

                            <label
                                for="number_of_beneficiaries"
                                class="mb-2 block text-sm font-semibold text-gray-700"
                            >
                                Number of Beneficiaries
                            </label>

                            <input
                                type="number"
                                id="number_of_beneficiaries"
                                name="number_of_beneficiaries"
                                value="{{ old('number_of_beneficiaries', 1) }}"
                                min="1"
                                required
                                class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                            >

                            @error('number_of_beneficiaries')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <div class="lg:col-span-2">

                            <label
                                for="are_document"
                                class="mb-2 block text-sm font-semibold text-gray-700"
                            >
                                ARE Document
                            </label>

                            <input
                                type="file"
                                id="are_document"
                                name="are_document"
                                accept="application/pdf,.pdf"
                                required
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3"
                            >

                            <p class="mt-2 text-xs text-gray-500">
                                PDF only. Maximum file size: 10 MB.
                            </p>

                            @error('are_document')
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
                                placeholder="Optional project or PPE distribution remarks."
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

                    <div class="bg-gray-900 px-7 py-5">

                        <h2 class="text-xl font-semibold text-white">
                            PPE to Distribute
                        </h2>

                        <p class="mt-1 text-sm text-gray-300">
                            Enter the quantity to issue. The system will deduct it from your current inventory.
                        </p>

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
                                        Available
                                    </th>

                                    <th class="px-5 py-4 text-center">
                                        Quantity to Designate
                                    </th>

                                    <th class="px-5 py-4 text-center">
                                        Remaining After
                                    </th>

                                </tr>

                            </thead>

                            <tbody class="divide-y divide-gray-100">

                                @foreach($inventories as $inventory)

                                    <tr>

                                        <td class="px-5 py-4 font-semibold text-gray-900">
                                            {{ $inventory->item->item_name }}
                                        </td>

                                        <td class="px-5 py-4 text-gray-700">
                                            {{ $inventory->item->label ?: '—' }}
                                        </td>

                                        <td class="px-5 py-4 text-gray-700">
                                            {{ $inventory->item->unit_of_measurement }}
                                        </td>

                                        <td class="px-5 py-4 text-center font-semibold text-gray-900">
                                            {{ number_format($inventory->quantity) }}
                                        </td>

                                        <td class="px-5 py-4 text-center">

                                           <input
    type="number"
    name="items[{{ $inventory->item_id }}]"
    value="{{ old('items.'.$inventory->item_id, 0) }}"
    min="0"
    max="{{ $inventory->quantity }}"
    step="1"
    inputmode="numeric"
    data-stock-input
    data-available="{{ $inventory->quantity }}"
    data-item-name="{{ trim(
        $inventory->item->item_name.' '.
        ($inventory->item->label ?? '')
    ) }}"
    class="w-32 rounded-lg border-gray-300 text-center
           focus:border-red-900 focus:ring-red-900"
>

<p
    data-stock-error
    class="mt-2 hidden text-xs font-semibold text-red-600"
></p>

@error('items.'.$inventory->item_id)
    <p class="mt-2 text-xs font-semibold text-red-600">
        {{ $message }}
    </p>
@enderror

                                        </td>

                                        <td class="px-5 py-4 text-center">

                                            <span
                                                data-remaining-output
                                                class="font-semibold text-green-700"
                                            >
                                                {{ number_format($inventory->quantity) }}
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
                                        Total PPE to Designate
                                    </td>

                                    <td class="px-5 py-4 text-center">

                                        <span
                                            id="totalDesignationQuantity"
                                            class="text-lg font-bold text-red-900"
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
                        <p class="px-7 pb-5 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a
                        href="{{ route('provincial.project-designations.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        id="submitDesignationButton"
                        class="inline-flex items-center justify-center rounded-xl bg-red-900 px-7 py-3 font-semibold text-white transition hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        Save Project Designation
                    </button>

                </div>

            </form>

        @endif

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById(
                'projectDesignationForm'
            );

            if (!form) {
                return;
            }

            const stockInputs = Array.from(
                document.querySelectorAll(
                    '[data-stock-input]'
                )
            );

            const totalOutput = document.getElementById(
                'totalDesignationQuantity'
            );

            const submitButton = document.getElementById(
                'submitDesignationButton'
            );

            function updateInventoryPreview() {
    let total = 0;
    let valid = true;
    let hasStockError = false;

    stockInputs.forEach(input => {
        const available = Number(
            input.dataset.available || 0
        );

        const itemName =
            input.dataset.itemName || 'PPE item';

        let quantity = Number(
            input.value || 0
        );

        const row = input.closest('tr');

        const remainingOutput = row?.querySelector(
            '[data-remaining-output]'
        );

        const stockError = row?.querySelector(
            '[data-stock-error]'
        );

        if (
            !Number.isFinite(quantity)
            || quantity < 0
        ) {
            quantity = 0;
            input.value = 0;
        }

        quantity = Math.floor(quantity);
        input.value = quantity;

        const remaining = available - quantity;
        const exceedsStock = quantity > available;

        if (remainingOutput) {
            remainingOutput.textContent =
                Math.max(0, remaining)
                    .toLocaleString();

            remainingOutput.className =
                exceedsStock
                    ? 'font-semibold text-red-700'
                    : 'font-semibold text-green-700';
        }

        if (exceedsStock) {
            input.classList.add(
                'border-red-500',
                'bg-red-50',
                'text-red-900'
            );

            input.setAttribute(
                'aria-invalid',
                'true'
            );

            if (stockError) {
                stockError.textContent =
                    `${itemName} has only `
                    +`${available.toLocaleString()} available.`;

                stockError.classList.remove(
                    'hidden'
                );
            }

            valid = false;
            hasStockError = true;
        } else {
            input.classList.remove(
                'border-red-500',
                'bg-red-50',
                'text-red-900'
            );

            input.removeAttribute(
                'aria-invalid'
            );

            if (stockError) {
                stockError.textContent = '';

                stockError.classList.add(
                    'hidden'
                );
            }
        }

        total += quantity;
    });

    if (totalOutput) {
        totalOutput.textContent =
            total.toLocaleString();
    }

    if (total <= 0) {
        valid = false;
    }

    if (submitButton) {
        submitButton.disabled = !valid;
    }

    return {
        valid,
        total,
        hasStockError,
    };
}

            stockInputs.forEach(input => {
                input.addEventListener(
                    'input',
                    updateInventoryPreview
                );
            });

           form.addEventListener(
    'submit',
    function (event) {
        const result =
            updateInventoryPreview();

        if (!result.valid) {
            event.preventDefault();

            const invalidInput =
                stockInputs.find(input => {
                    const available = Number(
                        input.dataset.available || 0
                    );

                    const quantity = Number(
                        input.value || 0
                    );

                    return quantity > available;
                });

            if (invalidInput) {
                const itemName =
                    invalidInput.dataset.itemName
                    || 'PPE item';

                const available = Number(
                    invalidInput.dataset.available || 0
                );

                alert(
                    `${itemName} has only `
                    +`${available.toLocaleString()} available.`
                );

                invalidInput.focus();

                return;
            }

            alert(
                'Enter at least one PPE quantity greater than zero.'
            );

            return;
        }

        if (submitButton) {
            submitButton.disabled = true;

            submitButton.textContent =
                'Saving Designation...';
        }
    }
);
            updateInventoryPreview();
        });
    </script>

</x-po_dashboard_layout>