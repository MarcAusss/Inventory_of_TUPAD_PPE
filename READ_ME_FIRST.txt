TUPAD TSSD PO Date-Only Display Overlay

1. Extract this ZIP directly into the Laravel project root.
2. Allow folders to merge and replace the included file.
3. Run:

   php artisan optimize:clear

Result:
The PO Date field at /tssd/distributions/create will display only YYYY-MM-DD.
Example: 2026-07-26 instead of 2026-07-26T00:00:00.000000Z.

No migration and no npm build are required.
