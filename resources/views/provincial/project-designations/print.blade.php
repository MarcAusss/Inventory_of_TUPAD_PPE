<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $reportTitle }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 10mm;
            color: #000;
            background: #fff;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px;
        }

        .no-print {
            margin-bottom: 14px;
            text-align: right;
        }

        .button {
            display: inline-block;
            padding: 9px 16px;
            border: 0;
            border-radius: 6px;
            color: #fff;
            background: #970c13;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .report-header {
            margin-bottom: 14px;
            text-align: center;
        }

        .report-header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .report-header p {
            margin: 5px 0 0;
            font-size: 10px;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 6.8px;
        }

        table.report th,
        table.report td {
            padding: 4px 3px;
            border: 1px solid #222;
            text-align: center;
            vertical-align: middle;
            overflow-wrap: anywhere;
        }

        table.report th {
            color: #fff;
            background: #641d21;
            font-weight: 700;
        }

        table.report td.text-left {
            text-align: left;
        }

        table.report td.total {
            font-weight: 700;
            background: #f3f4f6;
        }

        .signatures {
            width: 100%;
            margin-top: 28px;
            border-collapse: collapse;
        }

        .signatures td {
            width: 50%;
            padding: 0 35px;
            border: 0;
        }

        .signature-label {
            margin-bottom: 28px;
            font-size: 10px;
            font-weight: 700;
        }

        .signature-line {
            padding-top: 5px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 9px;
            font-weight: 700;
        }

        @page {
            size: A3 landscape;
            margin: 8mm;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }

            table.report th,
            table.report td.total {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    @php
        $itemQuantity = function (
            $designation,
            int $itemId
        ): int {
            return (int) $designation
                ->items
                ->where('item_id', $itemId)
                ->sum('quantity');
        };
    @endphp

    <div class="no-print">
        <button
            type="button"
            class="button"
            onclick="window.print()"
        >
            Print Report
        </button>
    </div>

    <header class="report-header">
        <h1>Project PPE Distribution Report</h1>

        <p>{{ $reportTitle }}</p>

        <p>
            Provincial Office:
            <strong>{{ $provinceName }}</strong>
        </p>

        <p>
            Printed:
            {{ $printedAt->format('F d, Y h:i A') }}
        </p>
    </header>

    <table class="report">
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
                    $allocation =
                        $designation
                            ->provinceDistribution;

                    $batch =
                        $allocation
                            ?->distributionBatch;

                    $callOff =
                        $batch?->callOff;

                    $supplier =
                        $batch
                            ?->purchaseOrder
                            ?->supplier;

                    $receipt =
                        $designation
                            ->deliveryReceipt;

                    $lsM =
                        $itemQuantity(
                            $designation,
                            1
                        );

                    $lsL =
                        $itemQuantity(
                            $designation,
                            2
                        );

                    $bucketHat =
                        $itemQuantity(
                            $designation,
                            3
                        );

                    $bootsUs9 =
                        $itemQuantity(
                            $designation,
                            4
                        );

                    $bootsUs10 =
                        $itemQuantity(
                            $designation,
                            5
                        );

                    $gloves =
                        $itemQuantity(
                            $designation,
                            6
                        );

                    $mask =
                        $itemQuantity(
                            $designation,
                            7
                        );

                    $totalLs =
                        $lsM + $lsL;

                    $totalBoots =
                        $bootsUs9
                        + $bootsUs10;

                    $totalPpe =
                        $totalLs
                        + $bucketHat
                        + $totalBoots
                        + $gloves
                        + $mask;
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>

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
                        {{
                            number_format(
                                $designation
                                    ->number_of_beneficiaries
                            )
                        }}
                    </td>

                    <td>
                        {{
                            number_format(
                                $designation
                                    ->number_of_days
                            )
                        }}
                    </td>

                    <td class="text-left">
                        {{
                            $supplier?->supplier_name
                            ?? '—'
                        }}
                    </td>

                    <td>
                        {{ $receipt?->dr_number ?? '—' }}
                    </td>

                    <td>
                        {{
                            $callOff?->call_off_number
                            ?? '—'
                        }}
                    </td>

                    <td>{{ number_format($lsM) }}</td>
                    <td>{{ number_format($lsL) }}</td>
                    <td class="total">{{ number_format($totalLs) }}</td>
                    <td>{{ number_format($bucketHat) }}</td>
                    <td>{{ number_format($bootsUs9) }}</td>
                    <td>{{ number_format($bootsUs10) }}</td>
                    <td class="total">{{ number_format($totalBoots) }}</td>
                    <td>{{ number_format($gloves) }}</td>
                    <td>{{ number_format($mask) }}</td>
                    <td class="total">{{ number_format($totalPpe) }}</td>
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

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-label">
                    Prepared by:
                </div>

                <div class="signature-line">
                    {{ $preparedBy }}
                </div>
            </td>

            <td>
                <div class="signature-label">
                    Reviewed by:
                </div>

                <div class="signature-line">
                    {{ $reviewedBy }}
                </div>
            </td>
        </tr>
    </table>
</body>
</html>