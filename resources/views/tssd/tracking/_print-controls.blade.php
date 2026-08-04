@php
    $printFilters = collect($printFilters ?? [])->filter(fn ($value) => filled($value));
@endphp

<div class="tracking-no-print flex justify-end">
    <button type="button" onclick="window.print()"
        class="inline-flex items-center justify-center rounded-xl bg-[#2E628D] px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#244F73]">
        Print Current View
    </button>
</div>

<div class="tracking-print-only hidden">
    <div class="mb-4 border-b-2 border-[#2E628D] pb-3">
        <div class="flex items-start justify-center gap-4">
            <img src="{{ asset('images/print/mainlogo.png') }}" alt="DOLE Logo"
                class="h-[82px] w-[118px] object-contain" onerror="this.style.display='none'">

            <div class="min-w-[300px] text-center text-black">
                <p class="m-0 text-[10px]">Republic of the Philippines</p>
                <p class="m-0 text-[12px] font-black">DEPARTMENT OF LABOR AND EMPLOYMENT</p>
                <p class="m-0 text-[10px]">Regional Office No. 5</p>
                <p class="m-0 text-[9px]">DOLE RO5 Bldg., Doña Aurora St., Old Albay, Legazpi City</p>
                <p class="mt-2 text-[13px] font-black uppercase tracking-wide text-[#2E628D]">{{ $reportTitle ?? 'PPE Tracking Center Report' }}</p>
            </div>

            <img src="{{ asset('images/print/iso-bureau-veritas.jpg') }}" alt="ISO Bureau Veritas"
                class="h-[82px] w-[145px] object-contain" onerror="this.style.display='none'">
        </div>

        <div class="mt-3 flex flex-wrap justify-center gap-x-5 gap-y-1 text-[9px] text-slate-700">
            @if ($printFilters->isEmpty())
                <span>Filters: All records in the current view</span>
            @else
                @foreach ($printFilters as $label => $value)
                    <span><strong>{{ $label }}:</strong> {{ $value }}</span>
                @endforeach
            @endif
            <span><strong>Printed:</strong> {{ now()->format('M d, Y h:i A') }}</span>
        </div>
    </div>
</div>
