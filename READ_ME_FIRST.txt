TUPAD PPE INVENTORY SYSTEM
LONGSLEEVE NAME STANDARDIZATION OVERLAY

INSTALLATION
1. Back up your project and database.
2. Extract this ZIP directly into the Laravel project root.
3. Allow Windows to merge folders and replace the included files.
4. Run:

   php artisan migrate
   php artisan optimize:clear

WHAT THIS REVISION DOES
- Changes visible Long Sleeve / Long Sleeves wording to Longsleeve.
- Changes the uppercase print/table wording to LONGSLEEVE.
- Updates validation messages, dashboards, table headers, print pages,
  item forms, seeders, and distribution displays.
- Converts existing item records named Long Sleeve, Long Sleeves,
  Longsleeve, or Longsleeves to the canonical database name Longsleeve.
- Keeps compatibility with older records while the migration is applied.
- Automatically stores future spacing/plural variants as Longsleeve.

IMPORTANT
- Internal database/form keys such as long_sleeve_medium are intentionally
  unchanged. They are technical identifiers and changing them would break
  existing migrations, records, requests, and calculations.
- No npm build is required for this revision.
