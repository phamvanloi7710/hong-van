<?php

namespace App\Domain\Dashboard;

final class NotificationDeepLink
{
    /** @var list<string> */
    private const ALLOWED_SECTIONS = [
        'dashboard', 'products', 'crop-solutions', 'services', 'transportation', 'warehouses',
        'leads', 'content-pages', 'showcase', 'seo', 'identity', 'settings', 'localization', 'audit', 'media',
    ];

    public function sanitize(mixed $value): ?string
    {
        if (! is_string($value) || $value === '' || str_contains($value, '\\') || str_contains($value, '..') || str_starts_with($value, '//')) {
            return null;
        }

        $parts = parse_url($value);
        if ($parts === false || isset($parts['scheme']) || isset($parts['host']) || isset($parts['user']) || isset($parts['port'])) {
            return null;
        }

        $path = $parts['path'] ?? '';
        if (preg_match('#^/admin/([a-z0-9-]+)(?:/.*)?$#', $path, $matches) !== 1
            || ! in_array($matches[1], self::ALLOWED_SECTIONS, true)) {
            return null;
        }

        $result = $path;
        if (isset($parts['query']) && preg_match('/^[A-Za-z0-9_=&%+.,:-]+$/', $parts['query']) === 1) {
            $result .= '?'.$parts['query'];
        }

        return $result;
    }
}
