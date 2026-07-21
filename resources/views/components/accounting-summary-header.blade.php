@props(['title', 'description'])
<section class="relative overflow-hidden rounded-3xl border border-[#B7D6E6] bg-white shadow-sm">
    <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-[#143A52] via-[#247BA0] to-[#55B7D9]"></div>
    <div class="px-6 py-7 sm:px-8">
        <span class="inline-flex rounded-full bg-[#B7D6E6]/35 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#247BA0] ring-1 ring-[#B7D6E6]">Accounting · Read Only</span>
        <h1 class="mt-4 text-2xl font-bold text-slate-950 sm:text-3xl">{{ $title }}</h1>
        <p class="mt-2 text-sm text-slate-600">{{ $description }}</p>
    </div>
</section>
