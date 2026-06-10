<?php

return [
    'retention_days' => (int) env('TRANSFER_RETENTION_DAYS', 7),
    'max_upload_mb' => (int) env('TRANSFER_MAX_UPLOAD_MB', 2048),
    'chunk_size_mb' => (int) env('TRANSFER_CHUNK_SIZE_MB', 8),
    'allowed_mime_types' => array_filter(array_map('trim', explode(',', env('TRANSFER_ALLOWED_MIME_TYPES', '')))),
    'cleanup_incomplete_hours' => (int) env('TRANSFER_CLEANUP_INCOMPLETE_HOURS', 24),
    'admin_enabled' => (bool) env('TRANSFER_ADMIN_ENABLED', false),
    'admin_user' => env('TRANSFER_ADMIN_USER', 'admin'),
    'admin_password' => env('TRANSFER_ADMIN_PASSWORD'),
    'virus_scan_enabled' => (bool) env('TRANSFER_VIRUS_SCAN_ENABLED', false),
    'virus_scan_command' => env('TRANSFER_VIRUS_SCAN_COMMAND', 'clamdscan --no-summary'),
];
