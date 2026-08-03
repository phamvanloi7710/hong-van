<?php

namespace App\Domain\PageBuilder;

use App\Domain\CropSolutions\CropSolutionDataSource;
use App\Domain\Posts\PostDataSource;
use App\Domain\Services\ServiceDataSource;
use App\Domain\Transportation\TransportationDataSource;
use App\Domain\Warehouses\WarehouseDataSource;
use App\Models\Certification;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;

final readonly class DataSourceRegistry
{
    public const MAX_LIMIT = 24;

    public function __construct(
        private CropSolutionDataSource $cropSolutions,
        private ServiceDataSource $services,
        private TransportationDataSource $transportation,
        private WarehouseDataSource $warehouses,
        private PostDataSource $posts,
    ) {}

    /** @return list<array<string,mixed>> */
    public function metadata(): array
    {
        return array_values(array_map(static fn (array $definition): array => [
            'key' => $definition['key'], 'filters' => array_keys($definition['filters']),
            'sorts' => $definition['sorts'], 'presets' => $definition['presets'],
            'maxLimit' => self::MAX_LIMIT, 'cacheTags' => $definition['cacheTags'],
        ], $this->definitions()));
    }

    /** @param array<string,mixed> $binding */
    public function resolve(array $binding, PageRenderOptions $options): DataSourceResult
    {
        $binding = $this->normalize($binding);
        $source = $binding['source'];
        $filters = $binding['filters'];
        $limit = $binding['limit'];
        $featured = ($filters['featured'] ?? false) === true || $binding['preset'] === 'featured';

        $items = match ($source) {
            'products' => $this->products($options->locale, $limit, $filters, $binding['sort']),
            'product_categories' => $this->productCategories($options->locale, $limit, $filters),
            'crop_solutions' => $this->cropSolutions->resolve($options->locale, $limit, $this->optionalString($filters, 'cropId')),
            'services' => $this->services->resolve($options->locale, $limit, $this->optionalString($filters, 'categoryId'), $featured),
            'fleet' => array_slice($this->transportation->resolve($options->locale, $limit)['vehicles'], 0, $limit),
            'routes' => array_slice($this->transportation->resolve($options->locale, $limit)['routes'], 0, $limit),
            'warehouses' => array_slice($this->warehouses->resolve($options->locale, $limit), 0, $limit),
            'stats' => $this->stats(),
            'partners' => $this->showcase(Partner::query(), $options->locale, $limit, $featured, 'name'),
            'certifications' => $this->showcase(Certification::query(), $options->locale, $limit, $featured, 'name'),
            'projects' => $this->showcase(Project::query(), $options->locale, $limit, $featured, 'title'),
            'posts' => $this->postItems($options->locale, $limit, $filters),
            default => throw new \LogicException("Unregistered Page Builder data source [{$source}]."),
        };
        $definition = $this->definitions()[$source];

        if ($items === [] && $options->mayUseSampleData()) {
            return new DataSourceResult($this->sampleItems($source, $options->locale), $definition['cacheTags'], true);
        }

        return new DataSourceResult($items, $definition['cacheTags']);
    }

    /**
     * @param  array<string, mixed>  $binding
     * @return array{source: string, filters: array<string, mixed>, sort: string, limit: int, preset: string}
     */
    public function normalize(array $binding): array
    {
        $source = $binding['source'] ?? null;
        $definitions = $this->definitions();
        if (! is_string($source) || ! isset($definitions[$source])) {
            $this->fail('source', 'unknown_data_source');
        }
        $definition = $definitions[$source];
        $filters = $binding['filters'] ?? [];
        if (! is_array($filters) || ($filters !== [] && array_is_list($filters))) {
            $this->fail('filters', 'object');
        }
        foreach ($filters as $key => $value) {
            $expected = $definition['filters'][$key] ?? null;
            if ($expected === null || ($expected === 'boolean' && ! is_bool($value)) || ($expected === 'string' && ! is_string($value))) {
                $this->fail('filters.'.(string) $key, 'unknown_filter');
            }
        }
        $sort = $binding['sort'] ?? 'default';
        $preset = $binding['preset'] ?? 'default';
        $limit = $binding['limit'] ?? 8;
        if (! is_string($sort) || ! in_array($sort, $definition['sorts'], true)) {
            $this->fail('sort', 'unknown_sort');
        }
        if (! is_string($preset) || ! in_array($preset, $definition['presets'], true)) {
            $this->fail('preset', 'unknown_preset');
        }
        if (! is_int($limit) || $limit < 1 || $limit > self::MAX_LIMIT) {
            $this->fail('limit', 'limit');
        }

        return compact('source', 'filters', 'sort', 'limit', 'preset');
    }

    /** @return array<string,array{key:string,filters:array<string,string>,sorts:list<string>,presets:list<string>,cacheTags:list<string>}> */
    private function definitions(): array
    {
        $base = static fn (string $key, array $filters = [], array $sorts = ['default'], array $presets = ['default']): array => compact('key', 'filters', 'sorts', 'presets') + ['cacheTags' => ['page-builder', 'data:'.$key]];

        return [
            'products' => $base('products', ['featured' => 'boolean', 'categoryId' => 'string'], ['default', 'newest', 'oldest'], ['default', 'featured']),
            'product_categories' => $base('product_categories', ['featured' => 'boolean', 'rootOnly' => 'boolean'], ['default'], ['default', 'featured']),
            'crop_solutions' => $base('crop_solutions', ['cropId' => 'string']),
            'services' => $base('services', ['featured' => 'boolean', 'categoryId' => 'string'], ['default'], ['default', 'featured']),
            'fleet' => $base('fleet'), 'routes' => $base('routes'), 'warehouses' => $base('warehouses'),
            'stats' => $base('stats', [], ['default'], ['default']),
            'partners' => $base('partners', ['featured' => 'boolean'], ['default'], ['default', 'featured']),
            'certifications' => $base('certifications', ['featured' => 'boolean'], ['default'], ['default', 'featured']),
            'projects' => $base('projects', ['featured' => 'boolean'], ['default'], ['default', 'featured']),
            'posts' => $base('posts', ['featured' => 'boolean', 'categorySlug' => 'string', 'tagSlug' => 'string'], ['default', 'newest'], ['default', 'featured']),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    private function products(string $locale, int $limit, array $filters, string $sort): array
    {
        $query = Product::query()->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now('UTC'))
            ->where(fn (Builder $q) => $q->whereNull('unpublished_at')->orWhere('unpublished_at', '>', now('UTC')))
            ->when(($filters['featured'] ?? false) === true, fn (Builder $q) => $q->where('is_featured', true))
            ->when($this->optionalString($filters, 'categoryId'), fn (Builder $q, string $id) => $q->whereHas('category', fn (Builder $category) => $category->where('public_id', $id)))
            ->with(['translations', 'category.translations', 'media'])->limit($limit);
        match ($sort) {
            'newest' => $query->orderByDesc('published_at'), 'oldest' => $query->orderBy('published_at'), default => $query->orderByDesc('is_featured')->orderByDesc('published_at')
        };

        return $query->get()->map(function (Product $item) use ($locale): array {
            $translation = $this->translation($item->translations, $locale);

            return ['public_id' => $item->public_id, 'title' => $translation?->getAttribute('name'), 'slug' => $translation?->getAttribute('slug'), 'summary' => $translation?->getAttribute('short_description'), 'is_featured' => $item->is_featured];
        })->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    private function productCategories(string $locale, int $limit, array $filters): array
    {
        return ProductCategory::query()->where('is_active', true)
            ->when(($filters['featured'] ?? false) === true, fn (Builder $q) => $q->where('is_featured', true))
            ->when(($filters['rootOnly'] ?? false) === true, fn (Builder $q) => $q->whereNull('parent_id'))
            ->with('translations')->orderBy('sort_order')->limit($limit)->get()->map(function (ProductCategory $item) use ($locale): array {
                $translation = $this->translation($item->translations, $locale);

                return ['public_id' => $item->public_id, 'title' => $translation?->getAttribute('name'), 'slug' => $translation?->getAttribute('slug'), 'summary' => $translation?->getAttribute('summary'), 'is_featured' => $item->is_featured];
            })->all();
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return list<array<string, mixed>>
     */
    private function showcase(Builder $query, string $locale, int $limit, bool $featured, string $titleField): array
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now('UTC'))
            ->when($featured, fn (Builder $q) => $q->where('is_featured', true))->with('translations')->orderBy('sort_order')->limit($limit)->get()
            ->map(function (Model $item) use ($locale, $titleField): array {
                $translation = $this->translation($item->getRelation('translations'), $locale);

                return ['public_id' => $item->getAttribute('public_id'), 'title' => $translation?->getAttribute($titleField), 'slug' => $translation?->getAttribute('slug'), 'summary' => $translation?->getAttribute('summary') ?? $translation?->getAttribute('description'), 'is_featured' => (bool) $item->getAttribute('is_featured')];
            })->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    private function postItems(string $locale, int $limit, array $filters): array
    {
        $items = $this->posts->listing($locale, $limit, $this->optionalString($filters, 'categorySlug'), $this->optionalString($filters, 'tagSlug'))->items();

        $result = [];
        foreach ($items as $item) {
            if (($filters['featured'] ?? false) === true && ! $item->is_featured) {
                continue;
            }
            $translation = $this->posts->translation($item, $locale);
            $publishedAt = $item->getAttribute('published_at');
            $result[] = ['public_id' => $item->public_id, 'title' => $translation?->title, 'slug' => $translation?->slug, 'summary' => $translation?->excerpt, 'published_at' => $publishedAt instanceof DateTimeInterface ? $publishedAt->format(DATE_ATOM) : null, 'is_featured' => $item->is_featured];
        }

        return $result;
    }

    /** @return list<array<string,mixed>> */
    private function stats(): array
    {
        $published = static fn (Builder $query): Builder => $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now('UTC'));

        return [
            ['key' => 'products', 'value' => $published(Product::query())->count()],
            ['key' => 'partners', 'value' => $published(Partner::query())->count()],
            ['key' => 'certifications', 'value' => $published(Certification::query())->count()],
            ['key' => 'projects', 'value' => $published(Project::query())->count()],
        ];
    }

    /** @return list<array<string,mixed>> */
    private function sampleItems(string $source, string $locale): array
    {
        return $source === 'stats'
            ? [['key' => 'sample', 'value' => 12]]
            : [['public_id' => 'preview-sample', 'title' => Lang::get('page_builder.preview.sample_title', [], $locale), 'summary' => Lang::get('page_builder.preview.sample_summary', [], $locale)]];
    }

    /** @param Collection<int, Model> $translations */
    private function translation(Collection $translations, string $locale): ?Model
    {
        return $translations->firstWhere('locale', $locale) ?? $translations->firstWhere('locale', 'vi') ?? $translations->first();
    }

    /** @param array<string,mixed> $values */
    private function optionalString(array $values, string $key): ?string
    {
        $value = $values[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function fail(string $path, string $key): never
    {
        throw ValidationException::withMessages(['bindings.'.$path => [__('page_builder.validation.'.$key)]]);
    }
}
