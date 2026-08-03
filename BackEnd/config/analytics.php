<?php

return [
    'cache_key' => 'hongvan.analytics.configuration.v1',
    'cookie' => [
        'name' => 'hongvan_consent',
        'same_site' => 'lax',
    ],
    'providers' => [
        'google_analytics_4' => [
            'identifier_pattern' => '/^G-[A-Z0-9]{4,32}$/',
            'script_hosts' => ['https://www.googletagmanager.com'],
            'connect_hosts' => ['https://www.google-analytics.com', 'https://region1.google-analytics.com'],
            'image_hosts' => ['https://www.google-analytics.com'],
        ],
        'google_tag_manager' => [
            'identifier_pattern' => '/^GTM-[A-Z0-9]{4,32}$/',
            'script_hosts' => ['https://www.googletagmanager.com'],
            'connect_hosts' => ['https://www.google-analytics.com', 'https://region1.google-analytics.com'],
            'image_hosts' => ['https://www.googletagmanager.com', 'https://www.google-analytics.com'],
        ],
        'plausible' => [
            'identifier_pattern' => '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i',
            'script_hosts' => ['https://plausible.io'],
            'connect_hosts' => ['https://plausible.io'],
            'image_hosts' => [],
        ],
    ],
];
