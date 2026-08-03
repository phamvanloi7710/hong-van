<?php

namespace App\Domain\Seo;

use App\Domain\Localization\LocaleRegistry;
use App\Models\SeoMeta;

final readonly class SitemapGenerator
{
    public function __construct(private LocaleRegistry $locales, private SeoEntityRegistry $entities, private SitemapCache $cache) {}

    public function index(): string
    {
        return $this->cache->remember('index', function (): string {
            $shards = [];
            $urlCount = 0;
            foreach ($this->activeLocales() as $locale) {
                $shards[] = ['name' => 'home-'.$locale, 'lastmod' => now('UTC')->toAtomString()];
                $urlCount++;
                foreach ($this->entities->types() as $type) {
                    $count = count($this->entityRows($type, $locale));
                    if ($count > 0) {
                        $shards[] = ['name' => $type.'-'.$locale, 'lastmod' => now('UTC')->toAtomString()];
                        $urlCount += $count;
                    }
                }
            }
            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            foreach ($shards as $shard) {
                $xml .= '<sitemap><loc>'.$this->e(url('/sitemaps/'.$shard['name'].'.xml')).'</loc><lastmod>'.$this->e($shard['lastmod']).'</lastmod></sitemap>';
            }
            $xml .= '</sitemapindex>';
            $this->cache->recordHealth(['generated_at' => now('UTC')->toAtomString(), 'shard_count' => count($shards), 'url_count' => $urlCount]);

            return $xml;
        });
    }

    public function shard(string $name): ?string
    {
        if (! preg_match('/^([a-z_]+)-(vi|en|zh)$/', $name, $matches)) {
            return null;
        }
        [$unused, $type, $locale] = $matches;
        if (! in_array($locale, $this->activeLocales(), true)) {
            return null;
        }

        if ($type === 'home') {
            return $this->cache->remember($name, fn (): string => $this->urlset([$this->homeRow($locale)]));
        }
        if (! in_array($type, $this->entities->types(), true)) {
            return null;
        }
        $rows = $this->entityRows($type, $locale);

        return $rows === [] ? null : $this->cache->remember($name, fn (): string => $this->urlset($rows));
    }

    /** @return list<string> */
    public function shardNames(): array
    {
        $names = [];
        foreach ($this->activeLocales() as $locale) {
            $names[] = 'home-'.$locale;
            foreach ($this->entities->types() as $type) {
                if ($this->entityRows($type, $locale) !== []) {
                    $names[] = $type.'-'.$locale;
                }
            }
        }

        return $names;
    }

    /** @return list<string> */
    private function activeLocales(): array
    {
        return array_values(array_filter($this->locales->supportedLocales(), fn (string $locale): bool => $this->locales->isActive($locale)));
    }

    /** @return array{loc:string,lastmod:string,alternates:array<string,string>} */
    private function homeRow(string $locale): array
    {
        $default = $this->locales->defaultLocale();
        $alternates = [];
        foreach ($this->activeLocales() as $active) {
            $alternates[$active] = url($active === $default ? '/' : '/'.$active);
        }
        $alternates['x-default'] = url('/');

        return ['loc' => $alternates[$locale], 'lastmod' => now('UTC')->toAtomString(), 'alternates' => $alternates];
    }

    /** @return list<array{loc:string,lastmod:string,alternates:array<string,string>}> */
    private function entityRows(string $type, string $locale): array
    {
        $class = $this->entities->classFor($type);
        $publishedIds = $class::query()->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now('UTC')))
            ->pluck('id');
        if ($publishedIds->isEmpty()) {
            return [];
        }

        $all = SeoMeta::query()->where('seoable_type', $type)->whereIn('seoable_id', $publishedIds)
            ->where('robots_index', true)->whereNotNull('canonical_url')->get()->groupBy('seoable_id');
        $rows = [];
        foreach ($all as $entityId => $metas) {
            $current = $metas->firstWhere('locale', $locale);
            if (! $current instanceof SeoMeta || ! $this->isHttpUrl($current->canonical_url)) {
                continue;
            }
            $alternates = [];
            foreach ($this->activeLocales() as $active) {
                $meta = $metas->firstWhere('locale', $active);
                if ($meta instanceof SeoMeta && $this->isHttpUrl($meta->canonical_url)) {
                    $alternates[$active] = $meta->canonical_url;
                }
            }
            $defaultUrl = $alternates[$this->locales->defaultLocale()] ?? null;
            if (is_string($defaultUrl)) {
                $alternates['x-default'] = $defaultUrl;
            }
            $rows[] = ['loc' => $current->canonical_url, 'lastmod' => ($current->updated_at ?? now('UTC'))->toAtomString(), 'alternates' => $alternates];
        }

        return $rows;
    }

    /** @param list<array{loc:string,lastmod:string,alternates:array<string,string>}> $rows */
    private function urlset(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
        foreach ($rows as $row) {
            $xml .= '<url><loc>'.$this->e($row['loc']).'</loc><lastmod>'.$this->e($row['lastmod']).'</lastmod>';
            foreach ($row['alternates'] as $language => $href) {
                $xml .= '<xhtml:link rel="alternate" hreflang="'.$this->e($language).'" href="'.$this->e($href).'" />';
            }
            $xml .= '</url>';
        }

        return $xml.'</urlset>';
    }

    private function isHttpUrl(mixed $url): bool
    {
        return is_string($url) && filter_var($url, FILTER_VALIDATE_URL) !== false && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
