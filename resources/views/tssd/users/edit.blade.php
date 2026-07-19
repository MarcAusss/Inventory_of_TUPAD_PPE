<x-po_dashboard_layout title="Edit User">

    @php
        $selectedRoleId = (int) old('role_id', $user->role_id);
        $provincialRole = $roles->firstWhere('name', 'Provincial Office');
        $provincialRoleId = $provincialRole?->id;
        $editingSelf = $user->is(auth()->user());
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">

        {{-- Page Header --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#075985] via-[#0284C7] to-[#38BDF8]"></div>
            <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-[#7DD3FC]/20 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="rounded-full bg-[#7DD3FC]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#075985] ring-1 ring-inset ring-[#7DD3FC]">
                            User Management
                        </span>
                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                            Edit Account
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Edit User</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        Update <span class="font-bold text-slate-800">{{ $user->name }}</span>’s login details,
                        access role, and account assignment.
                    </p>
                </div>

                <a href="{{ route('tssd.users.show', $user) }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-[#7DD3FC] hover:bg-sky-50 hover:text-[#075985] focus:outline-none focus:ring-2 focus:ring-[#0284C7] focus:ring-offset-2 sm:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                    </svg>
                    Back to User
                </a>
            </div>
        </section>

        {{-- Validation Summary --}}
        @if ($errors->any())
            <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-800"
                role="alert">
                <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-rose-100">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v4m0 4h.01M10.3 3.7 2.6 17a2 2 0 0 0 1.73 3h15.34a2 2 0 0 0 1.73-3L13.7 3.7a2 2 0 0 0-3.4 0Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold">Please correct the following:</h2>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if ($editingSelf)
            <div class="flex items-start gap-3 rounded-2xl border border-sky-200 bg-sky-50 px-5 py-4 text-sm text-sky-900">
                <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-sky-100">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 16.5v-4m0-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold">You are editing your own account</p>
                    <p class="mt-0.5 text-sky-800">Your TSSD role is locked while you are signed in.</p>
                </div>
            </div>
        @endif

        <form action="{{ route('tssd.users.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Account Details --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5 sm:px-7">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-[#0284C7] ring-1 ring-inset ring-sky-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0A17.9 17.9 0 0 1 12 21.75c-2.68 0-5.22-.59-7.5-1.65Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Account Details</h2>
                        <p class="mt-0.5 text-sm text-slate-500">Basic information used to identify and contact this user.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 p-6 sm:p-7 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="name" class="mb-2 block text-sm font-bold text-slate-700">
                            Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                            maxlength="255"
                            class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-[#0284C7] focus:ring-[#0284C7]">
                        @error('name')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="username" class="mb-2 block text-sm font-bold text-slate-700">
                            Username <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="username" name="username"
                            value="{{ old('username', $user->username) }}" required maxlength="100" autocomplete="username"
                            class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0284C7] focus:ring-[#0284C7]">
                        <p class="mt-2 text-xs text-slate-500">Use letters, numbers, dashes, and underscores only.</p>
                        @error('username')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-bold text-slate-700">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                            maxlength="255" autocomplete="email"
                            class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0284C7] focus:ring-[#0284C7]">
                        @error('email')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Role and Province --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5 sm:px-7">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-[#0284C7] ring-1 ring-inset ring-sky-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M12 3l7.5 3v5.25c0 4.63-3.2 8.77-7.5 9.75-4.3-.98-7.5-5.12-7.5-9.75V6L12 3Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Role and Province</h2>
                        <p class="mt-0.5 text-sm text-slate-500">Control system access and provincial data assignment.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 p-6 sm:p-7 md:grid-cols-2">
                    <div>
                        <label for="role_id" class="mb-2 block text-sm font-bold text-slate-700">
                            Role <span class="text-rose-500">*</span>
                        </label>
                        <select id="role_id" name="role_id" required @disabled($editingSelf)
                            class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0284C7] focus:ring-[#0284C7] disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected($selectedRoleId === (int) $role->id)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>

                        @if ($editingSelf)
                            <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                        @endif

                        @error('role_id')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="province-field"
                        class="{{ $selectedRoleId === (int) $provincialRoleId ? '' : 'hidden' }}">
                        <label for="province_id" class="mb-2 block text-sm font-bold text-slate-700">
                            Provincial Office <span class="text-rose-500">*</span>
                        </label>
                        <select id="province_id" name="province_id"
                            class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0284C7] focus:ring-[#0284C7]">
                            <option value="">Select province</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}"
                                    @selected((int) old('province_id', $user->province_id) === (int) $province->id)>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Limits the account to records assigned to this province.</p>
                        @error('province_id')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($editingSelf)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600 md:col-span-2">
                            <span class="font-bold text-slate-800">Role protection:</span> Your TSSD role remains locked to
                            prevent accidental removal of your User Management access.
                        </div>
                    @endif
                </div>
            </section>

            {{-- Password Reset --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5 sm:px-7">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-[#0284C7] ring-1 ring-inset ring-sky-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 0h10.5A1.75 1.75 0 0 1 19 12.25v7A1.75 1.75 0 0 1 17.25 21H6.75A1.75 1.75 0 0 1 5 19.25v-7a1.75 1.75 0 0 1 1.75-1.75Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Password Reset</h2>
                        <p class="mt-0.5 text-sm text-slate-500">Leave both fields blank to keep the current password.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 p-6 sm:p-7 md:grid-cols-2">
                    <div>
                        <label for="password" class="mb-2 block text-sm font-bold text-slate-700">New Password</label>
                        <input type="password" id="password" name="password" autocomplete="new-password" minlength="8"
                            class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0284C7] focus:ring-[#0284C7]">
                        <p class="mt-2 text-xs text-slate-500">Use at least 8 characters.</p>
                        @error('password')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-bold text-slate-700">
                            Confirm New Password
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            autocomplete="new-password" minlength="8"
                            class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0284C7] focus:ring-[#0284C7]">
                    </div>
                </div>
            </section>

            {{-- Form Actions --}}
            <div
                class="flex flex-col-reverse gap-3 rounded-3xl border border-slate-200 bg-white px-6 py-5 shadow-sm sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('tssd.users.show', $user) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#0EA5E9] px-7 py-3 text-sm font-bold text-white shadow-sm shadow-sky-600/20 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#0284C7] focus:ring-offset-2">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M5 12.5 9.5 17 19 7.5" />
                    </svg>
                    Save User Changes
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('role_id');
            const provinceField = document.getElementById('province-field');
            const provinceSelect = document.getElementById('province_id');
            const provincialRoleId = @json($provincialRoleId);

            if (!roleSelect || !provinceField || !provinceSelect) {
                return;
            }

            function updateProvinceField() {
                const isProvincial = Number(roleSelect.value) === Number(provincialRoleId);

                provinceField.classList.toggle('hidden', !isProvincial);
                provinceSelect.required = isProvincial;
                provinceSelect.disabled = !isProvincial;

                if (!isProvincial) {
                    provinceSelect.value = '';
                }
            }

            roleSelect.addEventListener('change', updateProvinceField);
            updateProvinceField();
        });
    </script>

</x-po_dashboard_layout>