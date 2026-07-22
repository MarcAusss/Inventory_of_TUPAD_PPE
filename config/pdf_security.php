<?php

return [
    /* New PDF templates are stored outside the public web directory. */
    'disk' => env('PDF_TEMPLATE_DISK', 'local'),

    /* Existing projects used the public disk, so old records remain readable. */
    'legacy_disk' => env('PDF_TEMPLATE_LEGACY_DISK', 'public'),

    /* Keep this conservative to reduce parser exhaustion attacks. */
    'max_pages' => (int) env('PDF_TEMPLATE_MAX_PAGES', 50),

    /* Must match or be lower than the Form Request upload limit. */
    'max_bytes' => (int) env('PDF_TEMPLATE_MAX_BYTES', 20 * 1024 * 1024),
];
