@props([
    'label',
    'value',
])

<div
    class="group rounded-2xl border border-slate-200
           bg-white p-5 shadow-sm transition
           hover:-translate-y-1 hover:shadow-md"
>
    <div
        class="mb-4 h-1 w-10 rounded-full
               bg-[#970C13] transition-all
               group-hover:w-16"
    ></div>

    <p
        class="text-xs font-bold uppercase
               tracking-wider text-slate-400"
    >
        {{ $label }}
    </p>

    <p
        class="mt-3 break-words text-2xl font-bold
               text-slate-900"
    >
        {{ $value }}
    </p>
</div>