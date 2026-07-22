<x-guest-layout>
    <div class="relative flex min-h-screen w-full items-center justify-center overflow-hidden bg-black px-4 py-6 sm:px-6 lg:px-8">

        {{-- Animated floating background --}}
        <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden">

            {{-- Main background --}}
            <img
                src="{{ url('images/mainbg.png') }}"
                alt=""
                class="floating-light-main absolute -left-[5%] -top-[5%] h-[110%] w-[110%] max-w-none object-cover"
            >

            {{-- Blurred moving light layer --}}
            <img
                src="{{ url('images/mainbg.png') }}"
                alt=""
                class="floating-light-glow absolute -left-[10%] -top-[10%] h-[120%] w-[120%] max-w-none object-cover opacity-50 blur-[28px]"
            >

            {{-- Soft dark overlay for readability --}}
            <div class="absolute inset-0 bg-black/25"></div>
        </div>

        {{-- Main login container --}}
        <div
            class="relative z-10 flex min-h-[650px] w-full max-w-[1300px] items-center justify-center overflow-hidden rounded-2xl
                   bg-[linear-gradient(to_top_left,#F9F9F9_0%,#3C9AB4_30%,#3E68A6_20%,#3C9AB4_30%,#648AC3_60%,#3E68A6_100%)]
                   px-4 py-8 shadow-[0_35px_100px_rgba(0,0,0,0.45)]
                   sm:px-8 sm:py-10
                   md:min-h-[720px]
                   lg:min-h-[650px] lg:justify-end lg:px-16
                   xl:min-h-[700px] xl:px-24
                   2xl:min-h-[750px] 2xl:px-[200px]
                   3xl:min-h-[800px] 3xl:px-[300px]">

            {{-- Decorative container image --}}
            <img
                src="{{ url('images/containerbg.png') }}"
                alt=""
                class="pointer-events-none absolute inset-y-0 left-0 z-0 hidden h-full w-[43%] object-cover object-right lg:block xl:w-[38%] 2xl:left-[-22px] 2xl:w-[30%]"
            >

            {{-- Decorative mobile overlay --}}
            <div
                class="pointer-events-none absolute inset-0 z-0 bg-gradient-to-b from-white/10 via-transparent to-sky-950/20 lg:hidden">
            </div>

            {{-- Login card --}}
            <div
                class="relative z-10 w-full max-w-[520px] rounded-2xl border border-[#B7D6E6]
                       bg-white/95 px-5 py-6 shadow-2xl backdrop-blur-sm
                       sm:px-8 sm:py-8
                       md:px-12
                       lg:w-[52%] lg:max-w-[600px]
                       xl:px-14">

                {{-- Header --}}
                <div class="flex flex-col items-center gap-4 border-b border-gray-300 pb-5 pt-2 sm:gap-5 sm:pt-4">

                    <img
                        src="{{ asset('images/mainlogo.jpg') }}"
                        alt="TUPAD Logo"
                        class="h-20 w-auto object-contain sm:h-24 md:h-28"
                    >

                    <h1 class="text-center text-lg font-medium leading-snug text-gray-800 sm:text-xl">
                        Welcome to TUPAD Inventory System
                    </h1>
                </div>

                <h2 class="my-5 text-center text-xl font-semibold text-gray-800 sm:text-2xl">
                    Login
                </h2>

                {{-- Session Status --}}
                <x-auth-session-status
                    class="mb-4"
                    :status="session('status')"
                />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Email --}}
                    <div class="relative mt-5">
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder=" "
                            class="peer w-full border-x-0 border-t-0 border-b-2 border-gray-300
                                   bg-transparent px-0 pb-2 pt-6 text-sm text-gray-900 outline-none
                                   transition-colors duration-300
                                   focus:border-sky-600 focus:ring-0
                                   sm:text-base"
                        >

                        <label
                            for="email"
                            class="absolute left-0 top-5 origin-[0] -translate-y-4 scale-75 transform
                                   text-gray-500 duration-300
                                   peer-placeholder-shown:top-7
                                   peer-placeholder-shown:translate-y-0
                                   peer-placeholder-shown:scale-100
                                   peer-focus:top-5
                                   peer-focus:-translate-y-4
                                   peer-focus:scale-75
                                   peer-focus:text-sky-600">
                            User Account
                        </label>

                        <x-input-error
                            :messages="$errors->get('email')"
                            class="mt-2"
                        />
                    </div>

                    {{-- Password --}}
                    <div class="relative mt-6">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder=" "
                            class="peer w-full border-x-0 border-t-0 border-b-2 border-gray-300
                                   bg-transparent px-0 pb-2 pt-6 text-sm text-gray-900 outline-none
                                   transition-colors duration-300
                                   focus:border-sky-600 focus:ring-0
                                   sm:text-base"
                        >

                        <label
                            for="password"
                            class="absolute left-0 top-5 origin-[0] -translate-y-4 scale-75 transform
                                   text-gray-500 duration-300
                                   peer-placeholder-shown:top-7
                                   peer-placeholder-shown:translate-y-0
                                   peer-placeholder-shown:scale-100
                                   peer-focus:top-5
                                   peer-focus:-translate-y-4
                                   peer-focus:scale-75
                                   peer-focus:text-sky-600">
                            Password
                        </label>

                        <x-input-error
                            :messages="$errors->get('password')"
                            class="mt-2"
                        />
                    </div>

                    {{-- Login Button --}}
                    <div class="mt-8">
                        <button
                            type="submit"
                            class="w-full cursor-pointer rounded-xl bg-sky-700 px-6 py-3
                                   text-sm font-semibold text-white shadow-lg shadow-sky-700/20
                                   transition duration-300
                                   hover:-translate-y-0.5 hover:bg-sky-600
                                   focus:outline-none focus:ring-4 focus:ring-sky-300
                                   active:translate-y-0
                                   sm:text-base">
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>