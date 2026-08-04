@once
    <style>
        .ppe-tracking-table {
            border-collapse: collapse;
            border: 1px solid #CBD5E1;
        }

        .ppe-tracking-table th {
            border: 1px solid #B8C6D1;
        }

        .ppe-tracking-table td {
            border: 1px solid #D8E1E8;
        }


        /* TSSD PPE Summary: freeze Delivery Receipts only after it reaches the left edge. */
        .ppe-summary-sticky-dr {
            position: sticky;
            left: 0;
            z-index: 20;
            background: #FFFFFF;
            box-shadow: 7px 0 12px -12px rgba(15, 23, 42, 0.85);
        }

        .ppe-tracking-table thead .ppe-summary-sticky-dr {
            z-index: 65;
            background: #2E628D;
            color: #FFFFFF;
        }

        .ppe-tracking-table tbody tr:hover > .ppe-summary-sticky-dr {
            background: #F7FBFD;
        }

        /* Fixed header clone used while the page itself scrolls vertically. */
        .ppe-summary-sticky-header {
            scrollbar-width: none;
            -ms-overflow-style: none;
            pointer-events: none;
            overscroll-behavior: none;
        }

        .ppe-summary-sticky-header::-webkit-scrollbar {
            display: none;
        }

        .ppe-summary-sticky-header .ppe-tracking-table {
            margin: 0;
            background: #2E628D;
        }

        .ppe-summary-sticky-header thead th {
            background: #2E628D;
            color: #FFFFFF;
        }
    </style>
@endonce
