<?php

return [
    'request_id_header' => 'X-Request-ID',

    'default_locale' => env('API_DEFAULT_LOCALE', 'vi'),

    'locales' => ['vi', 'en'],

    'rate_limit_per_minute' => (int) env('API_RATE_LIMIT_PER_MINUTE', 60),

    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
    ],
];
