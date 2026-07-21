@props([
    'title' => 'Dashboard',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ $title }} | {{ config('app.name', 'TUPAD Inventory System') }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-[#F7FBFD] font-sans text-[#36566E]">

    @php
        $user = auth()->user();

        $roleName = $user?->role?->name ?? 'Unknown Role';

        $dashboardRoute = match (true) {
            $user?->isSupply() => 'supply.dashboard',

            $user?->isTssd() => 'tssd.dashboard',

            $user?->isProvincial() => 'provincial.dashboard',

            $user?->isAccounting() => 'accounting.dashboard',

            default => 'dashboard',
        };
    @endphp

    <div x-data="{ sidebarOpen: false }" class="min-h-screen">

        {{-- Mobile overlay --}}
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 transform flex-col bg-[#F7FBFD] shadow-xl transition-transform duration-300 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            <div class="flex h-full flex-col">

                {{-- Logos --}}
                <div class="border-b border-[#D8EAF3] px-7 py-6">

                    <a href="{{ route($dashboardRoute) }}" class="flex items-center justify-center gap-3">

                        <img src="{{ asset('images/Primary Logo _ Full Color (3).png') }}" alt="Primary logo"
                            class="h-14 w-auto object-contain">

                        <img src="{{ asset('images/Primary Logo _ Full Color (3) - Copy.png') }}" alt="Secondary logo"
                            class="h-14 w-auto object-contain">

                    </a>

                    <div class="mt-4 text-center">

                        <p class="font-bold text-[#143A52]">
                            TUPAD PPE Inventory
                        </p>

                        <p class="mt-1 text-sm text-[#5F7D91]">
                            {{ $roleName }}
                        </p>

                        @if ($user?->isProvincial())
                            <p class="mt-1 text-xs font-semibold text-red-900">
                                {{ $user->provinceName() ?? 'No province assigned' }}
                            </p>
                        @endif

                    </div>

                </div>

                {{-- Navigation --}}
                <nav class="flex-1 overflow-y-auto px-4 py-6">

                    <p class="mb-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#5F7D91]">
                        Menu
                    </p>

                    {{-- Supply Unit --}}
                    @if ($user?->isSupply())

                        <div class="space-y-2">

                            <a href="{{ route('supply.dashboard') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('supply.dashboard')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Dashboard
                            </a>

                            <a href="{{ route('supply.purchase-orders.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('supply.purchase-orders.*')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Purchase Orders
                            </a>

                            <a href="{{ route('supply.suppliers.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('supply.suppliers.*')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Suppliers
                            </a>

                            <a href="{{ route('supply.items.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('supply.items.*')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                PPE Items
                            </a>

                            @if (Route::has('supply.call-offs.index'))
                                <a href="{{ route('supply.call-offs.index') }}"
                                    class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                        {{ request()->routeIs('supply.call-offs.*')
                                            ? 'bg-[#339DCB] text-white shadow'
                                            : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                    Call-Off Approvals
                                </a>
                            @endif

                        </div>

                        {{-- TSSD Unit --}}
                    @elseif($user?->isTssd())
                        <div class="space-y-2">

                            <a href="{{ route('tssd.dashboard') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('tssd.dashboard')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Dashboard
                            </a>

                            <a href="{{ route('tssd.distributions.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('tssd.distributions.*')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Provincial Distributions
                            </a>

                            <a href="{{ route('tssd.call-offs.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('tssd.call-offs.*')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Call-Off Management
                            </a>

                            @if (Route::has('tssd.inventory-monitoring.index'))
                                <a href="{{ route('tssd.inventory-monitoring.index') }}"
                                    class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                        {{ request()->routeIs('tssd.inventory-monitoring.*')
                                            ? 'bg-[#339DCB] text-white shadow'
                                            : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                    PPE Inventory & Transactions
                                </a>
                            @endif
                            <a href="{{ route('tssd.call-off-letters.index') }}"
                                class="group flex items-center gap-3 rounded-xl px-4 py-3
           text-sm font-semibold transition
           {{ request()->routeIs('tssd.call-off-letters.*')
               ? 'bg-[#339DCB] text-white shadow-sm'
               : 'text-slate-700 hover:bg-[#F7FBFD] hover:text-[#247BA0]' }}">

                                {{-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" class="h-5 w-5">

                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6 2.75h8.5L19 7.25v14H6z" />

                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14 2.75v4.5h5M9 12h6M9 16h6" />

                                </svg> --}}

                                <span>
                                    Call-Off Letters
                                </span>

                            </a>

                            @if (Route::has('tssd.users.index'))
                                <a href="{{ route('tssd.users.index') }}"
                                    class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
            {{ request()->routeIs('tssd.users.*')
                ? 'bg-[#339DCB] text-white shadow'
                : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                    User Management
                                </a>
                            @endif


                        </div>

                        {{-- Provincial Office --}}
                    @elseif($user?->isProvincial())
                        <div class="space-y-2">

                            <a href="{{ route('provincial.dashboard') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                {{ request()->routeIs('provincial.dashboard')
                    ? 'bg-[#339DCB] text-white shadow'
                    : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Dashboard
                            </a>

                            @if (Route::has('provincial.receiving.index'))
                                <a href="{{ route('provincial.receiving.index') }}"
                                    class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                    {{ request()->routeIs('provincial.receiving.index') ||
                    request()->routeIs('provincial.receiving.create') ||
                    request()->routeIs('provincial.receiving.show')
                        ? 'bg-[#339DCB] text-white shadow'
                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                    Approved Call-Offs
                                </a>
                            @endif

                            @if (Route::has('provincial.receiving.history'))
                                <a href="{{ route('provincial.receiving.history') }}"
                                    class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                    {{ request()->routeIs('provincial.receiving.history')
                        ? 'bg-[#339DCB] text-white shadow'
                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                    Receiving History
                                </a>
                            @endif

                            @if (Route::has('provincial.current-inventory.index'))
                                <a href="{{ route('provincial.current-inventory.index') }}"
                                    class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                    {{ request()->routeIs('provincial.current-inventory.*')
                        ? 'bg-[#339DCB] text-white shadow'
                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                    Current Inventory
                                </a>
                            @endif

                            @if (Route::has('provincial.inventory-ledger.index'))
                                <a href="{{ route('provincial.inventory-ledger.index') }}"
                                    class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                    {{ request()->routeIs('provincial.inventory-ledger.*')
                        ? 'bg-[#339DCB] text-white shadow'
                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                    Inventory Ledger
                                </a>
                            @endif

                            @if (Route::has('provincial.project-designations.index'))
                                <a href="{{ route('provincial.project-designations.index') }}"
                                    class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                    {{ request()->routeIs('provincial.project-designations.*')
                        ? 'bg-[#339DCB] text-white shadow'
                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                    Project PPE Designations
                                </a>
                            @endif
                            <a href="{{ route('provincial.call-off-inventory.index') }}"
                                class="{{ request()->routeIs('provincial.call-off-inventory.*')
                                    ? 'bg-[#339DCB] text-white'
                                    : 'text-slate-700 hover:bg-slate-100' }} flex items-center rounded-xl px-4 py-3
       text-sm font-semibold transition">
                                Per Call-Off Inventory
                            </a>

                        </div>
                    @elseif($user?->isAccounting())
                        <div class="space-y-2">

                            <a href="{{ route('accounting.dashboard') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('accounting.dashboard')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Dashboard
                            </a>

                            <p class="px-4 pt-4 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                Inventory Summaries
                            </p>

                            <a href="{{ route('accounting.supply-inventory.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('accounting.supply-inventory.*')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Supply Inventory
                            </a>

                            <a href="{{ route('accounting.provincial-inventory.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('accounting.provincial-inventory.*')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Provincial Inventory
                            </a>

                            <a href="{{ route('accounting.distributions.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('accounting.distributions.*')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                TSSD Distributions
                            </a>

                            <a href="{{ route('accounting.delivery-receipts.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('accounting.delivery-receipts.*')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Delivery Receipts
                            </a>

                            <a href="{{ route('accounting.project-designations.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('accounting.project-designations.*')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Project Designations
                            </a>

                            <p class="px-4 pt-4 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                Detailed Records
                            </p>

                            <a href="{{ route('accounting.inventory-ledger.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('accounting.inventory-ledger.*')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Inventory Ledger
                            </a>

                        </div>
                    @elseif($user?->isAccounting())
                        <div class="space-y-2">

                            <a href="{{ route('accounting.dashboard') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('accounting.dashboard')
                                        ? 'bg-[#339DCB] text-white shadow'
                                        : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                Dashboard
                            </a>

                            @if (Route::has('accounting.inventory-ledger.index'))
                                <a href="{{ route('accounting.inventory-ledger.index') }}"
                                    class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                        {{ request()->routeIs('accounting.inventory-ledger.*')
                                            ? 'bg-[#339DCB] text-white shadow'
                                            : 'text-[#36566E] hover:bg-[#B7D6E6]/35 hover:text-[#143A52]' }}">
                                    PPE Inventory & Transactions
                                </a>
                            @endif

                        </div>
                    @else
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            No authorized navigation is available for this account.
                        </div>

                    @endif


                </nav>

                {{-- Logout --}}
                <div class="border-t border-[#D8EAF3] p-5">

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit"
                            class="w-full rounded-xl bg-gradient-to-tr from-sky-700 via-sky-600 to-cyan-500 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:from-sky-600 hover:via-sky-500 hover:to-cyan-400">
                            Log Out
                        </button>

                    </form>

                </div>

            </div>

        </aside>

        {{-- Main area --}}
        <div class="min-h-screen lg:pl-72">

            {{-- Top header --}}
            <header class="sticky top-0 z-30 border-b border-[#D8EAF3] bg-white/95 shadow-sm backdrop-blur">

                <div class="flex min-h-20 items-center justify-between px-4 sm:px-6 lg:px-8">

                    <div class="flex items-center gap-4">

                        <button type="button"
                            class="rounded-lg border border-[#B7D6E6] bg-white px-4 py-2 font-medium text-[#36566E] shadow-sm lg:hidden"
                            @click="sidebarOpen = true">
                            Menu
                        </button>

                        <div>

                            <p class="text-xs font-semibold uppercase tracking-wider text-[#5F7D91]">
                                {{ $roleName }}
                            </p>

                            <h1 class="text-lg font-bold text-[#143A52]">
                                {{ $title }}
                            </h1>

                        </div>

                    </div>

                    <div class="hidden text-right sm:block">

                        <p class="text-sm font-semibold text-[#143A52]">
                            {{ $user?->name }}
                        </p>

                        <p id="live-clock" class="text-xs text-[#5F7D91]">
                            --:--:--
                        </p>

                        <p id="live-date" class="text-xs text-[#5F7D91]">
                            --/--/----
                        </p>

                    </div>

                </div>

            </header>

            {{-- Page content --}}
            <main class="p-4 sm:p-6 lg:p-8">

                {{ $slot }}
                @stack('scripts')

            </main>

        </div>

    </div>

    <script>
        function updateClockAndDate() {
            const now = new Date();

            const clockElement = document.getElementById('live-clock');
            const dateElement = document.getElementById('live-date');

            if (!clockElement || !dateElement) {
                return;
            }

            clockElement.textContent = new Intl.DateTimeFormat(
                'en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true,
                }
            ).format(now);

            dateElement.textContent = new Intl.DateTimeFormat(
                'en-US', {
                    month: '2-digit',
                    day: '2-digit',
                    year: 'numeric',
                }
            ).format(now);
        }

        updateClockAndDate();

        window.setInterval(
            updateClockAndDate,
            1000
        );
    </script>

</body>

</html>
