@props([
    'title' => 'Dashboard',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        {{ $title }} | {{ config('app.name', 'TUPAD Inventory System') }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans text-gray-900">

    @php
        $user = auth()->user();

        $roleName = $user?->role?->name ?? 'Unknown Role';

        $dashboardRoute = match (true) {
            $user?->isSupply() =>
                'supply.dashboard',

            $user?->isTssd() =>
                'tssd.dashboard',

            $user?->isProvincial() =>
                'provincial.dashboard',

            $user?->isAccounting() =>
                'accounting.dashboard',

            default =>
                'dashboard',
        };
    @endphp

    <div
        x-data="{ sidebarOpen: false }"
        class="min-h-screen"
    >

        {{-- Mobile overlay --}}
        <div
            x-cloak
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 transform flex-col bg-[#F7F7F7] shadow-xl transition-transform duration-300 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >

            <div class="flex h-full flex-col">

                {{-- Logos --}}
                <div class="border-b border-gray-200 px-7 py-6">

                    <a
                        href="{{ route($dashboardRoute) }}"
                        class="flex items-center justify-center gap-3"
                    >

                        <img
                            src="{{ asset('images/Primary Logo _ Full Color (3).png') }}"
                            alt="Primary logo"
                            class="h-14 w-auto object-contain"
                        >

                        <img
                            src="{{ asset('images/Primary Logo _ Full Color (3) - Copy.png') }}"
                            alt="Secondary logo"
                            class="h-14 w-auto object-contain"
                        >

                    </a>

                    <div class="mt-4 text-center">

                        <p class="font-bold text-gray-900">
                            TUPAD PPE Inventory
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $roleName }}
                        </p>

                        @if($user?->isProvincial())

                            <p class="mt-1 text-xs font-semibold text-red-900">
                                {{ $user->provinceName() ?? 'No province assigned' }}
                            </p>

                        @endif

                    </div>

                </div>

                {{-- Navigation --}}
                <nav class="flex-1 overflow-y-auto px-4 py-6">

                    <p class="mb-3 px-4 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Menu
                    </p>

                    {{-- Supply Unit --}}
                    @if($user?->isSupply())

                        <div class="space-y-2">

                            <a
                                href="{{ route('supply.dashboard') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('supply.dashboard')
                                        ? 'bg-red-900 text-white shadow'
                                        : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
                            >
                                Dashboard
                            </a>

                            <a
                                href="{{ route('supply.purchase-orders.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('supply.purchase-orders.*')
                                        ? 'bg-red-900 text-white shadow'
                                        : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
                            >
                                Purchase Orders
                            </a>

                            <a
                                href="{{ route('supply.suppliers.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('supply.suppliers.*')
                                        ? 'bg-red-900 text-white shadow'
                                        : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
                            >
                                Suppliers
                            </a>

                            <a
                                href="{{ route('supply.items.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('supply.items.*')
                                        ? 'bg-red-900 text-white shadow'
                                        : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
                            >
                                PPE Items
                            </a>

                            @if(Route::has('supply.call-offs.index'))

                                <a
                                    href="{{ route('supply.call-offs.index') }}"
                                    class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                        {{ request()->routeIs('supply.call-offs.*')
                                            ? 'bg-red-900 text-white shadow'
                                            : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
                                >
                                    Call-Off Approvals
                                </a>

                            @endif

                        </div>

                    {{-- TSSD Unit --}}
                    @elseif($user?->isTssd())

                        <div class="space-y-2">

                            <a
                                href="{{ route('tssd.dashboard') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('tssd.dashboard')
                                        ? 'bg-red-900 text-white shadow'
                                        : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
                            >
                                Dashboard
                            </a>

                            <a
                                href="{{ route('tssd.distributions.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('tssd.distributions.*')
                                        ? 'bg-red-900 text-white shadow'
                                        : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
                            >
                                Provincial Distributions
                            </a>

                            <a
                                href="{{ route('tssd.call-offs.index') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('tssd.call-offs.*')
                                        ? 'bg-red-900 text-white shadow'
                                        : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
                            >
                                Call-Off Management
                            </a>

                            @if(Route::has('tssd.users.index'))

    <a
        href="{{ route('tssd.users.index') }}"
        class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
            {{ request()->routeIs('tssd.users.*')
                ? 'bg-red-900 text-white shadow'
                : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
    >
        User Management
    </a>

@endif

                            @if(Route::has('tssd.pdf-templates.index'))

                                <a
                                    href="{{ route('tssd.pdf-templates.index') }}"
                                    class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                        {{ request()->routeIs('tssd.pdf-templates.*')
                                            ? 'bg-red-900 text-white shadow'
                                            : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
                                >
                                    PDF Print Templates
                                </a>

                            @endif

                        </div>

                    {{-- Provincial Office --}}
                    @elseif($user?->isProvincial())

    <div class="space-y-2">

        <a
            href="{{ route('provincial.dashboard') }}"
            class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                {{ request()->routeIs('provincial.dashboard')
                    ? 'bg-red-900 text-white shadow'
                    : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
        >
            Dashboard
        </a>

        @if(Route::has('provincial.receiving.index'))

            <a
                href="{{ route('provincial.receiving.index') }}"
                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                    {{ request()->routeIs('provincial.receiving.index')
                        || request()->routeIs('provincial.receiving.create')
                        || request()->routeIs('provincial.receiving.show')
                            ? 'bg-red-900 text-white shadow'
                            : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
            >
                Approved Call-Offs
            </a>

        @endif

        @if(Route::has('provincial.receiving.history'))

            <a
                href="{{ route('provincial.receiving.history') }}"
                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                    {{ request()->routeIs('provincial.receiving.history')
                        ? 'bg-red-900 text-white shadow'
                        : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
            >
                Receiving History
            </a>

        @endif

        @if(Route::has('provincial.current-inventory.index'))

            <a
                href="{{ route('provincial.current-inventory.index') }}"
                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                    {{ request()->routeIs('provincial.current-inventory.*')
                        ? 'bg-red-900 text-white shadow'
                        : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
            >
                Current Inventory
            </a>

        @endif

        @if(Route::has('provincial.inventory-ledger.index'))

            <a
                href="{{ route('provincial.inventory-ledger.index') }}"
                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                    {{ request()->routeIs('provincial.inventory-ledger.*')
                        ? 'bg-red-900 text-white shadow'
                        : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
            >
                Inventory Ledger
            </a>

        @endif

        @if(Route::has('provincial.project-designations.index'))

            <a
                href="{{ route('provincial.project-designations.index') }}"
                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                    {{ request()->routeIs('provincial.project-designations.*')
                        ? 'bg-red-900 text-white shadow'
                        : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
            >
                Project PPE Designations
            </a>


        @endif
        <a
    href="{{ route(
        'provincial.call-off-inventory.index'
    ) }}"
    class="{{
        request()->routeIs(
            'provincial.call-off-inventory.*'
        )
            ? 'bg-red-900 text-white'
            : 'text-slate-700 hover:bg-slate-100'
    }} flex items-center rounded-xl px-4 py-3
       text-sm font-semibold transition"
>
    Per Call-Off Inventory
</a>

    </div>@elseif($user?->isAccounting())

                        <div class="space-y-2">

                            <a
                                href="{{ route('accounting.dashboard') }}"
                                class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                    {{ request()->routeIs('accounting.dashboard')
                                        ? 'bg-red-900 text-white shadow'
                                        : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
                            >
                                Dashboard
                            </a>

                            @if(Route::has('accounting.inventory-ledger.index'))

                                <a
                                    href="{{ route('accounting.inventory-ledger.index') }}"
                                    class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                        {{ request()->routeIs('accounting.inventory-ledger.*')
                                            ? 'bg-red-900 text-white shadow'
                                            : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
                                >
                                    Provincial Inventory
                                </a>

                            @endif

                        </div>

                    @else

                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            No authorized navigation is available for this account.
                        </div>

                    @endif

                    {{-- Shared account navigation --}}
                    <div class="mt-7 border-t border-gray-200 pt-6">

                        <p class="mb-3 px-4 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Account
                        </p>

                        <a
                            href="{{ route('profile.edit') }}"
                            class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition
                                {{ request()->routeIs('profile.*')
                                    ? 'bg-red-900 text-white shadow'
                                    : 'text-gray-700 hover:bg-red-50 hover:text-red-900' }}"
                        >
                            Profile
                        </a>

                    </div>

                </nav>

                {{-- Logout --}}
                <div class="border-t border-gray-200 p-5">

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-gradient-to-tr from-black to-red-600 px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90"
                        >
                            Log Out
                        </button>

                    </form>

                </div>

            </div>

        </aside>

        {{-- Main area --}}
        <div class="min-h-screen lg:pl-72">

            {{-- Top header --}}
            <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/95 shadow-sm backdrop-blur">

                <div class="flex min-h-20 items-center justify-between px-4 sm:px-6 lg:px-8">

                    <div class="flex items-center gap-4">

                        <button
                            type="button"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 shadow-sm lg:hidden"
                            @click="sidebarOpen = true"
                        >
                            Menu
                        </button>

                        <div>

                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                                {{ $roleName }}
                            </p>

                            <h1 class="text-lg font-bold text-gray-900">
                                {{ $title }}
                            </h1>

                        </div>

                    </div>

                    <div class="hidden text-right sm:block">

                        <p class="text-sm font-semibold text-gray-900">
                            {{ $user?->name }}
                        </p>

                        <p
                            id="live-clock"
                            class="text-xs text-gray-500"
                        >
                            --:--:--
                        </p>

                        <p
                            id="live-date"
                            class="text-xs text-gray-500"
                        >
                            --/--/----
                        </p>

                    </div>

                </div>

            </header>

            {{-- Page content --}}
            <main class="p-4 sm:p-6 lg:p-8">

                @if(session('success'))

                    <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                        {{ session('success') }}
                    </div>

                @endif

                @if(session('error'))

                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
                        {{ session('error') }}
                    </div>

                @endif

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
                'en-US',
                {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true,
                }
            ).format(now);

            dateElement.textContent = new Intl.DateTimeFormat(
                'en-US',
                {
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