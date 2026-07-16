<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $reportTitle }}</title>

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

            tfoot {
                display: table-row-group;
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
        /*
        |--------------------------------------------------------------------------
        | Resolve project PPE quantities
        |--------------------------------------------------------------------------
        |
        | PPE quantities are resolved by item name and label rather than
        | fixed database IDs so new records remain compatible with the
        | current item structure.
        |
        */

        $projectPpeQuantities = function ($designation): array {
            $items = collect($designation->items ?? []);

            $normalize = fn($value): string => strtolower(trim((string) $value));

            $matchesName = function ($designationItem, array $acceptedNames) use ($normalize): bool {
                $name = $normalize($designationItem->item?->item_name ?? '');

                return in_array($name, $acceptedNames, true);
            };

            $matchesLabel = function ($designationItem, array $acceptedLabels) use ($normalize): bool {
                $label = $normalize($designationItem->item?->label ?? '');

                return in_array($label, $acceptedLabels, true);
            };

            $longSleeveMedium = (int) $items
                ->filter(
                    fn($row): bool => $matchesName($row, [
                        'long sleeve',
                        'long sleeves',
                        'longsleeve',
                        'longsleeves',
                    ]) && $matchesLabel($row, ['m', 'medium']),
                )
                ->sum('quantity');

            $longSleeveLarge = (int) $items
                ->filter(
                    fn($row): bool => $matchesName($row, [
                        'long sleeve',
                        'long sleeves',
                        'longsleeve',
                        'longsleeves',
                    ]) && $matchesLabel($row, ['l', 'large']),
                )
                ->sum('quantity');

            $bucketHat = (int) $items
                ->filter(fn($row): bool => $matchesName($row, ['bucket hat', 'bucket hats']))
                ->sum('quantity');

            $rubberBootsUs9 = (int) $items
                ->filter(
                    fn($row): bool => $matchesName($row, ['rubber boot', 'rubber boots']) &&
                        $matchesLabel($row, ['us9', 'us 9', '9']),
                )
                ->sum('quantity');

            $rubberBootsUs10 = (int) $items
                ->filter(
                    fn($row): bool => $matchesName($row, ['rubber boot', 'rubber boots']) &&
                        $matchesLabel($row, ['us10', 'us 10', '10']),
                )
                ->sum('quantity');

            $gloves = (int) $items
                ->filter(fn($row): bool => $matchesName($row, ['hand glove', 'hand gloves', 'glove', 'gloves']))
                ->sum('quantity');

            $mask = (int) $items->filter(fn($row): bool => $matchesName($row, ['mask', 'masks']))->sum('quantity');

            $totalLongSleeve = $longSleeveMedium + $longSleeveLarge;

            $totalRubberBoots = $rubberBootsUs9 + $rubberBootsUs10;

            $totalPpe = $totalLongSleeve + $bucketHat + $totalRubberBoots + $gloves + $mask;

            return [
                'long_sleeve_medium' => $longSleeveMedium,

                'long_sleeve_large' => $longSleeveLarge,

                'total_long_sleeve' => $totalLongSleeve,

                'bucket_hat' => $bucketHat,

                'rubber_boots_us9' => $rubberBootsUs9,

                'rubber_boots_us10' => $rubberBootsUs10,

                'total_rubber_boots' => $totalRubberBoots,

                'gloves' => $gloves,

                'mask' => $mask,

                'total_ppe' => $totalPpe,
            ];
        };

        /*
        |--------------------------------------------------------------------------
        | Report totals
        |--------------------------------------------------------------------------
        */

        $totals = [
            'beneficiaries' => 0,
            'workdays' => 0,

            'long_sleeve_medium' => 0,
            'long_sleeve_large' => 0,
            'total_long_sleeve' => 0,

            'bucket_hat' => 0,

            'rubber_boots_us9' => 0,
            'rubber_boots_us10' => 0,
            'total_rubber_boots' => 0,

            'gloves' => 0,
            'mask' => 0,

            'total_ppe' => 0,
        ];
    @endphp

    {{-- Screen controls --}}
    <div
        class="mb-[14px] flex justify-end gap-2 rounded-lg border
            border-slate-300 bg-slate-50 p-[10px] print:hidden">
        <a href="{{ route('provincial.project-designations.index', [
            'search' => $search,
        ]) }}"
            class="inline-flex cursor-pointer items-center justify-center
                rounded-md border border-slate-300 bg-white px-4 py-[9px]
                text-[13px] font-bold text-slate-700 no-underline
                transition hover:bg-slate-100">
            Back to Projects
        </a>

        <button type="button" onclick="window.print()"
            class="inline-flex cursor-pointer items-center justify-center
                rounded-md border-0 bg-[#339DCB] px-4 py-[9px]
                text-[13px] font-bold text-white transition
                hover:bg-[#C4ECFE] hover:text-black">
            Print Report
        </button>
    </div>

    {{-- DOLE Letterhead --}}
    <div class="flex items-center justify-center gap-4 pl-24">

        <img src="{{ asset('images/print/dole_logo.webp') }}" alt="DOLE Logo"
            class="max-h-[70px] w-[120px] object-contain" onerror="this.style.display='none'">

        <div class="min-w-[300px] text-center">

            <p class="m-0 font-arial text-[10px] font-normal">
                Republic of the Philippines
            </p>

            <p class="mb-0 font-arial text-[11px] font-bold">
                DEPARTMENT OF LABOR AND EMPLOYMENT
            </p>

            <p class="mb-0 font-arial text-[10px] font-normal">
                Regional Office No. 5
            </p>

            <p class="mb-0 font-arial text-[9px] italic">
                DOLE RO5 Bldg., Doña Aurora St.,
                Old Albay, Legazpi City
            </p>

            <p class="mb-0 font-arial text-[9px] italic">
                ORD: 0981-461-8788&nbsp;&nbsp;
                TSSD: 0963-206-0008&nbsp;&nbsp;
                IMSD: 0912-330-4751
            </p>

            <p class="mb-0 font-arial text-[9px]
                    text-black underline">
                ro5@dole.gov.ph
            </p>

        </div>

        <img src="{{ asset('images/print/Bagong_Pilipinas.png') }}" alt="Bagong Pilipinas"
            class="max-h-[72px] w-[105px] object-contain" onerror="this.style.display='none'">

        <img src="{{ asset('images/print/iso-bureau-veritas.jpg') }}" alt="ISO Bureau Veritas"
            class="max-h-[70px] w-[150px] object-contain" onerror="this.style.display='none'">

    </div>

    {{-- Date --}}
    <div class="my-3 flex justify-between mr-[220px]">
        <p class="mt-1 text-[9px] font-semibold text-black">
            Province Office: {{ $provinceName }}
        </p>

        <div>
            <p class="mb-0 text-[9px] font-bold text-black">
                Date: {{ now()->format('F d, Y') }}
            </p>
        </div>
    </div>

    {{-- Main project table --}}
    <table
        class="w-full table-fixed border-collapse text-[8px]
            [&_th]:border [&_th]:border-[#222]
            [&_th]:px-[3px] [&_th]:py-[5px]
            [&_th]:text-center [&_th]:font-bold
            [&_th]:align-middle
            [&_td]:border [&_td]:border-[#222]
            [&_td]:px-[3px] [&_td]:py-[5px]
            [&_td]:text-center [&_td]:align-middle
            [&_td]:[overflow-wrap:anywhere]
            print-exact">
        <thead>

            <tr>
                <th rowspan="2" class="w-[3%] bg-[#339DCB] text-white">
                    No.
                </th>

                <th rowspan="2" class="w-[7%] bg-[#339DCB] text-white">
                    Project Code
                </th>

                <th rowspan="2" class="w-[11%] bg-[#339DCB] text-white">
                    Project Title
                </th>

                <th rowspan="2" class="w-[9%] bg-[#339DCB] text-white">
                    Location
                </th>

                <th rowspan="2" class="w-[5%] bg-[#339DCB] text-white">
                    Ben.
                </th>

                <th rowspan="2" class="w-[4%] bg-[#339DCB] text-white">
                    W.D.
                </th>

                <th rowspan="2" class="w-[8%] bg-[#339DCB] text-white">
                    Supplier
                </th>

                <th rowspan="2" class="w-[7%] bg-[#339DCB] text-white">
                    Delivery Receipt
                </th>

                <th rowspan="2" class="w-[7%] bg-[#339DCB] text-white">
                    Call-Off Number
                </th>

                <th colspan="3" class="bg-[#339DCB] text-white">
                    Long Sleeves
                </th>

                <th rowspan="2" class="bg-[#339DCB] text-white">
                    Bucket Hat
                </th>

                <th colspan="3" class="bg-[#339DCB] text-white">
                    Rubber Boots
                </th>

                <th rowspan="2" class="bg-[#339DCB] text-white">
                    Gloves
                </th>

                <th rowspan="2" class="bg-[#339DCB] text-white">
                    Mask
                </th>

                <th rowspan="2" class="w-[10%] bg-[#339DCB] text-white">
                    Remarks
                </th>
            </tr>

            <tr>
                <th class="bg-[#E9FFFF] text-black">
                    M
                </th>

                <th class="bg-[#E9FFFF] text-black">
                    L
                </th>

                <th class="bg-[#E9FFFF] text-black">
                    Total
                </th>

                <th class="bg-[#E9FFFF] text-black">
                    US9
                </th>

                <th class="bg-[#E9FFFF] text-black">
                    US10
                </th>

                <th class="bg-[#E9FFFF] text-black">
                    Total
                </th>
            </tr>

        </thead>

        <tbody>

            @forelse($designations as $index => $designation)
                @php
                    $allocation = $designation->provinceDistribution;

                    $batch = $allocation?->distributionBatch;

                    $callOff = $batch?->callOff;

                    $purchaseOrder = $batch?->purchaseOrder;

                    $supplier = $purchaseOrder?->supplier;

                    $deliveryReceipt = $designation->deliveryReceipt;

                    $ppe = $projectPpeQuantities($designation);

                    $totals['beneficiaries'] += (int) $designation->number_of_beneficiaries;

                    $totals['workdays'] += (int) $designation->number_of_days;

                    foreach (
                        [
                            'long_sleeve_medium',
                            'long_sleeve_large',
                            'total_long_sleeve',
                            'bucket_hat',
                            'rubber_boots_us9',
                            'rubber_boots_us10',
                            'total_rubber_boots',
                            'gloves',
                            'mask',
                            'total_ppe',
                        ]
                        as $key
                    ) {
                        $totals[$key] += (int) $ppe[$key];
                    }
                @endphp

                <tr>

                    <td class="font-bold">
                        {{ $index + 1 }}
                    </td>

                    <td class="text-left font-bold text-[#143A52]">
                        {{ $designation->project_code }}
                    </td>

                    <td class="text-left">
                        {{ $designation->project_title }}
                    </td>

                    <td class="text-left">
                        {{ $designation->location }}
                    </td>

                    <td>
                        {{ number_format((int) $designation->number_of_beneficiaries) }}
                    </td>

                    <td>
                        {{ number_format((int) $designation->number_of_days) }}
                    </td>

                    <td class="text-left">
                        {{ $supplier?->supplier_name ?? '—' }}
                    </td>

                    <td>
                        {{ $deliveryReceipt?->dr_number ?? '—' }}
                    </td>

                    <td class="font-bold text-[#143A52]">
                        {{ $callOff?->call_off_number ?? '—' }}
                    </td>

                    <td>
                        {{ number_format($ppe['long_sleeve_medium']) }}
                    </td>

                    <td>
                        {{ number_format($ppe['long_sleeve_large']) }}
                    </td>

                    <td class="bg-[#E9FFFF] font-extrabold
                            text-[#143A52] print-exact">
                        {{ number_format($ppe['total_long_sleeve']) }}
                    </td>

                    <td>
                        {{ number_format($ppe['bucket_hat']) }}
                    </td>

                    <td>
                        {{ number_format($ppe['rubber_boots_us9']) }}
                    </td>

                    <td>
                        {{ number_format($ppe['rubber_boots_us10']) }}
                    </td>

                    <td class="bg-[#E9FFFF] font-extrabold
                            text-[#143A52] print-exact">
                        {{ number_format($ppe['total_rubber_boots']) }}
                    </td>

                    <td>
                        {{ number_format($ppe['gloves']) }}
                    </td>

                    <td>
                        {{ number_format($ppe['mask']) }}
                    </td>

                    <td class="text-left">
                        {{ $designation->remarks ?: '—' }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="19" class="py-8 text-center text-slate-500">
                        No project distribution records found.
                    </td>
                </tr>
            @endforelse

        </tbody>


    </table>
    {{-- Signatures --}}
    <table class="mt-8 w-full border-collapse [page-break-inside:avoid]">
        <tr>

            <td class="w-1/2 border-0 px-[45px] py-0 align-top">

                <div class=" text-[11px] font-normal">
                    Prepared by:
                </div>
                <br><br>

                <input type="text" placeholder="Input Name"
                    class="w-full p-1 font-bold border border-gray-400 print:border-0 print:p-0 bg-transparent text-left text-[11px] focus:border-black focus:ring-0">

                <input type="text" placeholder="Input Position"
                    class="w-full p-1 border border-gray-400 print:border-0 print:p-0 bg-transparent text-left text-[11px] font-normal focus:border-black focus:ring-0 p-1">

            </td>

            <td class="w-1/2 border-0 px-[45px] py-0 align-top">

                <div class=" text-[11px] font-normal">
                    Reviewed by:
                </div>
                <br><br>

                <input type="text" placeholder="Input Name"
                    class="w-full p-1 font-bold border border-gray-400 print:border-0 print:p-0 bg-transparent text-left text-[11px] focus:border-black focus:ring-0 p-1">

                <input type="text" placeholder="Input Position"
                    class="w-full p-1 border border-gray-400 print:border-0 print:p-0 bg-transparent text-left text-[11px] font-normal focus:border-black focus:ring-0 p-1">

            </td>

        </tr>
    </table>

</body>

</html>
