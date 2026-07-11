<x-po_dashboard_layout title="User Management">

    <div class="space-y-6">

        <div>

            <h1 class="text-3xl font-bold text-gray-900">
                User Management
            </h1>

            <p class="mt-1 text-sm text-gray-600">
                View and edit system user accounts.
            </p>

        </div>

        @if(session('success'))

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>

        @endif

        <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-5 py-4 text-yellow-900">

            User deletion is intentionally disabled to preserve purchase,
            distribution, receiving, project, and audit records.

        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

            <form
                action="{{ route('tssd.users.index') }}"
                method="GET"
                class="grid grid-cols-1 gap-4 lg:grid-cols-5"
            >

                <div class="lg:col-span-2">

                    <label
                        for="search"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Search
                    </label>

                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Name, username, email, role..."
                        class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                    >

                </div>

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
                        class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                    >

                        <option value="">
                            All roles
                        </option>

                        @foreach($roles as $role)

                            <option
                                value="{{ $role->id }}"
                                @selected(
                                    (int) $roleId
                                    === (int) $role->id
                                )
                            >
                                {{ $role->name }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div>

                    <label
                        for="province_id"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Province
                    </label>

                    <select
                        id="province_id"
                        name="province_id"
                        class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                    >

                        <option value="">
                            All provinces
                        </option>

                        @foreach($provinces as $province)

                            <option
                                value="{{ $province->id }}"
                                @selected(
                                    (int) $provinceId
                                    === (int) $province->id
                                )
                            >
                                {{ $province->name }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="flex items-end gap-2">

                    <button
                        type="submit"
                        class="flex-1 rounded-xl bg-red-900 px-5 py-3 font-semibold text-white hover:bg-red-800"
                    >
                        Apply
                    </button>

                    @if($search || $roleId || $provinceId)

                        <a
                            href="{{ route('tssd.users.index') }}"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Reset
                        </a>

                    @endif

                </div>

            </form>

        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-red-900 px-6 py-5">

                <h2 class="text-xl font-semibold text-white">
                    System Users
                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-100">

                        <tr class="text-xs font-semibold uppercase tracking-wide text-gray-700">

                            <th class="px-5 py-4 text-left">No.</th>
                            <th class="px-5 py-4 text-left">User</th>
                            <th class="px-5 py-4 text-left">Username</th>
                            <th class="px-5 py-4 text-left">Role</th>
                            <th class="px-5 py-4 text-left">Province</th>
                            <th class="px-5 py-4 text-center">Verified</th>
                            <th class="px-5 py-4 text-center">Actions</th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($users as $managedUser)

                            <tr class="hover:bg-gray-50">

                                <td class="px-5 py-4 text-sm text-gray-600">
                                    {{ $users->firstItem() + $loop->index }}
                                </td>

                                <td class="min-w-64 px-5 py-4">

                                    <p class="font-semibold text-gray-900">
                                        {{ $managedUser->name }}

                                        @if($managedUser->is(auth()->user()))

                                            <span class="ml-2 rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-800">
                                                You
                                            </span>

                                        @endif
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $managedUser->email }}
                                    </p>

                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $managedUser->username }}
                                </td>

                                <td class="px-5 py-4">

                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800">
                                        {{ $managedUser->role?->name ?? 'No role' }}
                                    </span>

                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $managedUser->province?->name ?? '—' }}
                                </td>

                                <td class="px-5 py-4 text-center">

                                    @if($managedUser->email_verified_at)

                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                            Verified
                                        </span>

                                    @else

                                        <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                            Unverified
                                        </span>

                                    @endif

                                </td>

                                <td class="px-5 py-4">

                                    <div class="flex justify-center gap-2">

                                        <a
                                            href="{{ route('tssd.users.show', $managedUser) }}"
                                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                                        >
                                            View
                                        </a>

                                        <a
                                            href="{{ route('tssd.users.edit', $managedUser) }}"
                                            class="rounded-lg bg-yellow-500 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-600"
                                        >
                                            Edit
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="7"
                                    class="px-6 py-14 text-center text-gray-500"
                                >
                                    No users matched the selected filters.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($users->hasPages())

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $users->links() }}
                </div>

            @endif

        </div>

    </div>

</x-po_dashboard_layout>