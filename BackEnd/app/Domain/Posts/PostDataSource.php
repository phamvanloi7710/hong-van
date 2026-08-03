<?php

namespace App\Domain\Posts;

use App\Models\Post;
use App\Models\PostSlugHistory;
use App\Models\PostTranslation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class PostDataSource
{
    /** @return LengthAwarePaginator<int, Post> */
    public function listing(string $locale, int $perPage = 12, ?string $categorySlug = null, ?string $tagSlug = null): LengthAwarePaginator
    {
        $query = $this->published()->when($categorySlug, static fn (Builder $builder) => $builder
            ->whereHas('category.translations', static fn ($translation) => $translation->where('locale', $locale)->where('slug', $categorySlug)))
            ->when($tagSlug, static fn (Builder $builder) => $builder
                ->whereHas('tags.translations', static fn ($translation) => $translation->where('locale', $locale)->where('slug', $tagSlug)));

        return $query->orderByDesc('published_at')->paginate(max(1, min($perPage, 50)));
    }

    /** @return array{post: Post, redirect_slug: ?string}|null */
    public function resolveSlug(string $locale, string $slug): ?array
    {
        $translation = PostTranslation::query()->where('locale', $locale)->where('slug', $slug)->first();
        $post = $translation === null ? null : $this->published()->whereKey($translation->post_id)->first();
        if ($post !== null) {
            return ['post' => $post, 'redirect_slug' => null];
        }

        $history = PostSlugHistory::query()->where('locale', $locale)->where('slug', $slug)->first();
        if ($history === null) {
            return null;
        }
        $post = $this->published()->whereKey($history->post_id)->first();
        if ($post === null) {
            return null;
        }
        $current = $post->translations->firstWhere('locale', $locale) ?? $post->translations->firstWhere('locale', 'vi');

        return ['post' => $post, 'redirect_slug' => $current?->slug];
    }

    /** @return list<Post> */
    public function related(Post $post, int $limit = 4): array
    {
        return $this->published()->whereKeyNot($post->getKey())
            ->when($post->post_category_id, static fn (Builder $query) => $query->where('post_category_id', $post->post_category_id))
            ->orderByDesc('published_at')->limit(max(1, min($limit, 12)))->get()->all();
    }

    public function translation(Post $post, string $locale): ?PostTranslation
    {
        return $post->translations->firstWhere('locale', $locale)
            ?? $post->translations->firstWhere('locale', 'vi')
            ?? $post->translations->first();
    }

    /** @return Builder<Post> */
    private function published(): Builder
    {
        return Post::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now('UTC'))
            ->where(static fn (Builder $query) => $query->whereNull('unpublished_at')->orWhere('unpublished_at', '>', now('UTC')))
            ->with(['translations', 'category.translations', 'tags.translations', 'author', 'featuredMedia']);
    }
}
