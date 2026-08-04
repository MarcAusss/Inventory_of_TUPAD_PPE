@include('tssd.tracking._table-styles')

@php
    $trackingLinks = [
        [
            'route' => 'tssd.tracking.summary',
            'label' => 'PPE Summary',
            'description' => 'PO → Call-Off → DR → project stock flow',
        ],
        [
            'route' => 'tssd.tracking.provincial-stock',
            'label' => 'Provincial Stock',
            'description' => 'Current remaining PPE by Provincial Office',
        ],
        [
            'route' => 'tssd.tracking.call-off-stock',
            'label' => 'Per Call-Off Stock',
            'description' => 'Allocation, receipts, distributions, and balance',
        ],
 
        [
            'route' => 'tssd.tracking.project-transactions',
            'label' => 'Project Transactions',
            'description' => 'Provincial PPE issued per project',
        ],
    ];
@endphp

<section class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
    @foreach ($trackingLinks as $trackingLink)
        @php($isActive = request()->routeIs($trackingLink['route']))
        <a href="{{ route($trackingLink['route']) }}"
            class="rounded-2xl border p-4 transition {{ $isActive
                ? 'border-[#2E628D] bg-[#2E628D] text-white shadow-md'
                : 'border-slate-200 bg-white text-slate-700 shadow-sm hover:border-[#2E628D]/40 hover:bg-[#F7FBFD]' }}">
            <p class="text-sm font-extrabold">{{ $trackingLink['label'] }}</p>
            <p class="mt-1 text-xs leading-5 {{ $isActive ? 'text-white/80' : 'text-slate-500' }}">
                {{ $trackingLink['description'] }}
            </p>
        </a>
    @endforeach
</section>
