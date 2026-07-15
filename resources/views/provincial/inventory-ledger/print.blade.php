<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Inventory of Supplies Distributed -
        {{ $selectedDeliveryReceipt->dr_number }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page {
            size: A3 landscape;
            margin: 8mm;
        }

        @media print {
            body {
                padding: 0 !important;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            tr,
            td,
            th {
                page-break-inside: avoid;
            }

            .print-color-exact {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body class="m-0 bg-white p-[10mm] font-sans text-[8px] text-black print:p-0">

    @php
        $allocation = $selectedDeliveryReceipt->provinceDistribution;
        $batch = $allocation?->distributionBatch;
        $callOff = $batch?->callOff;
        $purchaseOrder = $batch?->purchaseOrder;
        $supplier = $purchaseOrder?->supplier;
        $ppeItemIds = [1, 2, 3, 4, 5, 6, 7];
    @endphp

    {{-- =========================================================
        SCREEN-ONLY CONTROLS
    ========================================================== --}}
    <div class="mb-4 flex justify-end gap-2 rounded-lg border border-slate-300 bg-slate-50 p-3 print:hidden">
        <a href="{{ route('provincial.inventory-ledger.index', [
            'delivery_receipt_id' => $selectedDeliveryReceipt->id,
            'year' => $year,
        ]) }}"
            class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-[13px] font-bold text-slate-700 no-underline transition hover:bg-slate-100">
            Back to Ledger
        </a>

        <button type="button" onclick="window.print()"
            class="inline-flex items-center justify-center rounded-md bg-[#970C13] px-4 py-2 text-[13px] font-bold text-white transition hover:bg-[#641D21]">
            Print Report
        </button>
    </div>

    {{-- =========================================================
        LETTERHEAD
    ========================================================== --}}
    <div class="flex justify-center pl-28">
        <img src="{{ asset('images/print/dole_logo.webp') }}" alt="DOLE Logo"
            class="max-h-[85px] w-[120px] object-contain" onerror="this.style.display='none'">

        <div class="text-center">
            <p class="m-0 text-center text-[14px] font-normal">
                Republic of the Philippines
            </p>

            <p class="mb-0 text-[17px] font-extrabold">
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

            <p class="mb-0 mt-[7px] text-[13px] text-black underline">
                ro5@dole.gov.ph
            </p>

            <p class="mb-0 mt-[7px] text-[11px] font-bold text-black">
                {{ now()->format('F d, Y') }}
            </p>
        </div>
        <img src="{{ asset('images/print/Bagong_Pilipinas.png') }}" alt="Bagong Pilipinas"
            class="max-h-[82px] w-[105px] object-contain " onerror="this.style.display='none'">

        <img src="{{ asset('images/print/iso-bureau-veritas.jpg') }}" alt="ISO Bureau Veritas"
            class="max-h-[78px] w-[150px] object-contain" onerror="this.style.display='none'">
    </div>

    {{-- =========================================================
        SELECTED RECEIPT INFORMATION
    ========================================================== --}}
    <table class="mb-[9px] w-full">
        <tr>
            <td class="w-1/4 p-[5px] align-top">
                <span class="mb-0.5 block font-bold uppercase">
                    Provincial Office
                </span>
                {{ $provinceName }}
            </td>
        </tr>
    </table>

    {{-- =========================================================
        INVENTORY TABLE
    ========================================================== --}}
    <table class="w-full table-fixed border-collapse text-[6.5px]">
        <thead>
            <tr>
                <th rowspan="3"
                    class="print-color-exact w-[24px] border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center align-middle font-bold text-white">
                    No.
                </th>

                <th rowspan="3"
                    class="print-color-exact w-[67px] border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center align-middle font-bold text-white">
                    Call-Off Number
                </th>

                <th rowspan="3"
                    class="print-color-exact w-[73px] border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center align-middle font-bold text-white">
                    Name of Supplier
                </th>

                <th rowspan="3"
                    class="print-color-exact w-[62px] border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center align-middle font-bold text-white">
                    Delivery Receipt
                </th>

                <th rowspan="3"
                    class="print-color-exact w-[52px] border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center align-middle font-bold text-white">
                    Date of Delivery
                </th>

                <th rowspan="3"
                    class="print-color-exact w-[65px] border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center align-middle font-bold text-white">
                    Project Code
                </th>

                <th rowspan="3"
                    class="print-color-exact w-[67px] border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center align-middle font-bold text-white">
                    Location
                </th>

                <th rowspan="3"
                    class="print-color-exact w-[44px] border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center align-middle font-bold text-white">
                    No. of Beneficiaries
                </th>

                <th rowspan="3"
                    class="print-color-exact w-[35px] border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center align-middle font-bold text-white">
                    No. of Days
                </th>

                <th colspan="7"
                    class="print-color-exact border border-[#333] bg-[#970C13] px-0.5 py-[3px] text-center align-middle font-bold text-white">
                    Beginning Inventory
                </th>

                <th colspan="7"
                    class="print-color-exact border border-[#333] bg-[#C51017] px-0.5 py-[3px] text-center align-middle font-bold text-white">
                    Actual Distribution
                </th>

                <th colspan="7"
                    class="print-color-exact border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center align-middle font-bold text-white">
                    Ending Inventory
                </th>
            </tr>

            <tr>
                <th colspan="2"
                    class="print-color-exact border border-[#333] bg-[#970C13] px-0.5 py-[3px] text-center font-bold text-white">
                    Long Sleeve</th>
                <th rowspan="2"
                    class="print-color-exact border border-[#333] bg-[#970C13] px-0.5 py-[3px] text-center font-bold text-white">
                    Bucket Hat</th>
                <th colspan="2"
                    class="print-color-exact border border-[#333] bg-[#970C13] px-0.5 py-[3px] text-center font-bold text-white">
                    Rubber Boots</th>
                <th rowspan="2"
                    class="print-color-exact border border-[#333] bg-[#970C13] px-0.5 py-[3px] text-center font-bold text-white">
                    Gloves</th>
                <th rowspan="2"
                    class="print-color-exact border border-[#333] bg-[#970C13] px-0.5 py-[3px] text-center font-bold text-white">
                    Mask</th>

                <th colspan="2"
                    class="print-color-exact border border-[#333] bg-[#C51017] px-0.5 py-[3px] text-center font-bold text-white">
                    Long Sleeve</th>
                <th rowspan="2"
                    class="print-color-exact border border-[#333] bg-[#C51017] px-0.5 py-[3px] text-center font-bold text-white">
                    Bucket Hat</th>
                <th colspan="2"
                    class="print-color-exact border border-[#333] bg-[#C51017] px-0.5 py-[3px] text-center font-bold text-white">
                    Rubber Boots</th>
                <th rowspan="2"
                    class="print-color-exact border border-[#333] bg-[#C51017] px-0.5 py-[3px] text-center font-bold text-white">
                    Gloves</th>
                <th rowspan="2"
                    class="print-color-exact border border-[#333] bg-[#C51017] px-0.5 py-[3px] text-center font-bold text-white">
                    Mask</th>

                <th colspan="2"
                    class="print-color-exact border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center font-bold text-white">
                    Long Sleeve</th>
                <th rowspan="2"
                    class="print-color-exact border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center font-bold text-white">
                    Bucket Hat</th>
                <th colspan="2"
                    class="print-color-exact border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center font-bold text-white">
                    Rubber Boots</th>
                <th rowspan="2"
                    class="print-color-exact border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center font-bold text-white">
                    Gloves</th>
                <th rowspan="2"
                    class="print-color-exact border border-[#333] bg-[#641D21] px-0.5 py-[3px] text-center font-bold text-white">
                    Mask</th>
            </tr>

            <tr>
                @foreach (['Medium', 'Large', 'US9', 'US10'] as $label)
                    <th
                        class="print-color-exact border border-[#333] bg-[#DF979B] px-0.5 py-[3px] text-center font-bold text-[#641D21]">
                        {{ $label }}
                    </th>
                @endforeach

                @foreach (['Medium', 'Large', 'US9', 'US10'] as $label)
                    <th
                        class="print-color-exact border border-[#333] bg-[#DF979B] px-0.5 py-[3px] text-center font-bold text-[#641D21]">
                        {{ $label }}
                    </th>
                @endforeach

                @foreach (['Medium', 'Large', 'US9', 'US10'] as $label)
                    <th
                        class="print-color-exact border border-[#333] bg-[#DF979B] px-0.5 py-[3px] text-center font-bold text-[#641D21]">
                        {{ $label }}
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse($rows as $index => $row)
                @php
                    $beginning = $row['beginning'] ?? [];
                    $actual = $row['actual'] ?? [];
                    $ending = $row['ending'] ?? [];
                    $isOpeningRow = empty($row['supply_designation_id']);
                @endphp

                <tr>
                    <td class="border border-[#333] px-0.5 py-[3px] text-center align-middle break-words">
                        {{ $index + 1 }}
                    </td>

                    <td class="border border-[#333] px-0.5 py-[3px] text-center align-middle font-bold break-words">
                        {{ $row['call_off_number'] ?? '—' }}
                    </td>

                    <td class="border border-[#333] px-0.5 py-[3px] text-left align-middle break-words">
                        {{ $row['supplier_name'] ?? '—' }}
                    </td>

                    <td class="border border-[#333] px-0.5 py-[3px] text-center align-middle font-bold break-words">
                        {{ $row['delivery_receipt_number'] ?? '—' }}
                    </td>

                    <td class="border border-[#333] px-0.5 py-[3px] text-center align-middle break-words">
                        {{ isset($row['delivery_date']) && $row['delivery_date'] ? $row['delivery_date']->format('M d, Y') : '—' }}
                    </td>

                    <td class="border border-[#333] px-0.5 py-[3px] text-left align-middle break-words">
                        @if ($isOpeningRow)
                            No Project Yet
                        @else
                            <strong>
                                {{ $row['project_code'] ?? '—' }}
                            </strong>

                            <span class="mt-0.5 block text-[5.8px] font-normal">
                                {{ $row['project_title'] ?? '—' }}
                            </span>
                        @endif
                    </td>

                    <td class="border border-[#333] px-0.5 py-[3px] text-left align-middle break-words">
                        {{ $row['location'] ?? '—' }}
                    </td>

                    <td class="border border-[#333] px-0.5 py-[3px] text-center align-middle break-words">
                        {{ number_format((int) ($row['number_of_beneficiaries'] ?? 0)) }}
                    </td>

                    <td class="border border-[#333] px-0.5 py-[3px] text-center align-middle break-words">
                        {{ number_format((int) ($row['number_of_days'] ?? 0)) }}
                    </td>

                    @foreach ($ppeItemIds as $itemId)
                        <td
                            class="print-color-exact border border-[#333] bg-slate-50 px-0.5 py-[3px] text-center align-middle break-words">
                            {{ number_format((int) ($beginning[$itemId] ?? 0)) }}
                        </td>
                    @endforeach

                    @foreach ($ppeItemIds as $itemId)
                        <td
                            class="print-color-exact border border-[#333] bg-red-50 px-0.5 py-[3px] text-center align-middle font-bold break-words">
                            {{ number_format((int) ($actual[$itemId] ?? 0)) }}
                        </td>
                    @endforeach

                    @foreach ($ppeItemIds as $itemId)
                        <td
                            class="print-color-exact border border-[#333] bg-neutral-50 px-0.5 py-[3px] text-center align-middle font-bold break-words">
                            {{ number_format((int) ($ending[$itemId] ?? 0)) }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="30" class="border border-[#333] px-2 py-6 text-center text-[8px]">
                        No project distribution records were found for this Delivery Receipt.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>


    <div class="mt-[14px] text-right text-[6.5px] text-[#444]">
        Printed: {{ $printedAt->format('F d, Y h:i A') }}
    </div>
</body>

</html>
