<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $callOff->call_off_number ?? 'Call-Off' }} Letter
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /*
        |--------------------------------------------------------------------------
        | Page setup
        |--------------------------------------------------------------------------
        */

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top left,
                    rgba(85, 183, 217, 0.14),
                    transparent 32%),
                #eef4f7;
            color: #0f172a;
            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }

        button,
        a {
            font: inherit;
        }

        /*
        |--------------------------------------------------------------------------
        | Screen toolbar
        |--------------------------------------------------------------------------
        */

        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #dbe5ea;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 6px 24px rgba(20, 58, 82, 0.08);
            backdrop-filter: blur(12px);
        }

        .print-toolbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: min(100%, 1450px);
            margin: 0 auto;
            padding: 14px 24px;
        }

        .toolbar-heading {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 12px;
        }

        .toolbar-icon {
            display: flex;
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #e9f5fa;
            color: #247ba0;
        }

        .toolbar-icon svg {
            width: 22px;
            height: 22px;
        }

        .toolbar-text {
            min-width: 0;
        }

        .toolbar-label {
            margin: 0;
            color: #143a52;
            font-size: 15px;
            font-weight: 800;
        }

        .toolbar-subtitle {
            overflow: hidden;
            margin: 4px 0 0;
            color: #64748b;
            font-size: 12px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .toolbar-actions {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            gap: 10px;
        }

        .toolbar-button {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            border-radius: 12px;
            padding: 10px 17px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            transition:
                background 160ms ease,
                border-color 160ms ease,
                color 160ms ease,
                transform 160ms ease;
            cursor: pointer;
        }

        .toolbar-button:hover {
            transform: translateY(-1px);
        }

        .toolbar-button svg {
            width: 17px;
            height: 17px;
        }

        .toolbar-button-back {
            border-color: #b7d6e6;
            background: #ffffff;
            color: #247ba0;
        }

        .toolbar-button-back:hover {
            border-color: #55b7d9;
            background: #f7fbfd;
        }

        .toolbar-button-print {
            background: #339dcb;
            color: #ffffff;
            box-shadow: 0 8px 18px rgba(51, 157, 203, 0.22);
        }

        .toolbar-button-print:hover {
            background: #247ba0;
        }

        /*
        |--------------------------------------------------------------------------
        | Screen preview
        |--------------------------------------------------------------------------
        */

        .preview-wrapper {
            padding: 28px 18px 50px;
        }

        .preview-information {
            display: flex;
            width: 210mm;
            margin: 0 auto 12px;
            align-items: center;
            justify-content: space-between;
            color: #64748b;
            font-size: 11px;
        }

        .preview-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #b7d6e6;
            border-radius: 999px;
            background: #ffffff;
            padding: 6px 10px;
            color: #247ba0;
            font-weight: 700;
        }

        .paper {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            overflow: hidden;
            background: #ffffff;
            box-shadow:
                0 4px 8px rgba(15, 23, 42, 0.08),
                0 24px 60px rgba(20, 58, 82, 0.15);
        }

        .paper-content {
            position: relative;
            min-height: 297mm;
            padding: 9mm 11mm 10mm;
        }

        /*
        |--------------------------------------------------------------------------
        | Letterhead
        |--------------------------------------------------------------------------
        */

        .letterhead {
            display: grid;
            grid-template-columns: 75px minmax(0, 1fr) 75px;
            align-items: center;
            border-bottom: 1px solid #94a3b8;
            padding-bottom: 10px;
        }

        .letterhead-side {
            min-height: 60px;
        }

        .letterhead-center {
            text-align: center;
            line-height: 1.16;
        }

        .letterhead-republic {
            margin: 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 10.5pt;
            font-weight: 400;
        }

        .letterhead-department {
            margin: 3px 0 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 13pt;
            font-weight: 800;
            letter-spacing: 0.1px;
        }

        .letterhead-region {
            margin: 3px 0 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            font-weight: 700;
        }

        .letterhead-address {
            margin: 5px 0 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 8pt;
            font-style: italic;
        }

        .letterhead-contact {
            margin: 3px 0 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 7.8pt;
            font-style: italic;
        }

        .letterhead-email {
            margin: 3px 0 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 8pt;
            text-decoration: underline;
        }

        /*
        |--------------------------------------------------------------------------
        | Letter content
        |--------------------------------------------------------------------------
        */

        .letter-content {
            font-family: "Times New Roman", Times, serif;
            font-size: 10.5pt;
            line-height: 1.35;
        }

        .document-date {
            margin-top: 23px;
        }

        .recipient-block {
            margin-top: 20px;
        }

        .recipient-name {
            margin: 0;
            font-weight: 800;
        }

        .recipient-line {
            margin: 1px 0 0;
        }

        .attention-block {
            display: grid;
            grid-template-columns: 68px minmax(0, 1fr);
            margin-top: 12px;
            margin-left: 35px;
        }

        .attention-label {
            font-weight: 700;
        }

        .attention-name {
            font-weight: 700;
        }

        .attention-position {
            display: block;
            margin-top: 1px;
            font-weight: 400;
        }

        .salutation {
            margin-top: 18px;
        }

        .greeting {
            margin-top: 13px;
        }

        .request-paragraph {
            margin: 13px 0 0;
            text-align: justify;
            text-indent: 36px;
        }

        /*
        |--------------------------------------------------------------------------
        | Distribution table
        |--------------------------------------------------------------------------
        */

        .table-wrapper {
            margin-top: 14px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 6.6pt;
            line-height: 1.15;
        }

        .details-table col.province-column {
            width: 12%;
        }

        .details-table col.place-column {
            width: 25%;
        }

        .details-table col.date-column {
            width: 12%;
        }

        .details-table col.quantity-column {
            width: 7.28%;
        }

        .details-table th,
        .details-table td {
            border: 0.7px solid #000000;
            padding: 4px 3px;
            vertical-align: middle;
        }

        .details-table th {
            background: #0284C7;
            color: #ffffff;
            font-size: 6.4pt;
            font-weight: 800;
            text-align: center;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .details-table thead tr:nth-child(2) th {
            background: #075985;
        }

        .details-table tbody tr:not(.total-row):nth-child(even) {
            background: #F0F9FF;
        }

        .details-table td {
            overflow-wrap: anywhere;
        }

        .province-cell {
            font-weight: 600;
            text-align: left;
        }

        .place-cell {
            text-align: left;
        }

        .date-cell {
            text-align: center;
        }

        .quantity-cell {
            text-align: center;
        }

        .empty-table-cell {
            padding: 18px !important;
            color: #475569;
            text-align: center;
        }

        .total-row td {
            background: #E0F2FE;
            color: #075985;
            font-weight: 800;
            text-align: center;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        /*
        |--------------------------------------------------------------------------
        | Closing and signature
        |--------------------------------------------------------------------------
        */

        .closing-block {
            margin-top: 20px;
        }

        .very-truly {
            margin-top: 19px;
        }

        .signature-block {
            margin-top: 43px;
        }

        .signature-name {
            margin: 0;
            font-weight: 800;
        }

        .signature-position {
            margin: 2px 0 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Print behavior
        |--------------------------------------------------------------------------
        */

        @media print {

            html,
            body {
                width: 210mm;
                min-height: 297mm;
                background: #ffffff !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .print-toolbar,
            .preview-information {
                display: none !important;
            }

            .preview-wrapper {
                margin: 0;
                padding: 0;
            }

            .paper {
                width: 100%;
                min-height: auto;
                margin: 0;
                overflow: visible;
                box-shadow: none;
            }

            .paper-content {
                min-height: auto;
                padding: 0;
            }

            .details-table {
                page-break-inside: auto;
            }

            .details-table thead {
                display: table-header-group;
            }

            .details-table tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .signature-block {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive screen preview
        |--------------------------------------------------------------------------
        */

        @media screen and (max-width: 900px) {
            .print-toolbar-inner {
                align-items: flex-start;
                gap: 15px;
                padding: 12px 14px;
            }

            .toolbar-subtitle {
                display: none;
            }

            .toolbar-actions {
                gap: 7px;
            }

            .toolbar-button {
                padding: 10px 12px;
            }

            .toolbar-button-text {
                display: none;
            }

            .preview-wrapper {
                overflow-x: auto;
                padding: 20px 12px 40px;
            }

            .preview-information {
                width: 210mm;
            }
        }
    </style>
</head>

<body class="m-0 min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">

    {{-- Screen toolbar --}}
    <header class="print-toolbar sticky top-0 z-[100] border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur-xl">
        <div class="print-toolbar-inner mx-auto flex w-full max-w-[1450px] items-center justify-between px-6 py-3.5">

            <div class="toolbar-heading flex min-w-0 items-center gap-3">

                <div
                    class="toolbar-icon flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-xl bg-sky-50 text-[#0284C7]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                        stroke="currentColor">

                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-8.25A3.375 3.375 0 0 0 4.5 11.625v2.625m15 0A2.25 2.25 0 0 1 17.25 16.5H6.75A2.25 2.25 0 0 1 4.5 14.25m15 0v4.125A1.125 1.125 0 0 1 18.375 19.5H5.625A1.125 1.125 0 0 1 4.5 18.375V14.25m3.75-6V4.5h7.5v3.75M8.25 16.5v3h7.5v-3" />
                    </svg>
                </div>

                <div class="toolbar-text min-w-0">
                    <p class="toolbar-label m-0 text-[15px] font-extrabold text-slate-800">
                        Call-Off Request Letter
                    </p>

                    <p class="toolbar-subtitle mt-1 truncate text-xs text-slate-500">
                        {{ $callOff->call_off_number ?? 'No Call-Off Number' }}
                        ·
                        NEFA No.
                        {{ $purchaseOrder?->nefa_number ?? 'Not available' }}
                    </p>
                </div>

            </div>

            <div class="toolbar-actions flex shrink-0 items-center gap-2.5">

                <a href="{{ route('tssd.call-off-letters.edit', $callOff) }}"
                    class="toolbar-button toolbar-button-back inline-flex min-h-[42px] items-center justify-center gap-2 rounded-xl border border-sky-200 bg-white px-4 py-2.5 text-[13px] font-extrabold text-[#0284C7] transition hover:-translate-y-px hover:border-sky-300 hover:bg-sky-50">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">

                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>

                    <span class="toolbar-button-text">
                        Back
                    </span>
                </a>

                <button type="button" onclick="window.print()"
                    class="toolbar-button toolbar-button-print inline-flex min-h-[42px] items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#075985] via-[#0284C7] to-[#38BDF8] px-4 py-2.5 text-[13px] font-extrabold text-white shadow-lg shadow-sky-900/20 transition hover:-translate-y-px hover:opacity-90">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">

                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3.75h10.5v4.5H6.75v-4.5ZM6.75 15.75h10.5v4.5H6.75v-4.5ZM4.5 8.25h15a2.25 2.25 0 0 1 2.25 2.25v4.125a1.125 1.125 0 0 1-1.125 1.125H17.25m-10.5 0H3.375a1.125 1.125 0 0 1-1.125-1.125V10.5A2.25 2.25 0 0 1 4.5 8.25Z" />
                    </svg>

                    <span class="toolbar-button-text">
                        Print
                    </span>
                </button>

            </div>

        </div>
    </header>

    {{-- Print preview --}}
    <div class="preview-wrapper overflow-x-auto px-[18px] pb-[50px] pt-7">

        <div
            class="preview-information mx-auto mb-3 flex w-[210mm] items-center justify-between text-[11px] text-slate-500">

            <span
                class="preview-badge inline-flex items-center gap-1.5 rounded-full border border-sky-200 bg-white px-2.5 py-1.5 font-bold text-[#0284C7]">
                A4 Portrait
            </span>

            <span>
                The toolbar will not appear on the printed document.
            </span>

        </div>

        <main class="paper relative mx-auto min-h-[297mm] w-[210mm] overflow-hidden bg-white shadow-2xl">

            <div class="paper-content relative min-h-[297mm] px-[11mm] pb-[10mm] pt-[9mm]">

                {{-- Letterhead --}}
                <header class="letterhead">

                    <div class="letterhead-side"></div>

                    <div class="letterhead-center">

                        <p class="letterhead-republic">
                            Republic of the Philippines
                        </p>

                        <p class="letterhead-department">
                            DEPARTMENT OF LABOR AND EMPLOYMENT
                        </p>

                        <p class="letterhead-region">
                            Regional Office No. 5
                        </p>

                        <p class="letterhead-address">
                            DOLE RO5 Bldg., Doña Aurora St.,
                            Old Albay, Legazpi City
                        </p>

                        <p class="letterhead-contact">
                            ORD: 0981-461-8788&nbsp;&nbsp;
                            TSSD: 0963-206-0008&nbsp;&nbsp;
                            IMSD: 0912-330-4751
                        </p>

                        <p class="letterhead-email">
                            ro5@dole.gov.ph
                        </p>

                    </div>

                    <div class="letterhead-side"></div>

                </header>

                <div class="letter-content">

                    {{-- Document date --}}
                    <div class="document-date">
                        {{ $callOff->call_off_date?->format('F j, Y') ?? now()->format('F j, Y') }}
                    </div>

                    {{-- Recipient --}}
                    <section class="recipient-block">

                        <p class="recipient-name">
                            CHERRY B. MOSATALLA, CPA
                        </p>

                        <p class="recipient-line">
                            IMSD Chief
                        </p>

                        <p class="recipient-line">
                            This Office
                        </p>

                        <div class="attention-block">

                            <div class="attention-label">
                                Attention:
                            </div>

                            <div>
                                <span class="attention-name">
                                    ANTONETTE M. LEGSON
                                </span>

                                <span class="attention-position">
                                    Supply Officer
                                </span>
                            </div>

                        </div>

                    </section>

                    <div class="salutation">
                        Ma’am:
                    </div>

                    <div class="greeting">
                        Greetings!
                    </div>

                    {{-- Automated request paragraph --}}
                    <p class="request-paragraph">
                        We respectfully request for the
                        {{ strtolower($callOffLabel) }}
                        of TUPAD personal protective equipment
                        (PPE) under NEFA No.
                        <strong>
                            {{ $purchaseOrder?->nefa_number ?? '—' }}
                        </strong>
                        with project title
                        “<strong>{{ $nefaTitle }}</strong>”
                        amounting to
                        <strong>
                            P{{ number_format((float) ($purchaseOrder?->total_amount ?? 0), 2) }}
                        </strong>.
                        Below are the details:
                    </p>

                    {{-- Provincial distribution table --}}
                    <div class="table-wrapper mt-3.5 overflow-hidden rounded-lg border border-slate-300">

                        <table
                            class="details-table w-full table-fixed border-collapse font-sans text-[6.6pt] leading-[1.15]">

                            <colgroup>
                                <col class="province-column">
                                <col class="place-column">
                                <col class="date-column">

                                @for ($column = 0; $column < 6; $column++)
                                    <col class="quantity-column">
                                @endfor
                            </colgroup>

                            <thead>

                                <tr>
                                    <th rowspan="2">
                                        PROVINCE
                                    </th>

                                    <th rowspan="2">
                                        PLACE OF DELIVERY
                                    </th>

                                    <th rowspan="2">
                                        DATE OF DELIVERY
                                    </th>

                                    <th colspan="2">
                                        LONG SLEEVES
                                    </th>

                                    <th rowspan="2">
                                        BUCKET HAT
                                    </th>

                                    <th colspan="2">
                                        RUBBER BOOTS
                                    </th>

                                    <th rowspan="2">
                                        GLOVES
                                    </th>
                                </tr>

                                <tr>
                                    <th>M</th>
                                    <th>L</th>
                                    <th>UK 9</th>
                                    <th>UK 10</th>
                                </tr>

                            </thead>

                            <tbody>

                                @forelse ($rows as $row)
                                    <tr>

                                        <td class="province-cell">
                                            {{ $row['province'] }}
                                        </td>

                                        <td class="place-cell">
                                            {{ $row['place_of_delivery'] }}
                                        </td>

                                        <td class="date-cell">
                                            @if ($row['delivery_date'])
                                                {{ \Illuminate\Support\Carbon::parse($row['delivery_date'])->format('F j, Y') }}
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <td class="quantity-cell">
                                            {{ number_format($row['long_sleeve_medium']) }}
                                        </td>

                                        <td class="quantity-cell">
                                            {{ number_format($row['long_sleeve_large']) }}
                                        </td>

                                        <td class="quantity-cell">
                                            {{ number_format($row['bucket_hat']) }}
                                        </td>

                                        <td class="quantity-cell">
                                            {{ number_format($row['rubber_boots_us9']) }}
                                        </td>

                                        <td class="quantity-cell">
                                            {{ number_format($row['rubber_boots_us10']) }}
                                        </td>

                                        <td class="quantity-cell">
                                            {{ number_format($row['hand_gloves']) }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="9" class="empty-table-cell">

                                            No provincial distributions
                                            were found for this Call-Off.

                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="total-row bg-sky-100 text-[#075985]">

                                    <td colspan="3">
                                        TOTAL
                                    </td>

                                    <td>
                                        {{ number_format($totals['long_sleeve_medium']) }}
                                    </td>

                                    <td>
                                        {{ number_format($totals['long_sleeve_large']) }}
                                    </td>

                                    <td>
                                        {{ number_format($totals['bucket_hat']) }}
                                    </td>

                                    <td>
                                        {{ number_format($totals['rubber_boots_us9']) }}
                                    </td>

                                    <td>
                                        {{ number_format($totals['rubber_boots_us10']) }}
                                    </td>

                                    <td>
                                        {{ number_format($totals['hand_gloves']) }}
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                    {{-- Closing --}}
                    <section class="closing-block">

                        <div>
                            Thank you very much.
                        </div>

                        <div class="very-truly">
                            Very truly yours,
                        </div>

                    </section>

                    {{-- Signature --}}
                    <section class="signature-block">

                        <p class="signature-name">
                            CHING B. BANANIA
                        </p>

                        <p class="signature-position">
                            TSSD Chief/OIC Assistant Regional Director
                        </p>

                    </section>

                </div>

            </div>

        </main>

        <footer class="fixed bottom-0 left-0 w-full print:fixed print:bottom-0">
            <img src="{{ asset('images/footer.png') }}" alt="Footer" class="w-full h-auto object-cover">
        </footer>
    </div>

</body>

</html>
