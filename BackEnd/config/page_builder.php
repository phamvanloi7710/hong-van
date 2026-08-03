<?php

return [
    'preview' => [
        'cache_store' => env('PAGE_BUILDER_PREVIEW_CACHE_STORE', 'redis'),
        'ttl_seconds' => max(60, (int) env('PAGE_BUILDER_PREVIEW_TTL_SECONDS', 300)),
        'message_schema_version' => 1,
    ],
];
