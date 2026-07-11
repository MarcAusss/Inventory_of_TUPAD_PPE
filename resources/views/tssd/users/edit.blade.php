<x-po_dashboard_layout title="Edit User">

    @php
        $selectedRoleId = (int) old(
            'role_id',
            $user->role_id
        );

        $provincialRole = $roles->firstWhere(
            'name',
            'Provincial Office'
        );

        $provincialRoleId = $provincialRole?->id;

        $editingSelf = $user->is(auth()->user());
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-900">
                    Edit User
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Update {{ $user->name }}’s login details and account assignment.
                </p>

            </div>

            <a
                href="{{ route('tssd.users.show', $user) }}"
                class="rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Back to User
            </a>

        </div>

        @if($errors->any())

            <div class="rounded-xl border border-red-200 bg-red-50 px-6 py-5">

                <h2 class="font-semibold text-red-800">
                    Please correct the following:
                </h2>

                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-red-700">

                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif

        @if($editingSelf)

            <div class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 text-blue-900">

                You are editing your own TSSD account. Your role cannot be changed
                while you are logged in.

            </div>

        @endif

        <form
            action="{{ route('tssd.users.update', $user) }}"
            method="POST"
            class="space-y-6"
        >

            @csrf
            @method('PUT')

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

                <div class="bg-red-900 px-7 py-5">

                    <h2 class="text-xl font-semibold text-white">
                        Account Details
                    </h2>

                    <p class="mt-1 text-sm text-red-100">
                        Update the user’s name, username, and email address.
                    </p>

                </div>

                <div class="grid grid-cols-1 gap-6 p-7 md:grid-cols-2">

                    <div class="md:col-span-2">

                        <label
                            for="name"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            maxlength="255"
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                        >

                        @error('name')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <div>

                        <label
                            for="username"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Username
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username', $user->username) }}"
                            required
                            maxlength="100"
                            autocomplete="username"
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                        >

                        <p class="mt-2 text-xs text-gray-500">
                            Letters, numbers, dashes, and underscores only.
                        </p>

                        @error('username')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <div>

                        <label
                            for="email"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            maxlength="255"
                            autocomplete="email"
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                        >

                        @error('email')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

                <div class="bg-gray-900 px-7 py-5">

                    <h2 class="text-xl font-semibold text-white">
                        Role and Province
                    </h2>

                    <p class="mt-1 text-sm text-gray-300">
                        Provincial Office accounts require a province assignment.
                    </p>

                </div>

                <div class="grid grid-cols-1 gap-6 p-7 md:grid-cols-2">

                    <div>

                        <label
                            for="role_id"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Role
                        </label>

                        <select
                            id="role_id"
                            name="role_id"
                            required
                            @disabled($editingSelf)
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900 disabled:cursor-not-allowed disabled:bg-gray-100"
                        >

                            @foreach($roles as $role)

                                <option
                                    value="{{ $role->id }}"
                                    @selected(
                                        $selectedRoleId === (int) $role->id
                                    )
                                >
                                    {{ $role->name }}
                                </option>

                            @endforeach

                        </select>

                        @if($editingSelf)

                            <input
                                type="hidden"
                                name="role_id"
                                value="{{ $user->role_id }}"
                            >

                        @endif

                        @error('role_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <div
                        id="province-field"
                        class="{{ $selectedRoleId === (int) $provincialRoleId
                            ? ''
                            : 'hidden' }}"
                    >

                        <label
                            for="province_id"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Provincial Office
                        </label>

                        <select
                            id="province_id"
                            name="province_id"
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                        >

                            <option value="">
                                Select province
                            </option>

                            @foreach($provinces as $province)

                                <option
                                    value="{{ $province->id }}"
                                    @selected(
                                        (int) old(
                                            'province_id',
                                            $user->province_id
                                        ) === (int) $province->id
                                    )
                                >
                                    {{ $province->name }}
                                </option>

                            @endforeach

                        </select>

                        <p class="mt-2 text-xs text-gray-500">
                            This limits the user to records assigned to the selected province.
                        </p>

                        @error('province_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    @if($editingSelf)

                        <div class="md:col-span-2 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">

                            Your TSSD role remains locked. This prevents accidental
                            removal of your access to User Management.

                        </div>

                    @endif

                </div>

            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

                <div class="bg-red-900 px-7 py-5">

                    <h2 class="text-xl font-semibold text-white">
                        Password Reset
                    </h2>

                    <p class="mt-1 text-sm text-red-100">
                        Leave both fields blank to keep the current password.
                    </p>

                </div>

                <div class="grid grid-cols-1 gap-6 p-7 md:grid-cols-2">

                    <div>

                        <label
                            for="password"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            New Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            minlength="8"
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                        >

                        @error('password')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <div>

                        <label
                            for="password_confirmation"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Confirm New Password
                        </label>

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            autocomplete="new-password"
                            minlength="8"
                            class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                        >

                    </div>

                </div>

            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <a
                    href="{{ route('tssd.users.show', $user) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-red-900 px-7 py-3 font-semibold text-white transition hover:bg-red-800"
                >
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

            const provincialRoleId = @json(
                $provincialRoleId
            );

            if (
                !roleSelect ||
                !provinceField ||
                !provinceSelect
            ) {
                return;
            }

            function updateProvinceField() {
                const isProvincial =
                    Number(roleSelect.value)
                    === Number(provincialRoleId);

                provinceField.classList.toggle(
                    'hidden',
                    !isProvincial
                );

                provinceSelect.required =
                    isProvincial;

                provinceSelect.disabled =
                    !isProvincial;

                if (!isProvincial) {
                    provinceSelect.value = '';
                }
            }

            roleSelect.addEventListener(
                'change',
                updateProvinceField
            );

            updateProvinceField();
        });
    </script>

</x-po_dashboard_layout>