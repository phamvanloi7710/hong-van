<?php

return [
    'cache_ttl_seconds' => max(15, (int) env('DASHBOARD_CACHE_TTL_SECONDS', 60)),
    'sync_export_limit' => max(1, (int) env('DASHBOARD_SYNC_EXPORT_LIMIT', 1000)),
    'report_retention_hours' => max(1, (int) env('DASHBOARD_REPORT_RETENTION_HOURS', 24)),
    'timezone' => env('DASHBOARD_TIMEZONE', 'Asia/Ho_Chi_Minh'),
];
