<x-guest-layout>

    <div class="relative flex h-screen w-screen items-center justify-center overflow-hidden bg-black">

        {{-- Animated floating background --}}
        <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden">

            {{-- Main background --}}
            <img src="{{ url('images/mainbg.png') }}" alt=""
                class="floating-light-main absolute left-[-5%] top-[-5%] h-[110%] w-[110%] max-w-none object-cover">

            {{-- Blurred moving light layer --}}
            <img src="{{ url('images/mainbg.png') }}" alt=""
                class="floating-light-glow absolute left-[-10%] top-[-10%] h-[120%] w-[120%] max-w-none object-cover opacity-50 blur-[28px]">

            {{-- Soft dark overlay for readability --}}
            <div class="absolute inset-0 bg-black/15"></div>
        </div>

        {{-- Main login container --}}
        <div
            class="relative z-10 flex h-[900px] w-[1400px] items-center justify-between overflow-hidden rounded-xl
                   bg-[linear-gradient(to_top_left,#F9F9F9_0%,#3C9AB4_30%,#3E68A6_20%,#3C9AB4_30%,#648AC3_60%,#3E68A6_100%)]
                   px-[200px] shadow-[0_35px_100px_rgba(0,0,0,0.45)]">

            <img src="{{ url('images/containerbg.png') }}" alt=""
                class="h-full w-[30%] absolute top-0 left-[-22px] z-0">

            <div></div>

            <div
                class="relative z-10 w-1/2 rounded-2xl border-2 border-[#B7D6E6]
                       bg-white p-5 px-14 shadow-2xl">
                <!-- Header -->
                <div class="flex flex-col items-center gap-5 border-b border-gray-300 pb-5 pt-9">

                    <img src="{{ asset('images/mainlogo.jpg') }}" alt="TUPAD Logo" class="h-28">

                    <h1 class="text-center text-xl">
                        Welcome to TUPAD Inventory System
                    </h1>
                </div>

                <h2 class="my-5 text-center text-xl font-semibold">
                    Login
                </h2>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="relative mt-5">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autofocus placeholder=" "
                            class="peer w-full border-x-0 border-t-0 border-b-2 border-gray-300 bg-transparent px-0 pt-6 pb-2 text-gray-900 outline-none focus:border-blue-600 focus:ring-0" />

                        <label for="email"
                            class="absolute left-0 top-5 origin-[0] -translate-y-4 scale-75 transform text-gray-500 duration-300
               peer-placeholder-shown:top-7
               peer-placeholder-shown:translate-y-0
               peer-placeholder-shown:scale-100
               peer-focus:top-5
               peer-focus:-translate-y-4
               peer-focus:scale-75
               peer-focus:text-blue-600">
                            User Account
                        </label>

                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="relative mt-6">
                        <input id="password" type="password" name="password" required placeholder=" "
                            class="peer w-full border-x-0 border-t-0 border-b-2 border-gray-300 bg-transparent px-0 pt-6 pb-2 text-gray-900 outline-none focus:border-blue-600 focus:ring-0" />

                        <label for="password"
                            class="absolute left-0 top-5 origin-[0] -translate-y-4 scale-75 transform text-gray-500 duration-300
               peer-placeholder-shown:top-7
               peer-placeholder-shown:translate-y-0
               peer-placeholder-shown:scale-100
               peer-focus:top-5
               peer-focus:-translate-y-4
               peer-focus:scale-75
               peer-focus:text-blue-600">
                            Password
                        </label>

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Login Button -->
                    <div class="mt-6 text-center">
                        <button type="submit"
                            class="cursor-pointer rounded-xl bg-sky-700 px-10 py-2 text-white
                                   transition duration-300 hover:bg-sky-600">
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-guest-layout>
