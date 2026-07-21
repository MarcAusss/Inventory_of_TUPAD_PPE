<x-po_dashboard_layout title="Provincial Inventory Summary">

    <div class="mx-auto max-w-[1900px] space-y-6">

        {{-- Page Header --}}
        <section
            class="relative overflow-hidden rounded-3xl
                   border border-[#B7D6E6] bg-white shadow-sm">

            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b
                       from-[#143A52]
                       via-[#247BA0]
                       to-[#55B7D9]">
            </div>

            <div
                class="flex flex-col gap-6 px-6 py-7 sm:px-8
                       lg:flex-row lg:items-center
                       lg:justify-between">

                <div>

                    <span
                        class="inline-flex rounded-full
                               bg-[#B7D6E6]/35 px-3 py-1
                               text-xs font-bold uppercase
                               tracking-[0.16em] text-[#247BA0]
                               ring-1 ring-[#B7D6E6]">

                        Accounting · Read Only

                    </span>

                    <h1
                        class="mt-4 text-2xl font-bold
                               text-slate-950 sm:text-3xl">

                        Provincial Inventory Summary

                    </h1>

                    <p class="mt-2 text-sm text-slate-600">

                        Current PPE inventory balances for all
                        provincial offices.

                    </p>

                </div>

                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center justify-center
                           rounded-xl border border-[#B7D6E6]
                           bg-white px-5 py-3 text-sm font-bold
                           text-[#247BA0] shadow-sm transition
                           hover:bg-[#F7FBFD]">

                    Print Summary

                </button>

            </div>

        </section>

        {{-- Summary Cards --}}
        <section
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <div
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm">

                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.14em] text-slate-500">

                    Visible Provinces

                </p>

                <p
                    class="mt-3 text-3xl font-extrabold
                           text-[#143A52]">

                    {{ number_format($totalProvinces) }}

                </p>

            </div>

            <div
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm">

                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.14em] text-slate-500">

                    Total Available PPE

                </p>

                <p
                    class="mt-3 text-3xl font-extrabold
                           text-[#143A52]">

                    {{ number_format($totalAvailable) }}

                </p>

            </div>

            <div
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm">

                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.14em] text-slate-500">

                    Long Sleeves

                </p>

                <p
                    class="mt-3 text-3xl font-extrabold
                           text-[#247BA0]">

                    {{ number_format($totals['long_total']) }}

                </p>

            </div>

            <div
                class="rounded-2xl border border-slate-200
                       bg-white p-5 shadow-sm">

                <p
                    class="text-xs font-bold uppercase
                           tracking-[0.14em] text-slate-500">

                    Rubber Boots

                </p>

                <p
                    class="mt-3 text-3xl font-extrabold
                           text-[#247BA0]">

                    {{ number_format($totals['boots_total']) }}

                </p>

            </div>

        </section>

        {{-- Filters and Table --}}
        <section
            class="overflow-hidden rounded-3xl
                   border border-slate-200
                   bg-white shadow-sm">

            <form
                method="GET"
                action="{{ route('accounting.provincial-inventory.index') }}"
                class="grid gap-4 border-b border-slate-200 p-5
                       sm:grid-cols-2
                       lg:grid-cols-[1fr_320px_auto_auto]
                       lg:items-end">

                <div>

                    <label
                        for="search"
                        class="mb-2 block text-xs font-bold
                               uppercase tracking-wide
                               text-slate-500">

                        Search Province

                    </label>

                    <input
                        id="search"
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search province name..."
                        class="w-full rounded-xl
                               border-slate-300 text-sm
                               focus:border-[#339DCB]
                               focus:ring-[#339DCB]">

                </div>

                <div>

                    <label
                        for="province_id"
                        class="mb-2 block text-xs font-bold
                               uppercase tracking-wide
                               text-slate-500">

                        Province

                    </label>

                    <select
                        id="province_id"
                        name="province_id"
                        class="w-full rounded-xl
                               border-slate-300 text-sm
                               focus:border-[#339DCB]
                               focus:ring-[#339DCB]">

                        <option value="">
                            All provinces
                        </option>

                        @foreach ($provinces as $province)

                            <option
                                value="{{ $province->id }}"
                                @selected(
                                    (int) $provinceId
                                    === (int) $province->id
                                )>

                                {{ $province->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                <button
                    type="submit"
                    class="rounded-xl bg-[#339DCB]
                           px-5 py-3 text-sm font-bold
                           text-white transition
                           hover:bg-[#247BA0]">

                    Apply Filter

                </button>

                <a
                    href="{{ route('accounting.provincial-inventory.index') }}"
                    class="rounded-xl border border-[#B7D6E6]
                           px-5 py-3 text-center text-sm
                           font-bold text-[#247BA0]
                           transition hover:bg-[#F7FBFD]">

                    Reset

                </a>

            </form>

            <div class="overflow-x-auto">

                <table
                    class="w-full min-w-[1250px]
                           border-collapse">

                    <thead
                        class="text-xs font-bold uppercase
                               tracking-wide text-white">

                        <tr class="bg-[#247BA0]">

                            <th
                                rowspan="2"
                                class="border border-white/20
                                       px-5 py-4 text-left">

                                Province

                            </th>

                            <th
                                colspan="3"
                                class="border border-white/20
                                       px-4 py-3 text-center">

                                Long Sleeves

                            </th>

                            <th
                                rowspan="2"
                                class="border border-white/20
                                       px-4 py-3 text-center">

                                Bucket Hat

                            </th>

                            <th
                                colspan="3"
                                class="border border-white/20
                                       px-4 py-3 text-center">

                                Rubber Boots

                            </th>

                            <th
                                rowspan="2"
                                class="border border-white/20
                                       px-4 py-3 text-center">

                                Gloves

                            </th>

                            <th
                                rowspan="2"
                                class="border border-white/20
                                       px-4 py-3 text-center">

                                Mask

                            </th>

                            <th
                                rowspan="2"
                                class="border border-white/20
                                       bg-[#143A52]
                                       px-4 py-3 text-center">

                                Total PPE

                            </th>

                        </tr>

                        <tr class="bg-[#339DCB]">

                            <th
                                class="border border-white/20
                                       px-4 py-3 text-center">
                                M
                            </th>

                            <th
                                class="border border-white/20
                                       px-4 py-3 text-center">
                                L
                            </th>

                            <th
                                class="border border-white/20
                                       px-4 py-3 text-center">
                                Total
                            </th>

                            <th
                                class="border border-white/20
                                       px-4 py-3 text-center">
                                US9
                            </th>

                            <th
                                class="border border-white/20
                                       px-4 py-3 text-center">
                                US10
                            </th>

                            <th
                                class="border border-white/20
                                       px-4 py-3 text-center">
                                Total
                            </th>

                        </tr>

                    </thead>

                    <tbody
                        class="divide-y divide-slate-100
                               text-sm text-slate-700">

                        @forelse ($summaries as $row)

                            <tr
                                class="transition
                                       hover:bg-[#F7FBFD]">

                                <td
                                    class="px-5 py-4 font-bold
                                           text-slate-900">

                                    {{ $row['province']->name }}

                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $row['long_medium']
                                    ) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $row['long_large']
                                    ) }}
                                </td>

                                <td
                                    class="bg-[#F7FBFD]
                                           px-4 py-4 text-center
                                           font-bold text-[#247BA0]">

                                    {{ number_format(
                                        $row['long_total']
                                    ) }}

                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $row['bucket_hat']
                                    ) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $row['boots_9']
                                    ) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $row['boots_10']
                                    ) }}
                                </td>

                                <td
                                    class="bg-[#F7FBFD]
                                           px-4 py-4 text-center
                                           font-bold text-[#247BA0]">

                                    {{ number_format(
                                        $row['boots_total']
                                    ) }}

                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $row['gloves']
                                    ) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $row['mask']
                                    ) }}
                                </td>

                                <td
                                    class="bg-[#EAF6FC]
                                           px-4 py-4 text-center
                                           text-lg font-extrabold
                                           text-[#143A52]">

                                    {{ number_format(
                                        $row['total']
                                    ) }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="11"
                                    class="px-6 py-14 text-center
                                           text-sm text-slate-500">

                                    No provincial inventory records found.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                    @if ($summaries->isNotEmpty())

                        <tfoot
                            class="border-t-2 border-[#B7D6E6]
                                   bg-[#F7FBFD] text-sm
                                   font-extrabold text-[#143A52]">

                            <tr>

                                <td class="px-5 py-4">
                                    Consolidated Total
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $totals['long_medium']
                                    ) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $totals['long_large']
                                    ) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $totals['long_total']
                                    ) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $totals['bucket_hat']
                                    ) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $totals['boots_9']
                                    ) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $totals['boots_10']
                                    ) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $totals['boots_total']
                                    ) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $totals['gloves']
                                    ) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    {{ number_format(
                                        $totals['mask']
                                    ) }}
                                </td>

                                <td
                                    class="bg-[#EAF6FC]
                                           px-4 py-4 text-center
                                           text-lg">

                                    {{ number_format(
                                        $totals['total']
                                    ) }}

                                </td>

                            </tr>

                        </tfoot>

                    @endif

                </table>

            </div>

        </section>

    </div>

    <style>
        @media print {
            aside,
            nav,
            form,
            button {
                display: none !important;
            }

            body {
                background: white !important;
            }

            main {
                margin: 0 !important;
                padding: 0 !important;
            }

            section {
                box-shadow: none !important;
            }
        }
    </style>

</x-po_dashboard_layout>