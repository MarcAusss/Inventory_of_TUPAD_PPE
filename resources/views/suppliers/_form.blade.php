@php
    $supplier = $supplier ?? new \App\Models\Supplier();
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    @php
        $fields = [
            ['supplier_name', 'Supplier Name', 'text', true],
            ['contact_person', 'Contact Person', 'text', true],
            ['contact_number', 'Contact Number', 'text', true],
            ['email', 'Email Address', 'email', false],
        ];
    @endphp

    @foreach ($fields as [$name, $label, $type, $required])
        <div class="{{ $name === 'supplier_name' ? 'lg:col-span-2' : '' }}">
            <label for="{{ $name }}" class="mb-2 block text-sm font-bold text-slate-700">
                {{ $label }} @if ($required)
                    <span class="text-red-600">*</span>
                @endif
            </label>
            <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
                value="{{ old($name, $supplier->{$name} ?? '') }}" @required($required)
                class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#970C13] focus:ring-[#970C13]">
            @error($name)
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach

    <div class="flex items-end">
        <label
            class="flex w-full cursor-pointer items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
            <input type="checkbox" name="is_active" value="1"
                {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}
                class="rounded border-slate-300 text-[#970C13] focus:ring-[#970C13]">
            <span>
                <span class="block text-sm font-bold text-slate-900">Active Supplier</span>
                <span class="mt-1 block text-xs text-slate-500">Available for Purchase Order selection.</span>
            </span>
        </label>
    </div>

    <div class="lg:col-span-2">
        <label for="address" class="mb-2 block text-sm font-bold text-slate-700">Address <span
                class="text-red-600">*</span></label>
        <textarea id="address" name="address" rows="3" required
            class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#970C13] focus:ring-[#970C13]">{{ old('address', $supplier->address ?? '') }}</textarea>
        @error('address')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="lg:col-span-2">
        <label for="remarks" class="mb-2 block text-sm font-bold text-slate-700">Remarks</label>
        <textarea id="remarks" name="remarks" rows="3"
            class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#970C13] focus:ring-[#970C13]">{{ old('remarks', $supplier->remarks ?? '') }}</textarea>
        @error('remarks')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

