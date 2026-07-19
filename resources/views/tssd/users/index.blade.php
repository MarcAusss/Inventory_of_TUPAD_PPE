<x-po_dashboard_layout title="User Management">

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- Page Header --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#075985] via-[#0284C7] to-[#38BDF8]"></div>

            <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-[#7DD3FC]/15 blur-3xl"></div>
            <div class="absolute -bottom-20 right-40 h-44 w-44 rounded-full bg-[#38BDF8]/10 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-[#7DD3FC]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#0284C7] ring-1 ring-inset ring-[#7DD3FC]">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18 18.72a9.1 9.1 0 0 0 3.74-.48 3 3 0 0 0-4.68-2.72M18 18.72v-.01c0-1.1-.29-2.13-.8-3.02M18 18.72v.13A11.93 11.93 0 0 1 12 20.5c-2.19 0-4.24-.59-6-1.62v-.16m12 0a5.97 5.97 0 0 0-.94-3.2M6 18.72a9.1 9.1 0 0 1-3.74-.48 3 3 0 0 1 4.68-2.72M6 18.72v-.01c0-1.1.29-2.13.8-3.02M6 18.72v.13A11.93 11.93 0 0 0 12 20.5m-5.2-4.81a6 6 0 0 1 10.4 0M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                            TSSD Unit
                        </span>

                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                            Administration
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                        User Management
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        View system accounts, review assigned access, and update user information.
                    </p>
                </div>

                <div
                    class="flex w-full items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50/80 px-5 py-4 sm:w-auto sm:min-w-[230px]">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#075985] to-[#38BDF8] text-white shadow-sm shadow-[#0284C7]/20">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a4 4 0 0 0-4-4h-1m-5 6H2v-2a4 4 0 0 1 4-4h4m6-4a4 4 0 1 1 0-8m-4 8a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm4 10v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Records</p>
                        <p class="mt-1 text-2xl font-black text-[#0284C7]">{{ number_format($users->total()) }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Feedback Messages --}}
        @if (session('success'))
            <div
                class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 shadow-sm"
                role="status">
                <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>
                </div>
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
            <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9-1.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM12 16.5h.008v.008H12V16.5Z" />
                </svg>
            </div>

            <div>
                <p class="font-bold">Account records are protected</p>
                <p class="mt-0.5 leading-6 text-amber-800">
                    User deletion is disabled to preserve purchase, distribution, receiving, project, and audit records.
                </p>
            </div>
        </div>

        {{-- Search and Filters --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Search &amp; Filter</h2>
                    <p class="mt-1 text-sm text-slate-500">Narrow the list using one or more account details.</p>
                </div>

                @if ($search || $roleId || $provinceId)
                    <span
                        class="inline-flex w-fit items-center gap-1.5 rounded-full bg-[#7DD3FC]/15 px-3 py-1 text-xs font-bold text-[#0284C7] ring-1 ring-inset ring-[#7DD3FC]/70">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#38BDF8]"></span>
                        Filters active
                    </span>
                @endif
            </div>

            <form action="{{ route('tssd.users.index') }}" method="GET"
                class="grid grid-cols-1 gap-5 px-6 py-6 sm:grid-cols-2 lg:grid-cols-12 lg:items-end sm:px-7">

                <div class="sm:col-span-2 lg:col-span-5">
                    <label for="search" class="mb-2 block text-sm font-bold text-slate-700">
                        Search user
                    </label>

                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                            </svg>
                        </div>

                        <input type="search" id="search" name="search" value="{{ $search }}"
                            placeholder="Name, username, email, or role"
                            class="block w-full rounded-xl border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-[#0284C7] focus:ring-[#0284C7]">
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <label for="role_id" class="mb-2 block text-sm font-bold text-slate-700">Role</label>

                    <select id="role_id" name="role_id"
                        class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0284C7] focus:ring-[#0284C7]">
                        <option value="">All roles</option>

                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((int) $roleId === (int) $role->id)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label for="province_id" class="mb-2 block text-sm font-bold text-slate-700">Province</label>

                    <select id="province_id" name="province_id"
                        class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0284C7] focus:ring-[#0284C7]">
                        <option value="">All provinces</option>

                        @foreach ($provinces as $province)
                            <option value="{{ $province->id }}" @selected((int) $provinceId === (int) $province->id)>
                                {{ $province->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2 sm:col-span-2 lg:col-span-2">
                    <button type="submit"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#38BDF8] px-4 py-3 text-sm font-bold text-white shadow-sm shadow-[#0284C7]/20 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#0284C7] focus:ring-offset-2">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 6h10.25M3.25 6H6.5m0 0a2 2 0 1 0 4 0 2 2 0 0 0-4 0Zm7 12h7.25M3.25 18H9.5m0 0a2 2 0 1 0 4 0 2 2 0 0 0-4 0Zm6-6h5.25M3.25 12H11.5m0 0a2 2 0 1 0 4 0 2 2 0 0 0-4 0Z" />
                        </svg>
                        Apply
                    </button>

                    @if ($search || $roleId || $provinceId)
                        <a href="{{ route('tssd.users.index') }}" title="Reset filters"
                            class="inline-flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-600 shadow-sm transition hover:border-[#7DD3FC] hover:bg-[#7DD3FC]/10 hover:text-[#0284C7] focus:outline-none focus:ring-2 focus:ring-[#0284C7] focus:ring-offset-2">
                            <span class="sr-only">Reset filters</span>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.02 9.35h4.15V5.2m-.36 3.79a8.25 8.25 0 1 0 .26 5.88" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- Users Table --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                <div>
                    <h2 class="text-lg font-black text-slate-900">System Users</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Showing {{ number_format($users->firstItem() ?? 0) }}–{{ number_format($users->lastItem() ?? 0) }}
                        of {{ number_format($users->total()) }} records
                    </p>
                </div>

                <div
                    class="inline-flex w-fit items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-600">
                    <svg class="h-4 w-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0A17.9 17.9 0 0 1 12 21.75c-2.68 0-5.22-.59-7.5-1.63Z" />
                    </svg>
                    Account Directory
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#0EA5E9] text-xs font-bold uppercase tracking-[0.08em] text-white">
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-left">No.</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-left">User</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-left">Username</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-left">Role</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-left">Province</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-center">Status</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($users as $managedUser)
                            <tr class="group transition-colors hover:bg-[#7DD3FC]/[0.08]">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-500">
                                    {{ $users->firstItem() + $loop->index }}
                                </td>

                                <td class="min-w-[280px] px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#7DD3FC]/35 to-[#7DD3FC]/15 text-sm font-black uppercase text-[#0284C7] ring-1 ring-inset ring-[#7DD3FC]/60">
                                            {{ mb_substr($managedUser->name, 0, 1) }}
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-bold text-slate-900">{{ $managedUser->name }}</p>

                                                @if ($managedUser->is(auth()->user()))
                                                    <span
                                                        class="rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-sky-700 ring-1 ring-inset ring-sky-200">
                                                        You
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="mt-1 truncate text-sm text-slate-500">{{ $managedUser->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-700">
                                    {{ $managedUser->username }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200">
                                        {{ $managedUser->role?->name ?? 'No role' }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 21s6-5.1 6-11a6 6 0 1 0-12 0c0 5.9 6 11 6 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                        </svg>
                                        {{ $managedUser->province?->name ?? 'Not assigned' }}
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    @if ($managedUser->email_verified_at)
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Verified
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Unverified
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('tssd.users.show', $managedUser) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:border-[#7DD3FC] hover:bg-[#7DD3FC]/10 hover:text-[#0284C7] focus:outline-none focus:ring-2 focus:ring-[#0284C7] focus:ring-offset-2">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.04 12.32a1 1 0 0 1 0-.64C3.42 7.51 7.35 4.5 12 4.5s8.58 3.01 9.96 7.18a1 1 0 0 1 0 .64C20.58 16.49 16.65 19.5 12 19.5S3.42 16.49 2.04 12.32Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            View
                                        </a>

                                        <a href="{{ route('tssd.users.edit', $managedUser) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-[#0284C7] px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#075985] focus:outline-none focus:ring-2 focus:ring-[#0284C7] focus:ring-offset-2">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.86 4.49 2.65 2.65M18 2.75a1.88 1.88 0 0 1 2.65 2.65L8.25 17.8l-4.5 1.2 1.2-4.5L18 2.75Z" />
                                            </svg>
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0A17.9 17.9 0 0 1 12 21.75c-2.68 0-5.22-.59-7.5-1.63Z" />
                                        </svg>
                                    </div>
                                    <p class="mt-4 font-bold text-slate-700">No users found</p>
                                    <p class="mt-1 text-sm text-slate-500">Try changing or clearing the selected filters.</p>

                                    @if ($search || $roleId || $provinceId)
                                        <a href="{{ route('tssd.users.index') }}"
                                            class="mt-5 inline-flex items-center rounded-xl bg-[#0284C7] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#075985]">
                                            Clear filters
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-slate-200 bg-slate-50/70 px-6 py-4 sm:px-7">
                    {{ $users->onEachSide(1)->links() }}
                </div>
            @endif
        </section>

    </div>

</x-po_dashboard_layout>