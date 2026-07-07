<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>

    <!-- Add your Tailwind CSS or Bootstrap compiler links here -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans">
    <aside class="fixed left-0 top-0 w-75 h-full bg-[#F7F7F7]"> 
        <div class="flex flex-col justify-between h-full py-10">
            <div class="">
                <div class="flex mx-10 ">
                    <img src="{{ url('images/Primary Logo _ Full Color (3).png')}}" alt="" class="w-16 h-relative">
                    <img src="{{ url('images/Primary Logo _ Full Color (3) - Copy.png')}}" alt=""
                        class="w-relative h-16">
                </div>
                <div class="mt-10">
                    <div class="px-16 mb-5">
                        <h1 class="">MENU</h1>
                    </div>
                    <a href="{{route('dashboard')}}" class="flex gap-2 px-20 text-xl">
                        <i class=""></i>
                        Dashboard
                    </a>
                    <!-- <a href="{{ Route('supply.suppliers.index')}}" class="flex mt-5 gap-2 px-20 text-xl">
                        <i class=""></i>
                        Suppliers
                    </a>
                    <a href="{{ Route('supply.purchase-orders.index')}}" class="flex mt-5 gap-2 px-20 text-xl">
                        <i class=""></i>
                        Purchase Orders
                    </a> -->
                    
                </div>
            </div>
            <div class="flex items-center justify-center w-full">
                {{-- <a href="{{ route('auth.logout')}}">Logout</a> --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf 
                    <div
                        class="bg-[linear-gradient(to_top_right,#000000_5%,#EE1C09_60%)] text-white py-2 px-10 rounded-xl">
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link> 
                    </div>
                </form>
            </div>
        </div> 
    </aside>

    <div class="ml-[330px] ">
        <header class="w-full h-22 mt-2 rounded-xl bg-[#F7F7F7] flex justify-between px-7 py-2">
            <div class="flex flex-col h-full justify-center">
                <div class="text-[16px]" id="live-clock">--:--:--</div>
                <div class="text-[16px]" id="live-date">--/--/--</div>
            </div>
        </header>
        <main class="w-full min-h-175 mt-2 rounded-xl bg-[#F7F7F7] px-7 py-3">
            @yield('dashboard-accountant')
        </main>
    </div>

    <footer>
        
    </footer>

    <script>
        function updateClockAndDate() {
            const now = new Date();

            // --- TIME CALCULATION (12-Hour Format) ---
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            hours = hours % 12;
            hours = hours ? hours : 12; // Convert 0 to 12
            const formattedHours = String(hours).padStart(2, '0'); // Keeps two digits for alignment

            // --- DATE CALCULATION (MM/DD/YY) ---
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const year = String(now.getFullYear()).slice(0); // Gets last two digits of the year

            // --- OUTPUT ---
            document.getElementById('live-clock').textContent = `${formattedHours}:${minutes}:${seconds}`;
            document.getElementById('live-date').textContent = `${month}/${day}/${year}`;
        }

        // Run instantly, then refresh every second
        updateClockAndDate();
        setInterval(updateClockAndDate, 1000);
    </script>
</body>

</html>