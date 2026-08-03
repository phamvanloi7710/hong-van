<?php

return [
    'disk' => env('MEDIA_DISK', 'public'),
    'visibility' => env('MEDIA_VISIBILITY', 'public'),
    'queue' => env('MEDIA_QUEUE', 'media'),
    'max_upload_kb' => (int) env('MEDIA_MAX_UPLOAD_KB', 10240),
    'trash_retention_days' => (int) env('MEDIA_TRASH_RETENTION_DAYS', 30),
    'allowed_extensions' => [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'avif' => ['image/avif'],
        'pdf' => ['application/pdf'],
    ],
    'variants' => [
        'thumbnail' => ['width' => 320, 'height' => 320],
        'preview' => ['width' => 1280, 'height' => 1280],
    ],
    'variant_formats' => ['webp', 'avif'],
    'usage_owner_types' => ['product', 'page', 'post', 'settings', 'crop_category', 'crop', 'crop_stage', 'crop_solution', 'service', 'vehicle'],
];
