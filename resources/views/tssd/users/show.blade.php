<x-po_dashboard_layout title="User Details">

    <div class="mx-auto max-w-5xl space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $user->name }}
                    </h1>

                    @if($user->is(auth()->user()))

                        <span class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800">
                            Your Account
                        </span>

                    @endif

                </div>

                <p class="mt-1 text-sm text-gray-600">
                    View the user’s account, role, and provincial assignment.
                </p>

            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('tssd.users.index') }}"
                    class="rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Back to Users
                </a>

                <a
                    href="{{ route('tssd.users.edit', $user) }}"
                    class="rounded-xl bg-yellow-500 px-5 py-3 font-semibold text-white transition hover:bg-yellow-600"
                >
                    Edit User
                </a>

            </div>

        </div>

        @if(session('success'))

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>

        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

                <p class="text-sm font-medium text-gray-500">
                    Full Name
                </p>

                <p class="mt-2 text-lg font-semibold text-gray-900">
                    {{ $user->name }}
                </p>

            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

                <p class="text-sm font-medium text-gray-500">
                    Username
                </p>

                <p class="mt-2 text-lg font-semibold text-gray-900">
                    {{ $user->username }}
                </p>

            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

                <p class="text-sm font-medium text-gray-500">
                    Email Status
                </p>

                <div class="mt-3">

                    @if($user->email_verified_at)

                        <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">
                            Verified
                        </span>

                    @else

                        <span class="rounded-full bg-yellow-100 px-3 py-1 text-sm font-semibold text-yellow-800">
                            Unverified
                        </span>

                    @endif

                </div>

            </div>

        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-red-900 px-6 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Account Information
                </h2>

            </div>

            <dl class="grid grid-cols-1 gap-0 md:grid-cols-2">

                <div class="border-b border-gray-200 p-6 md:border-r">

                    <dt class="text-sm font-medium text-gray-500">
                        Email Address
                    </dt>

                    <dd class="mt-2 break-all font-semibold text-gray-900">
                        {{ $user->email }}
                    </dd>

                </div>

                <div class="border-b border-gray-200 p-6">

                    <dt class="text-sm font-medium text-gray-500">
                        Role
                    </dt>

                    <dd class="mt-2">

                        <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-800">
                            {{ $user->role?->name ?? 'No role assigned' }}
                        </span>

                    </dd>

                </div>

                <div class="border-b border-gray-200 p-6 md:border-r">

                    <dt class="text-sm font-medium text-gray-500">
                        Provincial Office
                    </dt>

                    <dd class="mt-2 font-semibold text-gray-900">
                        {{ $user->province?->name ?? 'Not applicable' }}
                    </dd>

                </div>

                <div class="border-b border-gray-200 p-6">

                    <dt class="text-sm font-medium text-gray-500">
                        Email Verified At
                    </dt>

                    <dd class="mt-2 font-semibold text-gray-900">
                        {{ $user->email_verified_at?->format('F d, Y h:i A') ?? 'Not yet verified' }}
                    </dd>

                </div>

                <div class="p-6 md:border-r">

                    <dt class="text-sm font-medium text-gray-500">
                        Account Created
                    </dt>

                    <dd class="mt-2 font-semibold text-gray-900">
                        {{ $user->created_at?->format('F d, Y h:i A') }}
                    </dd>

                </div>

                <div class="p-6">

                    <dt class="text-sm font-medium text-gray-500">
                        Last Updated
                    </dt>

                    <dd class="mt-2 font-semibold text-gray-900">
                        {{ $user->updated_at?->format('F d, Y h:i A') }}
                    </dd>

                </div>

            </dl>

        </div>

        <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-5 py-4 text-sm text-yellow-900">

            User deletion is disabled because this account may be referenced by
            purchase orders, distributions, Call-Offs, receipts, project records,
            uploaded PDFs, or audit data.

        </div>

    </div>

</x-po_dashboard_layout>