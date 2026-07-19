<x-po_dashboard_layout title="User Details">

    <div class="mx-auto max-w-6xl space-y-6">

        {{-- Page Header --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#075985] via-[#0284C7] to-[#38BDF8]"></div>
            <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-[#7DD3FC]/20 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-center gap-4">
                    <div
                        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#075985] via-[#0284C7] to-[#38BDF8] text-2xl font-black uppercase text-white shadow-md shadow-sky-600/20">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="rounded-full bg-[#7DD3FC]/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-[#075985] ring-1 ring-inset ring-[#7DD3FC]">
                                User Profile
                            </span>

                            @if ($user->is(auth()->user()))
                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                    Your Account
                                </span>
                            @endif
                        </div>

                        <h1 class="mt-3 truncate text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                            {{ $user->name }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-600">Account, access role, and provincial assignment details.</p>
                    </div>
                </div>

                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                    <a href="{{ route('tssd.users.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-[#7DD3FC] hover:bg-sky-50 hover:text-[#075985] focus:outline-none focus:ring-2 focus:ring-[#0284C7] focus:ring-offset-2">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                        </svg>
                        Back to Users
                    </a>

                    <a href="{{ route('tssd.users.edit', $user) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#0EA5E9] px-5 py-3 text-sm font-bold text-white shadow-sm shadow-sky-600/20 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#0284C7] focus:ring-offset-2">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.86 4.49 2.65 2.65M18 2.75a1.88 1.88 0 0 1 2.65 2.65L8.25 17.8l-4.5 1.2 1.2-4.5L18 2.75Z" />
                        </svg>
                        Edit User
                    </a>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800"
                role="status">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>
                </div>
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Full Name</p>
                        <p class="mt-3 text-lg font-black text-slate-900">{{ $user->name }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-[#0284C7]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0A17.9 17.9 0 0 1 12 21.75c-2.68 0-5.22-.59-7.5-1.65Z" />
                        </svg>
                    </div>
                </div>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Username</p>
                        <p class="mt-3 text-lg font-black text-slate-900">{{ $user->username }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-[#0284C7]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 9.75h7.5m-7.5 4.5h7.5M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                        </svg>
                    </div>
                </div>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Email Status</p>
                        <div class="mt-3">
                            @if ($user->email_verified_at)
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
                        </div>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-[#0284C7]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M12 3l7.5 3v5.25c0 4.63-3.2 8.77-7.5 9.75-4.3-.98-7.5-5.12-7.5-9.75V6L12 3Z" />
                        </svg>
                    </div>
                </div>
            </article>
        </div>

        {{-- Account Information --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5 sm:px-7">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-[#0284C7] ring-1 ring-inset ring-sky-200">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 6.75h7.5M8.25 10.5h7.5m-7.5 3.75H12M6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900">Account Information</h2>
                    <p class="mt-0.5 text-sm text-slate-500">Complete account access and activity details.</p>
                </div>
            </div>

            <dl class="grid grid-cols-1 md:grid-cols-2">
                <div class="border-b border-slate-200 p-6 md:border-r">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Email Address</dt>
                    <dd class="mt-2 break-all font-bold text-slate-900">{{ $user->email }}</dd>
                </div>

                <div class="border-b border-slate-200 p-6">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Role</dt>
                    <dd class="mt-2">
                        <span
                            class="inline-flex rounded-full bg-sky-50 px-3 py-1 text-sm font-bold text-[#075985] ring-1 ring-inset ring-sky-200">
                            {{ $user->role?->name ?? 'No role assigned' }}
                        </span>
                    </dd>
                </div>

                <div class="border-b border-slate-200 p-6 md:border-r">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Provincial Office</dt>
                    <dd class="mt-2 font-bold text-slate-900">{{ $user->province?->name ?? 'Not applicable' }}</dd>
                </div>

                <div class="border-b border-slate-200 p-6">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Email Verified At</dt>
                    <dd class="mt-2 font-bold text-slate-900">
                        {{ $user->email_verified_at?->format('F d, Y h:i A') ?? 'Not yet verified' }}
                    </dd>
                </div>

                <div class="border-b border-slate-200 p-6 md:border-b-0 md:border-r">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Account Created</dt>
                    <dd class="mt-2 font-bold text-slate-900">{{ $user->created_at?->format('F d, Y h:i A') }}</dd>
                </div>

                <div class="p-6">
                    <dt class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Last Updated</dt>
                    <dd class="mt-2 font-bold text-slate-900">{{ $user->updated_at?->format('F d, Y h:i A') }}</dd>
                </div>
            </dl>
        </section>

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
                    User deletion is disabled because this account may be referenced by purchases, distributions,
                    Call-Offs, receipts, project records, uploaded PDFs, or audit data.
                </p>
            </div>
        </div>
    </div>

</x-po_dashboard_layout>