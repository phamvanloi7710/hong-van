<?php

return [
    'request_id_header' => 'X-Request-ID',

    'default_locale' => env('API_DEFAULT_LOCALE', 'vi'),

    'locales' => ['vi', 'en', 'zh'],

    'rate_limit_per_minute' => (int) env('API_RATE_LIMIT_PER_MINUTE', 60),

    'auth_rate_limits' => [
        'login_per_minute' => (int) env('AUTH_LOGIN_RATE_LIMIT_PER_MINUTE', 5),
        'password_per_minute' => (int) env('AUTH_PASSWORD_RATE_LIMIT_PER_MINUTE', 3),
    ],

    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
    ],
];
