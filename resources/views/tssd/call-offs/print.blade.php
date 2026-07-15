<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Province Distribution Summary - {{ $callOff->call_off_number }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        @media print {
            thead {
                display: table-header-group;
            }

            tr,
            th,
            td {
                page-break-inside: avoid;
            }

            .print-exact {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body class="m-0 bg-white p-[10mm] font-sans text-[8px] text-black print:p-0">

    @php
        $batch = $callOff->distributionBatch;
        $purchaseOrder = $batch?->purchaseOrder;
        $allocations = $batch?->provinceDistributions ?? collect();

        $ppeQuantities = function ($allocation): array {
            $items = collect($allocation->items ?? []);

            $normalize = fn($value): string => strtolower(trim((string) $value));

            $matchesName = function ($row, array $names) use ($normalize): bool {
                return in_array($normalize($row->item?->item_name ?? ''), $names, true);
            };

            $matchesLabel = function ($row, array $labels) use ($normalize): bool {
                return in_array($normalize($row->item?->label ?? ''), $labels, true);
            };

            $lsm = (int) $items
                ->filter(
                    fn($row): bool => $matchesName($row, [
                        'long sleeve',
                        'long sleeves',
                        'longsleeve',
                        'longsleeves',
                    ]) && $matchesLabel($row, ['m', 'medium']),
                )
                ->sum('quantity');

            $lsl = (int) $items
                ->filter(
                    fn($row): bool => $matchesName($row, [
                        'long sleeve',
                        'long sleeves',
                        'longsleeve',
                        'longsleeves',
                    ]) && $matchesLabel($row, ['l', 'large']),
                )
                ->sum('quantity');

            $bucket = (int) $items
                ->filter(fn($row): bool => $matchesName($row, ['bucket hat', 'bucket hats']))
                ->sum('quantity');

            $us9 = (int) $items
                ->filter(
                    fn($row): bool => $matchesName($row, ['rubber boot', 'rubber boots']) &&
                        $matchesLabel($row, ['us9', 'us 9', '9']),
                )
                ->sum('quantity');

            $us10 = (int) $items
                ->filter(
                    fn($row): bool => $matchesName($row, ['rubber boot', 'rubber boots']) &&
                        $matchesLabel($row, ['us10', 'us 10', '10']),
                )
                ->sum('quantity');

            $gloves = (int) $items
                ->filter(fn($row): bool => $matchesName($row, ['hand glove', 'hand gloves', 'glove', 'gloves']))
                ->sum('quantity');

            $mask = (int) $items->filter(fn($row): bool => $matchesName($row, ['mask', 'masks']))->sum('quantity');

            return [
                'lsm' => $lsm,
                'lsl' => $lsl,
                'total_ls' => $lsm + $lsl,
                'bucket' => $bucket,
                'us9' => $us9,
                'us10' => $us10,
                'total_boots' => $us9 + $us10,
                'gloves' => $gloves,
                'mask' => $mask,
            ];
        };

        $totals = [
            'lsm' => 0,
            'lsl' => 0,
            'total_ls' => 0,
            'bucket' => 0,
            'us9' => 0,
            'us10' => 0,
            'total_boots' => 0,
            'gloves' => 0,
            'mask' => 0,
        ];
    @endphp

    {{-- Screen controls --}}
    <div class="mb-[14px] flex justify-end gap-2 rounded-lg border border-slate-300 bg-slate-50 p-[10px] print:hidden">
        <button type="button" onclick="window.close()"
            class="inline-flex cursor-pointer items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-[9px] text-[13px] font-bold text-slate-700 hover:bg-slate-100">
            Close
        </button>

        <button type="button" onclick="window.print()"
            class="inline-flex cursor-pointer items-center justify-center rounded-md border-0 bg-[#970C13] px-4 py-[9px] text-[13px] font-bold text-white hover:bg-[#7f0a10]">
            Print Report
        </button>
    </div>

    {{-- DOLE letterhead --}}
    <div class="flex items-start justify-center gap-4 pl-24">
        <img src="{{ asset('images/print/dole_logo.webp') }}" alt="DOLE Logo"
            class="max-h-[85px] w-[120px] object-contain" onerror="this.style.display='none'">

        <div class="min-w-[460px] text-center">
            <p class="m-0 text-[14px] font-normal">
                Republic of the Philippines
            </p>

            <p class="mb-0 mt-1 text-[17px] font-extrabold">
                DEPARTMENT OF LABOR AND EMPLOYMENT
            </p>

            <p class="mb-0 mt-[13px] text-[15px] font-bold">
                Regional Office No. 5
            </p>

            <p class="mb-0 mt-[14px] text-[11px] italic">
                DOLE RO5 Bldg., Doña Aurora St., Old Albay, Legazpi City
            </p>

            <p class="mb-0 mt-[7px] text-[10px] italic">
                ORD: 0981-461-8788&nbsp;&nbsp;
                TSSD: 0963-206-0008&nbsp;&nbsp;
                IMSD: 0912-330-4751
            </p>

            <p class="mb-0 mt-[7px] text-[13px] text-black underline">
                ro5@dole.gov.ph
            </p>

            <p class="mb-0 mt-[7px] text-[11px] font-bold text-black">
                {{ now()->format('F d, Y') }}
            </p>
        </div>

        <img src="{{ asset('images/print/Bagong_Pilipinas.png') }}" alt="Bagong Pilipinas"
            class="max-h-[82px] w-[105px] object-contain" onerror="this.style.display='none'">

        <img src="{{ asset('images/print/iso-bureau-veritas.jpg') }}" alt="ISO Bureau Veritas"
            class="max-h-[78px] w-[150px] object-contain" onerror="this.style.display='none'">
    </div>

    <div class="py-10"></div>

    {{-- Distribution table --}}
    <table
        class="w-full table-fixed border-collapse text-[9px]
        [&_th]:border [&_th]:border-[#222]
        [&_th]:px-[4px] [&_th]:py-[6px]
        [&_th]:text-center [&_th]:font-bold
        [&_th]:align-middle
        [&_td]:border [&_td]:border-[#222]
        [&_td]:px-[4px] [&_td]:py-[6px]
        [&_td]:text-center [&_td]:align-middle
        [&_td]:[overflow-wrap:anywhere]
        print-exact">
        <thead>
            <tr>
                <th rowspan="2" class="w-[11%] bg-[#641D21] text-left text-white">
                    Province
                </th>

                <th rowspan="2" class="w-[9%] bg-[#641D21] text-white">
                    Delivery Date
                </th>

                <th rowspan="2" class="w-[17%] bg-[#641D21] text-left text-white">
                    Place of Delivery
                </th>

                <th colspan="3" class="bg-[#641D21] text-white">
                    Long Sleeves
                </th>

                <th rowspan="2" class="bg-[#641D21] text-white">
                    Bucket Hat
                </th>

                <th colspan="3" class="bg-[#641D21] text-white">
                    Rubber Boots
                </th>

                <th rowspan="2" class="bg-[#641D21] text-white">
                    Gloves
                </th>

                <th rowspan="2" class="bg-[#641D21] text-white">
                    Mask
                </th>
            </tr>

            <tr>
                <th class="bg-[#970C13] text-white">M</th>
                <th class="bg-[#970C13] text-white">L</th>
                <th class="bg-[#970C13] text-white">Total</th>

                <th class="bg-[#970C13] text-white">US9</th>
                <th class="bg-[#970C13] text-white">US10</th>
                <th class="bg-[#970C13] text-white">Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse($allocations as $allocation)
                @php
                    $ppe = $ppeQuantities($allocation);

                    foreach (array_keys($totals) as $key) {
                        $totals[$key] += $ppe[$key];
                    }
                @endphp

                <tr>
                    <td class="text-left font-bold uppercase text-[#641D21]">
                        {{ $allocation->province?->name ?? '—' }}
                    </td>

                    <td>
                        {{ $allocation->scheduled_delivery_date?->format('M d, Y') ?? '—' }}
                    </td>

                    <td class="text-left">
                        {{ $allocation->place_of_delivery ?? '—' }}
                    </td>

                    <td>{{ number_format($ppe['lsm']) }}</td>
                    <td>{{ number_format($ppe['lsl']) }}</td>

                    <td class="bg-red-50 font-extrabold text-[#641D21] print-exact">
                        {{ number_format($ppe['total_ls']) }}
                    </td>

                    <td>{{ number_format($ppe['bucket']) }}</td>
                    <td>{{ number_format($ppe['us9']) }}</td>
                    <td>{{ number_format($ppe['us10']) }}</td>

                    <td class="bg-red-50 font-extrabold text-[#641D21] print-exact">
                        {{ number_format($ppe['total_boots']) }}
                    </td>

                    <td>{{ number_format($ppe['gloves']) }}</td>
                    <td>{{ number_format($ppe['mask']) }}</td>
                </tr>

            @empty
                <tr>
                    <td colspan="12" class="py-8 text-center text-slate-500">
                        No provincial distribution records found.
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if ($allocations->isNotEmpty())
            <tfoot>
                <tr class="font-extrabold">
                    <td colspan="3" class="bg-slate-100 text-right uppercase print-exact">
                        Grand Total
                    </td>

                    <td class="bg-slate-100 print-exact">
                        {{ number_format($totals['lsm']) }}
                    </td>

                    <td class="bg-slate-100 print-exact">
                        {{ number_format($totals['lsl']) }}
                    </td>

                    <td class="bg-[#DF979B]/30 text-[#641D21] print-exact">
                        {{ number_format($totals['total_ls']) }}
                    </td>

                    <td class="bg-slate-100 print-exact">
                        {{ number_format($totals['bucket']) }}
                    </td>

                    <td class="bg-slate-100 print-exact">
                        {{ number_format($totals['us9']) }}
                    </td>

                    <td class="bg-slate-100 print-exact">
                        {{ number_format($totals['us10']) }}
                    </td>

                    <td class="bg-[#DF979B]/30 text-[#641D21] print-exact">
                        {{ number_format($totals['total_boots']) }}
                    </td>

                    <td class="bg-slate-100 print-exact">
                        {{ number_format($totals['gloves']) }}
                    </td>

                    <td class="bg-slate-100 print-exact">
                        {{ number_format($totals['mask']) }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Signatures --}}
    <table class="mt-8 w-full border-collapse [page-break-inside:avoid]">
        <tr>
            <td class="w-1/2 border-0 px-[35px] py-0 align-top">
                <div class="border-r border-slate-300 px-4 py-3">
                    <p class="m-0 text-[8px] font-bold uppercase tracking-wide text-slate-500">
                        Call-Off Number
                    </p>

                    <p class="mb-0 mt-1 text-[11px] font-extrabold text-[#641D21]">
                        {{ $callOff->call_off_number }}
                    </p>
                </div>
            </td>
        </tr>
    </table>

</body>

</html>
