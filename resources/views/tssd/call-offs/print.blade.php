<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Province Distribution Summary -
        {{ $callOff->call_off_number }}
    </title>

    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #000;
            background: #fff;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }

        .print-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 12px;
        }

        .print-actions button {
            border: 0;
            border-radius: 6px;
            padding: 9px 18px;
            font-weight: 700;
            cursor: pointer;
        }

        .print-button {
            background: #970C13;
            color: #fff;
        }

        .close-button {
            background: #e5e7eb;
            color: #111827;
        }

        .report {
            width: 100%;
        }

        .letterhead {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .letterhead td {
            border: none;
            vertical-align: middle;
        }

        .logo-cell {
            width: 18%;
            text-align: center;
        }

        .center-cell {
            width: 57%;
            text-align: center;
        }

        .right-cell {
            width: 25%;
            text-align: center;
        }

        .logo-placeholder {
            min-height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .republic {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .department {
            font-size: 18px;
            font-weight: 700;
        }

        .regional-office {
            margin-top: 18px;
            font-size: 15px;
            font-weight: 700;
        }

        .address {
            margin-top: 18px;
            font-size: 11px;
            font-style: italic;
        }

        .contacts {
            margin-top: 8px;
            font-size: 11px;
            font-style: italic;
        }

        .email {
            margin-top: 7px;
            font-size: 13px;
            text-decoration: underline;
        }

        .report-title {
            margin-top: 18px;
            text-align: center;
        }

        .report-title h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .report-title p {
            margin: 5px 0 0;
            font-size: 12px;
            font-weight: 700;
        }

        .report-meta {
            width: 100%;
            margin: 18px 0 10px;
            border-collapse: collapse;
        }

        .report-meta td {
            width: 25%;
            padding: 4px 8px;
            vertical-align: top;
        }

        .meta-label {
            display: block;
            margin-bottom: 3px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .meta-value {
            font-size: 10px;
            font-weight: 700;
        }

        .distribution-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .distribution-table th,
        .distribution-table td {
            border: 1px solid #000;
            padding: 5px 3px;
        }

        .distribution-table thead th {
            text-align: center;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .distribution-table tbody td {
            font-size: 8px;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: 700;
        }

        .footer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 100px;
            margin-top: 45px;
        }

        .signature {
            min-height: 70px;
        }

        .signature-label {
            font-weight: 700;
        }

        .signature-line {
            margin-top: 45px;
            border-top: 1px solid #000;
            padding-top: 4px;
            text-align: center;
            font-weight: 700;
        }

        @media print {
            .print-actions {
                display: none !important;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    @php
        $batch = $callOff->distributionBatch;

        $purchaseOrder =
            $batch?->purchaseOrder;
    @endphp

    <div class="print-actions">
        <button
            type="button"
            class="close-button"
            onclick="window.close()"
        >
            Close
        </button>

        <button
            type="button"
            class="print-button"
            onclick="window.print()"
        >
            Print
        </button>
    </div>

    <main class="report">

        {{-- =====================================================
        DOLE HEADER
        ====================================================== --}}

        <table class="letterhead">
            <tr>
                <td class="logo-cell">
                    <div class="logo-placeholder">
                        DOLE LOGO
                    </div>
                </td>

                <td class="center-cell">
                    <div class="republic">
                        Republic of the Philippines
                    </div>

                    <div class="department">
                        DEPARTMENT OF LABOR AND EMPLOYMENT
                    </div>

                    <div class="regional-office">
                        Regional Office No. 5
                    </div>

                    <div class="address">
                        DOLE RO5 Bldg., Doña Aurora St.,
                        Old Albay, Legazpi City
                    </div>

                    <div class="contacts">
                        ORD: 0981-461-8788
                        TSSD: 0963-206-0008
                        IMSD: 0912-330-4751
                    </div>

                    <div class="email">
                        ro5@dole.gov.ph
                    </div>
                </td>

                <td class="right-cell">
                    <div class="logo-placeholder">
                        BAGONG PILIPINAS / ISO
                    </div>
                </td>
            </tr>
        </table>

        {{-- =====================================================
        REPORT TITLE
        ====================================================== --}}

        <section class="report-title">
            <h1>
                Province Distribution Summary
            </h1>

            <p>
                {{ now()->format('F d, Y') }}
            </p>
        </section>

        {{-- =====================================================
        REPORT INFORMATION
        ====================================================== --}}

        <table class="report-meta">
            <tr>
                <td>
                    <span class="meta-label">
                        Call-Off Number
                    </span>

                    <span class="meta-value">
                        {{ $callOff->call_off_number }}
                    </span>
                </td>

                <td>
                    <span class="meta-label">
                        Purchase Order
                    </span>

                    <span class="meta-value">
                        {{ $purchaseOrder?->po_number ?? '—' }}
                    </span>
                </td>

                <td>
                    <span class="meta-label">
                        Supplier
                    </span>

                    <span class="meta-value">
                        {{
                            $purchaseOrder
                                ?->supplier
                                ?->supplier_name
                            ?? '—'
                        }}
                    </span>
                </td>

                <td>
                    <span class="meta-label">
                        Distribution Date
                    </span>

                    <span class="meta-value">
                        {{
                            $batch
                                ?->distribution_date
                                ?->format('F d, Y')
                            ?? '—'
                        }}
                    </span>
                </td>
            </tr>
        </table>

        {{-- =====================================================
        PROVINCE DISTRIBUTION TABLE
        ====================================================== --}}

        <table class="distribution-table">

            <thead>

                <tr>
                    <th rowspan="2">
                        Province
                    </th>

                    <th rowspan="2">
                        Delivery Date
                    </th>

                    <th rowspan="2">
                        Place of Delivery
                    </th>

                    <th colspan="3">
                        Long Sleeves
                    </th>

                    <th rowspan="2">
                        Bucket Hat
                    </th>

                    <th colspan="3">
                        Rubber Boots
                    </th>

                    <th rowspan="2">
                        Gloves
                    </th>

                    <th rowspan="2">
                        Mask
                    </th>
                </tr>

                <tr>
                    <th>M</th>
                    <th>L</th>
                    <th>Total</th>

                    <th>US9</th>
                    <th>US10</th>
                    <th>Total</th>
                </tr>

            </thead>

            <tbody>

                @foreach(
                    $batch?->provinceDistributions
                        ?? collect()
                    as $allocation
                )
                    @php
                        $items =
                            $allocation->items
                            ?? collect();

                        $lsm = $items
                            ->filter(
                                fn ($row) =>
                                    $row->item
                                    && $row->item->item_name
                                        === 'Long Sleeve'
                                    && $row->item->label
                                        === 'Medium'
                            )
                            ->sum('quantity');

                        $lsl = $items
                            ->filter(
                                fn ($row) =>
                                    $row->item
                                    && $row->item->item_name
                                        === 'Long Sleeve'
                                    && $row->item->label
                                        === 'Large'
                            )
                            ->sum('quantity');

                        $bucket = $items
                            ->filter(
                                fn ($row) =>
                                    $row->item
                                    && $row->item->item_name
                                        === 'Bucket Hat'
                            )
                            ->sum('quantity');

                        $us9 = $items
                            ->filter(
                                fn ($row) =>
                                    $row->item
                                    && $row->item->item_name
                                        === 'Rubber Boots'
                                    && $row->item->label
                                        === 'US9'
                            )
                            ->sum('quantity');

                        $us10 = $items
                            ->filter(
                                fn ($row) =>
                                    $row->item
                                    && $row->item->item_name
                                        === 'Rubber Boots'
                                    && $row->item->label
                                        === 'US10'
                            )
                            ->sum('quantity');

                        $gloves = $items
                            ->filter(
                                fn ($row) =>
                                    $row->item
                                    && $row->item->item_name
                                        === 'Hand Gloves'
                            )
                            ->sum('quantity');

                        $mask = $items
                            ->filter(
                                fn ($row) =>
                                    $row->item
                                    && $row->item->item_name
                                        === 'Mask'
                            )
                            ->sum('quantity');
                    @endphp

                    <tr>
                        <td class="text-left font-bold">
                            {{
                                $allocation
                                    ->province
                                    ?->name
                                ?? '—'
                            }}
                        </td>

                        <td class="text-center">
                            {{
                                $allocation
                                    ->scheduled_delivery_date
                                    ?->format('M d, Y')
                                ?? '—'
                            }}
                        </td>

                        <td class="text-left">
                            {{
                                $allocation
                                    ->place_of_delivery
                                ?? '—'
                            }}
                        </td>

                        <td class="text-center">
                            {{ number_format($lsm) }}
                        </td>

                        <td class="text-center">
                            {{ number_format($lsl) }}
                        </td>

                        <td class="text-center font-bold">
                            {{
                                number_format(
                                    $lsm + $lsl
                                )
                            }}
                        </td>

                        <td class="text-center">
                            {{ number_format($bucket) }}
                        </td>

                        <td class="text-center">
                            {{ number_format($us9) }}
                        </td>

                        <td class="text-center">
                            {{ number_format($us10) }}
                        </td>

                        <td class="text-center font-bold">
                            {{
                                number_format(
                                    $us9 + $us10
                                )
                            }}
                        </td>

                        <td class="text-center">
                            {{ number_format($gloves) }}
                        </td>

                        <td class="text-center">
                            {{ number_format($mask) }}
                        </td>
                    </tr>

                @endforeach

            </tbody>

        </table>

        {{-- =====================================================
        SIGNATURE FOOTER
        ====================================================== --}}

        <section class="footer">

            <div class="signature">
                <div class="signature-label">
                    Prepared by:
                </div>

                <div class="signature-line">
                    Name and Signature
                </div>
            </div>

            <div class="signature">
                <div class="signature-label">
                    Reviewed by:
                </div>

                <div class="signature-line">
                    Name and Signature
                </div>
            </div>

        </section>

    </main>

</body>

</html>