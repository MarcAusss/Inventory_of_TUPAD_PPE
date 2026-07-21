<x-po_dashboard_layout>

    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-900">
                    Assign Call-Off Number
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Assign one shared Call-Off Number to a complete TSSD distribution batch.
                </p>

            </div>

            <a href="{{ route('tssd.call-offs.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50">
                Back to Call-Offs
            </a>

        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())

            <div class="rounded-xl border border-red-200 bg-red-50 px-6 py-5">

                <h2 class="font-semibold text-red-800">
                    Please correct the following:
                </h2>

                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-red-700">

                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach

                </ul>

            </div>

        @endif

        @if ($distributionBatches->isEmpty())

            <div class="rounded-2xl border border-yellow-200 bg-yellow-50 px-6 py-8 text-center">

                <h2 class="text-xl font-semibold text-yellow-900">
                    No eligible distribution batches
                </h2>

                <p class="mt-2 text-sm text-yellow-800">
                    Create and submit a TSSD distribution batch before assigning a Call-Off Number.
                </p>

                <a href="{{ route('tssd.distributions.create') }}"
                    class="mt-5 inline-flex rounded-xl bg-[#339DCB] px-5 py-3 font-semibold text-white transition hover:bg-red-800">
                    Create Distribution
                </a>

            </div>
        @else
            <form action="{{ route('tssd.call-offs.store') }}" method="POST" class="space-y-6">

                @csrf

                {{-- Call-Off Information --}}
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

                    <div class="bg-[#339DCB] px-7 py-5">

                        <h2 class="text-xl font-semibold text-white">
                            Call-Off Information
                        </h2>

                    </div>

                    <div class="grid grid-cols-1 gap-6 p-7 lg:grid-cols-2">

                        <div class="lg:col-span-2">

                            <label for="tssd_distribution_batch_id"
                                class="mb-2 block text-sm font-semibold text-gray-700">
                                Distribution Batch
                            </label>

                            <select id="tssd_distribution_batch_id" name="tssd_distribution_batch_id" required
                                class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900">

                                <option value="">
                                    Select Distribution Batch
                                </option>

                                @foreach ($distributionBatches as $batch)
                                    <option value="{{ $batch->id }}" @selected(old('tssd_distribution_batch_id') == $batch->id)
                                        data-po-number="{{ $batch->purchaseOrder->po_number }}"
                                        data-supplier="{{ $batch->purchaseOrder->supplier->supplier_name }}"
                                        data-distribution-date="{{ $batch->distribution_date?->format('Y-m-d') }}"
                                        data-province-count="{{ $batch->provinceDistributions->count() }}">
                                        Batch #{{ $batch->id }}
                                        —
                                        {{ $batch->purchaseOrder->po_number }}
                                        —
                                        {{ $batch->purchaseOrder->supplier->supplier_name }}
                                        —
                                        {{ $batch->provinceDistributions->count() }} province(s)
                                    </option>
                                @endforeach

                            </select>

                            @error('tssd_distribution_batch_id')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <div>

                            <label for="call_off_number" class="mb-2 block text-sm font-semibold text-gray-700">
                                Call-Off Number
                            </label>

                            <input type="text" id="call_off_number" name="call_off_number"
                                value="{{ old('call_off_number') }}" required maxlength="100"
                                placeholder="Example: CO-2026-0001"
                                class="w-full rounded-xl border-gray-300 uppercase focus:border-red-900 focus:ring-red-900">

                            @error('call_off_number')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <div>

                            <label for="assigned_at" class="mb-2 block text-sm font-semibold text-gray-700">
                                Assignment Date
                            </label>

                            <input type="date" id="assigned_at" name="assigned_at"
                                value="{{ old('assigned_at', now()->format('Y-m-d')) }}" required
                                class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900">

                            @error('assigned_at')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <div class="lg:col-span-2">

                            <label for="remarks" class="mb-2 block text-sm font-semibold text-gray-700">
                                Remarks
                            </label>

                            <textarea id="remarks" name="remarks" rows="4" maxlength="5000"
                                placeholder="Optional remarks regarding this Call-Off assignment."
                                class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900">{{ old('remarks') }}</textarea>

                            @error('remarks')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </div>

                {{-- Selected Batch Preview --}}
                <div id="batchPreview"
                    class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

                    <div class="bg-gray-900 px-7 py-5">

                        <h2 class="text-xl font-semibold text-white">
                            Selected Distribution Batch
                        </h2>

                    </div>

                    <div class="grid grid-cols-1 gap-6 p-7 sm:grid-cols-2 lg:grid-cols-4">

                        <div>

                            <p class="text-sm font-medium text-gray-500">
                                Batch Number
                            </p>

                            <p id="previewBatchNumber" class="mt-1 font-semibold text-gray-900">
                                —
                            </p>

                        </div>

                        <div>

                            <p class="text-sm font-medium text-gray-500">
                                Purchase Order
                            </p>

                            <p id="previewPurchaseOrder" class="mt-1 font-semibold text-gray-900">
                                —
                            </p>

                        </div>

                        <div>

                            <p class="text-sm font-medium text-gray-500">
                                Supplier
                            </p>

                            <p id="previewSupplier" class="mt-1 font-semibold text-gray-900">
                                —
                            </p>

                        </div>

                        <div>

                            <p class="text-sm font-medium text-gray-500">
                                Provinces Included
                            </p>

                            <p id="previewProvinceCount" class="mt-1 font-semibold text-gray-900">
                                —
                            </p>

                        </div>

                    </div>

                </div>

                {{-- Submit --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a href="{{ route('tssd.call-offs.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50">
                        Cancel
                    </a>

                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-[#339DCB] px-7 py-3 font-semibold text-white transition hover:bg-red-800">
                        Assign Call-Off Number
                    </button>

                </div>

            </form>

        @endif

    </div>

    <script>
        const batchSelect =
            document.getElementById('tssd_distribution_batch_id');

        const batchPreview =
            document.getElementById('batchPreview');

        function updateBatchPreview() {
            if (!batchSelect || !batchPreview) {
                return;
            }

            const option =
                batchSelect.options[batchSelect.selectedIndex];

            if (!batchSelect.value) {
                batchPreview.classList.add('hidden');
                return;
            }

            document.getElementById(
                    'previewBatchNumber'
                ).textContent =
                `Batch #${batchSelect.value}`;

            document.getElementById(
                    'previewPurchaseOrder'
                ).textContent =
                option.dataset.poNumber || 'Not available';

            document.getElementById(
                    'previewSupplier'
                ).textContent =
                option.dataset.supplier || 'Not available';

            document.getElementById(
                    'previewProvinceCount'
                ).textContent =
                option.dataset.provinceCount || '0';

            batchPreview.classList.remove('hidden');
        }

        if (batchSelect) {
            batchSelect.addEventListener(
                'change',
                updateBatchPreview
            );

            updateBatchPreview();
        }
    </script>

</x-po_dashboard_layout>
