<?php

namespace App\Domain\Search;

use App\Models\CropSolution;
use App\Models\Post;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class RelatedContentQuery
{
    /** @return list<array{type: string, public_id: string, title: ?string, slug: ?string}> */
    public function resolve(string $type, string $publicId, string $locale, int $limit = 4): array
    {
        $safeLimit = max(1, min($limit, 12));

        return match ($type) {
            'products' => $this->products($publicId, $locale, $safeLimit),
            'crop_solutions' => $this->cropSolutions($publicId, $locale, $safeLimit),
            'services' => $this->services($publicId, $locale, $safeLimit),
            'posts' => $this->posts($publicId, $locale, $safeLimit),
            'projects' => [],
            default => [],
        };
    }

    /** @return list<array{type: string, public_id: string, title: ?string, slug: ?string}> */
    private function products(string $publicId, string $locale, int $limit): array
    {
        $source = $this->published(Product::query())->where('public_id', $publicId)->first();
        if ($source === null) {
            return [];
        }

        $categoryId = $source->getAttribute('product_category_id');
        $tagIds = $source->tags()->pluck('hongvan_product_tags.id');
        if (! is_int($categoryId) && $tagIds->isEmpty()) {
            return [];
        }
        $query = $this->published(Product::query())->whereKeyNot($source->getKey());
        $query->where(static function (Builder $related) use ($categoryId, $tagIds): void {
            if (is_int($categoryId)) {
                $related->where('product_category_id', $categoryId);
            }
            if ($tagIds->isNotEmpty()) {
                if (is_int($categoryId)) {
                    $related->orWhereHas('tags', static fn (Builder $tags) => $tags->whereIn('hongvan_product_tags.id', $tagIds));
                } else {
                    $related->whereHas('tags', static fn (Builder $tags) => $tags->whereIn('hongvan_product_tags.id', $tagIds));
                }
            }
        });

        return $this->format($query->with('translations')->limit($limit)->get()->all(), 'products', $locale, 'name');
    }

    /** @return list<array{type: string, public_id: string, title: ?string, slug: ?string}> */
    private function cropSolutions(string $publicId, string $locale, int $limit): array
    {
        $source = $this->published(CropSolution::query())->where('public_id', $publicId)->first();
        if ($source === null) {
            return [];
        }
        $cropId = $source->getAttribute('crop_id');
        $stageId = $source->getAttribute('crop_stage_id');
        $query = $this->published(CropSolution::query())->whereKeyNot($source->getKey());
        $query->where(static function (Builder $related) use ($cropId, $stageId): void {
            $related->where('crop_id', $cropId);
            if (is_int($stageId)) {
                $related->orWhere('crop_stage_id', $stageId);
            }
        });

        return $this->format($query->with('translations')->limit($limit)->get()->all(), 'crop_solutions', $locale, 'title');
    }

    /** @return list<array{type: string, public_id: string, title: ?string, slug: ?string}> */
    private function services(string $publicId, string $locale, int $limit): array
    {
        $source = $this->published(Service::query())->where('public_id', $publicId)->first();
        if ($source === null || $source->service_category_id === null) {
            return [];
        }

        return $this->format($this->published(Service::query())->whereKeyNot($source->getKey())
            ->where('service_category_id', $source->service_category_id)->with('translations')->limit($limit)->get()->all(), 'services', $locale, 'name');
    }

    /** @return list<array{type: string, public_id: string, title: ?string, slug: ?string}> */
    private function posts(string $publicId, string $locale, int $limit): array
    {
        $source = $this->published(Post::query())->where('public_id', $publicId)->first();
        if ($source === null) {
            return [];
        }
        $tagIds = $source->tags()->pluck('hongvan_post_tags.id');
        if ($source->post_category_id === null && $tagIds->isEmpty()) {
            return [];
        }
        $query = $this->published(Post::query())->whereKeyNot($source->getKey());
        $query->where(static function (Builder $related) use ($source, $tagIds): void {
            if ($source->post_category_id !== null) {
                $related->where('post_category_id', $source->post_category_id);
            }
            if ($tagIds->isNotEmpty()) {
                $method = $source->post_category_id === null ? 'whereHas' : 'orWhereHas';
                $related->{$method}('tags', static fn (Builder $tags) => $tags->whereIn('hongvan_post_tags.id', $tagIds));
            }
        });

        return $this->format($query->with('translations')->limit($limit)->get()->all(), 'posts', $locale, 'title');
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function published(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now('UTC'))
            ->whereNull('deleted_at')->when(
                in_array($query->getModel()->getTable(), ['hongvan_products', 'hongvan_crop_solutions', 'hongvan_services', 'hongvan_posts'], true),
                static fn (Builder $builder) => $builder->where(static fn (Builder $window) => $window->whereNull('unpublished_at')->orWhere('unpublished_at', '>', now('UTC'))),
            )->orderByDesc('is_featured')->orderByDesc('published_at');
    }

    /**
     * @param  list<Model>  $models
     * @return list<array{type: string, public_id: string, title: ?string, slug: ?string}>
     */
    private function format(array $models, string $type, string $locale, string $titleField): array
    {
        return array_map(static function (Model $model) use ($type, $locale, $titleField): array {
            $translations = $model->getRelation('translations');
            $translation = $translations->firstWhere('locale', $locale) ?? $translations->firstWhere('locale', 'vi') ?? $translations->first();

            return ['type' => $type, 'public_id' => (string) $model->getAttribute('public_id'), 'title' => $translation?->getAttribute($titleField), 'slug' => $translation?->getAttribute('slug')];
        }, $models);
    }
}
