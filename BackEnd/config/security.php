<?php

$csv = static fn (string $value): array => array_values(array_filter(
    array_map('trim', explode(',', $value)),
    static fn (string $item): bool => $item !== '',
));
$trustedHosts = array_map(
    static fn (string $host): string => '^'.preg_quote($host, '/').'$',
    $csv((string) env('TRUSTED_HOSTS', 'hongvan.local,localhost,127.0.0.1')),
);

return [
    'trusted_hosts' => $trustedHosts,
    'trusted_proxies' => $csv((string) env('TRUSTED_PROXIES', '')),

    'headers' => [
        'content_security_policy' => "default-src 'self'; base-uri 'self'; object-src 'none'; form-action 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:; connect-src 'self'; frame-src 'self'; frame-ancestors 'none'",
        'preview_content_security_policy' => "default-src 'self'; base-uri 'self'; object-src 'none'; form-action 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'",
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'hsts_max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
    ],

    'rate_limits' => [
        'public_forms_per_minute' => (int) env('PUBLIC_FORM_RATE_LIMIT_PER_MINUTE', 10),
        'public_search_per_minute' => (int) env('PUBLIC_SEARCH_RATE_LIMIT_PER_MINUTE', 30),
        'uploads_per_minute' => (int) env('UPLOAD_RATE_LIMIT_PER_MINUTE', 20),
        'preview_sessions_per_minute' => (int) env('PREVIEW_SESSION_RATE_LIMIT_PER_MINUTE', 10),
        'preview_views_per_minute' => (int) env('PREVIEW_VIEW_RATE_LIMIT_PER_MINUTE', 60),
    ],

    'audit' => [
        'retention_days' => (int) env('AUDIT_RETENTION_DAYS', 365),
        'max_string_length' => 2000,
    ],

    'logging' => [
        'channel' => env('SECURITY_LOG_CHANNEL', 'security'),
        'retention_days' => (int) env('SECURITY_LOG_RETENTION_DAYS', 90),
    ],
];
