<?php

namespace App\Domain\PageBuilder;

final class PageBuilderCacheKeys
{
    public static function published(string $pagePublicId, string $locale, string $pageVersionPublicId, ?string $themeVersionPublicId): string
    {
        return implode(':', ['page-builder', 'published', $pagePublicId, $locale, $pageVersionPublicId, $themeVersionPublicId ?? 'theme-none']);
    }

    /** @return list<string> */
    public static function tags(string $pagePublicId, string $pageVersionPublicId, ?string $themeVersionPublicId): array
    {
        $tags = ['page-builder', 'page:'.$pagePublicId, 'page-version:'.$pageVersionPublicId];
        if ($themeVersionPublicId !== null) {
            $tags[] = 'theme-version:'.$themeVersionPublicId;
        }

        return $tags;
    }

    /** @return array<string, mixed> */
    public static function metadata(): array
    {
        return [
            'publishedKey' => 'page-builder:published:{pagePublicId}:{locale}:{pageVersionPublicId}:{themeVersionPublicId|theme-none}',
            'tags' => ['page-builder', 'page:{pagePublicId}', 'page-version:{pageVersionPublicId}', 'theme-version:{themeVersionPublicId}'],
        ];
    }
}
