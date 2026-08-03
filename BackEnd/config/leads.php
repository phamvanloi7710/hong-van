<?php

return [
    'deduplicate_minutes' => (int) env('LEAD_DEDUPLICATE_MINUTES', 15),
    'retention_days' => (int) env('LEAD_RETENTION_DAYS', 365),
    'export_limit' => (int) env('LEAD_EXPORT_LIMIT', 5000),
    'privacy_policy_version' => env('LEAD_PRIVACY_POLICY_VERSION', '2026-08'),
    'transitions' => [
        'new' => ['contacted', 'spam', 'archived'],
        'contacted' => ['qualified', 'processing', 'spam', 'archived'],
        'qualified' => ['processing', 'done', 'archived'],
        'processing' => ['done', 'archived'],
        'done' => ['archived'],
        'spam' => ['archived'],
        'archived' => [],
    ],
];
