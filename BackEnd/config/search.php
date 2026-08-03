<?php

return [
    'driver' => 'mysql_fulltext',
    'types' => ['products', 'crop_solutions', 'services', 'posts', 'projects'],
    'min_query_length' => 2,
    'max_query_length' => 100,
    'default_per_page' => 12,
    'max_per_page' => 24,
    'analytics_enabled' => (bool) env('SEARCH_ANALYTICS_ENABLED', false),
    'analytics_hash_key' => env('SEARCH_ANALYTICS_HASH_KEY') ?: env('APP_KEY'),
];
