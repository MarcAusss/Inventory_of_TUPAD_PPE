<x-guest-layout>
    <div class="flex justify-center items-center min-h-screen bg-gray-100">
        <div class="flex w-[930px] h-[565px] rounded-xl shadow-[-12px_7px_20.6px_rgba(0,0,0,0.3)] overflow-hidden bg-white">

            <!-- Left Panel -->
            <div class="w-1/2 bg-[linear-gradient(to_top_right,#000000_5%,#EE1C09_60%)] flex items-center justify-center">
                <h1 class="text-center uppercase text-4xl text-white font-bold leading-relaxed">
                    TULONG <br>
                    panghanapbuhay <br>
                    sa ating <br>
                    disadvantaged <br>
                    workers <br>
                    (TUPAD)
                </h1>
            </div>

            <!-- Right Panel -->
            <div class="w-1/2 px-14 bg-white">

                <!-- Header -->
                <div class="flex gap-5 border-b border-gray-300 flex-col pt-9 pb-5 items-center">
                    <img
                        src="{{ asset('images/Primary Logo _ Full Color (3).png') }}"
                        alt="TUPAD Logo"
                        class="h-28"
                    >

                    <h1 class="text-xl text-center">
                        Welcome to TUPAD Inventory System
                    </h1>
                </div>

                <h2 class="text-center my-5 text-xl font-semibold">
                    Login
                </h2>

                <!-- Session Status -->
                <x-auth-session-status
                    class="mb-4"
                    :status="session('status')"
                />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="flex flex-col">
                        <label for="email">
                            User Account
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="p-1 w-full border-b-2 mt-3 outline-none border-black"
                            placeholder="User Account"
                        >

                        <x-input-error
                            :messages="$errors->get('email')"
                            class="mt-2"
                        />
                    </div>

                    <!-- Password -->
                    <div class="flex flex-col mt-5">
                        <label for="password">
                            Password
                        </label>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="p-1 w-full border-b-2 mt-3 outline-none border-black"
                            placeholder="Password"
                        >

                        <x-input-error
                            :messages="$errors->get('password')"
                            class="mt-2"
                        />
                    </div>


                    <!-- Forgot Password -->
                    @if (Route::has('password.request'))
                        <div class="mt-3 text-right">
                            <a
                                href="{{ route('password.request') }}"
                                class="text-sm text-red-700 hover:underline"
                            >
                                Forgot Password?
                            </a>
                        </div>
                    @endif

                    <!-- Login Button -->
                    <div class="text-center mt-6">
                        <button
                            type="submit"
                            class="cursor-pointer px-10 py-2 bg-red-900 rounded-xl text-white hover:bg-red-800 transition"
                        >
                            Login
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-guest-layout>