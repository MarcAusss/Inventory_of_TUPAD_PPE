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
         * Resolve project PPE quantities by item name and label.
         *
         * This does not depend on fixed database item IDs.
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
    @endphp

    {{-- Screen controls --}}
    <div class="mb-[14px] flex justify-end gap-2 rounded-lg border border-slate-300 bg-slate-50 p-[10px] print:hidden">
        <a href="{{ route('provincial.project-designations.index', [
            'search' => $search,
        ]) }}"
            class="inline-flex cursor-pointer items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-[9px] text-[13px] font-bold text-slate-700 no-underline hover:bg-slate-100">
            Back to Projects
        </a>

        <button type="button"
            class="inline-flex cursor-pointer items-center justify-center rounded-md border-0 bg-[#970C13] px-4 py-[9px] text-[13px] font-bold text-white hover:bg-[#7f0a10]"
            onclick="window.print()">
            Print Report
        </button>
    </div>

    {{-- Letterhead --}}
    <div class="flex justify-center pl-28">
        <img src="{{ asset('images/print/dole_logo.webp') }}" alt="DOLE Logo"
            class="max-h-[85px] w-[120px] object-contain" onerror="this.style.display='none'">

        <div class="text-center">
            <p class="m-0 text-center text-[14px] font-normal">
                Republic of the Philippines
            </p>

            <p class="mb-0 mt-1 text-[17px] font-extrabold">
                DEPARTMENT OF LABOR AND EMPLOYMENT
            </p>

            <p class="mb-0 text-[15px] font-bold">
                Regional Office No. 5
            </p>

            <p class="mb-0 text-[11px] italic">
                DOLE RO5 Bldg., Doña Aurora St., Old Albay, Legazpi City
            </p>

            <p class="mb-0 text-[10px] italic">
                ORD: 0981-461-8788&nbsp;&nbsp;
                TSSD: 0963-206-0008&nbsp;&nbsp;
                IMSD: 0912-330-4751
            </p>

            <p class="mb-0 my-[7px] text-[13px] text-black underline">
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


    <table
        class="w-full table-fixed border-collapse text-[12px] [&_th]:border [&_th]:border-[#222] [&_th]:bg-[#641D21] [&_th]:px-[3px] [&_th]:py-1 [&_th]:text-center [&_th]:font-bold [&_th]:text-white [&_th]:align-middle [&_td]:border [&_td]:border-[#222] [&_td]:px-[3px] [&_td]:py-1 [&_td]:text-center [&_td]:align-middle [&_td]:[overflow-wrap:anywhere] print-exact">
        <thead>
            <tr>
                <th>No.</th>
                <th>Project Code</th>
                <th>Project Title</th>
                <th>Location</th>
                <th>Beneficiaries</th>
                <th>Workdays</th>
                <th>Supplier</th>
                <th>Delivery Receipt</th>
                <th>Call-Off Number</th>
                <th>LS M</th>
                <th>LS L</th>
                <th>Total LS</th>
                <th>Bucket Hat</th>
                <th>Boots US9</th>
                <th>Boots US10</th>
                <th>Total Boots</th>
                <th>Gloves</th>
                <th>Mask</th>
                <th>Total PPE</th>
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
                @endphp

                <tr>
                    <td>
                        {{ $index + 1 }}
                    </td>

                    <td class="text-left">
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

                    <td>
                        {{ $callOff?->call_off_number ?? '—' }}
                    </td>

                    <td>
                        {{ number_format($ppe['long_sleeve_medium']) }}
                    </td>

                    <td>
                        {{ number_format($ppe['long_sleeve_large']) }}
                    </td>

                    <td class="bg-red-50 font-bold text-[#641D21] print-exact">
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

                    <td class="bg-red-50 font-bold text-[#641D21] print-exact">
                        {{ number_format($ppe['total_rubber_boots']) }}
                    </td>

                    <td>
                        {{ number_format($ppe['gloves']) }}
                    </td>

                    <td>
                        {{ number_format($ppe['mask']) }}
                    </td>

                    <td class="bg-gray-100 font-extrabold text-[#641D21] print-exact">
                        {{ number_format($ppe['total_ppe']) }}
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="19">
                        No project distribution records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="mt-7 w-full border-collapse [page-break-inside:avoid]">
        <tr>
            <td class="w-1/2 border-0 px-[35px] py-0 align-top">
                <div class="mb-7 text-[10px] font-bold">
                    Prepared by:
                </div>

                <div class="min-h-[18px] border-t border-black pt-[5px] text-center text-[9px] font-bold">
                </div>
            </td>

            <td class="w-1/2 border-0 px-[35px] py-0 align-top">
                <div class="mb-7 text-[10px] font-bold">
                    Reviewed by:
                </div>

                <div class="min-h-[18px] border-t border-black pt-[5px] text-center text-[9px] font-bold">
                    {{ $reviewedBy ?: ' ' }}
                </div>
            </td>
        </tr>
    </table>


</body>

</html>
