TUPAD PPE TABLE UI + GROUPED TOTAL REVISION
===========================================

INSTALLATION
------------
1. Back up your Laravel project.
2. Extract this ZIP directly into the Laravel project root.
3. Allow Windows to merge folders and replace the included files.
4. Open PowerShell in the project root and run:

   php artisan optimize:clear
   npm run build

   For development, you may use:

   npm run dev

NO DATABASE MIGRATION IS REQUIRED.

CHANGES INCLUDED
----------------
1. Main table header rows remain #2E628D.
2. Secondary/grouped header rows use exactly #2e628dde.
   Examples: M, L, Total, US9, US10, Total.
3. Plain table data text is black throughout the dashboard.
   Existing badges, links, and icon action controls can retain their own
   explicit colors.
4. Table actions remain fixed icon-only controls.
5. Hovering an action does not reveal an expanding text label.
6. Native tooltips and accessible aria-labels remain.
7. In the Accounting Provincial Inventory and Accounting Inventory Ledger
   consolidated PPE tables:
   - M and L remain visible for each province row.
   - US9 and US10 remain visible for each province row.
   - Consolidated Total leaves M, L, US9, and US10 empty.
   - Only Long Sleeves Total and Rubber Boots Total are displayed for those
     grouped items in the consolidated row.
8. Total PPE does not double-count grouped items.

TOTAL PPE RULE
--------------
Each province row uses:

Long Sleeves Total
+ Bucket Hat
+ Rubber Boots Total
+ Gloves
+ Mask
= Total PPE

M and L are used to calculate Long Sleeves Total, but they are not added again.
US9 and US10 are used to calculate Rubber Boots Total, but they are not added
again.

Example using 470 for every size/item:
Long Sleeves Total = 470 + 470 = 940
Rubber Boots Total = 470 + 470 = 940
Total PPE = 940 + 470 + 940 + 470 + 470 = 3,290

FILES INCLUDED
--------------
resources/css/app.css
resources/js/app.js
resources/views/components/po_dashboard_layout.blade.php
resources/views/accounting/inventory-ledger/index.blade.php
resources/views/accounting/provincial-inventory/index.blade.php
tailwind.config.js
