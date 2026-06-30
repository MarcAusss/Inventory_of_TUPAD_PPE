@php
    $supplier = $supplier ?? new \App\Models\Supplier();
@endphp

    <div class="space-y-6">

        <!-- Supplier Name -->
        <div>
            <label class="block text-sm font-medium text-gray-700">
                Supplier Name
            </label>

            <input
                type="text"
                name="supplier_name"
            value="{{ old('supplier_name', optional($supplier)->supplier_name) }}"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>

            @error('supplier_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Contact Person -->
        <div>
            <label class="block text-sm font-medium text-gray-700">
                Contact Person
            </label>

            <input
                type="text"
                name="contact_person"
                value="{{ old('contact_person', $supplier->contact_person ?? '') }}"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
                required>

            @error('contact_person')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Contact Number -->
        <div>
            <label class="block text-sm font-medium text-gray-700">
                Contact Number
            </label>

            <input
                type="text"
                name="contact_number"
                value="{{ old('contact_number', $supplier->contact_number ?? '') }}"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
                required>

            @error('contact_number')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700">
                Email
            </label>

            <input
                type="email"
                name="email"
                value="{{ old('email', $supplier->email ?? '') }}"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm">

            @error('email')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Address -->
        <div>
            <label class="block text-sm font-medium text-gray-700">
                Address
            </label>

            <textarea
                name="address"
                rows="3"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
                required>{{ old('address', $supplier->address ?? '') }}</textarea>

            @error('address')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remarks -->
        <div>
            <label class="block text-sm font-medium text-gray-700">
                Remarks
            </label>

            <textarea
                name="remarks"
                rows="3"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm">{{ old('remarks', $supplier->remarks ?? '') }}</textarea>
        </div>

        <!-- Status -->
        <div class="flex items-center gap-2">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}
                class="rounded">

            <label>
                Active Supplier
            </label>
        </div>

    </div>