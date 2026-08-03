<?php

namespace App\Domain\Posts;

use App\Domain\Audit\AuditTrail;
use App\Domain\Localization\TranslatableModel;
use App\Domain\Media\MediaUsageTracker;
use App\Exceptions\ConflictException;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostSlugHistory;
use App\Models\PostTag;
use App\Models\PostTranslation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class PostManager
{
    public function __construct(
        private RichTextSanitizer $sanitizer,
        private MediaUsageTracker $mediaUsage,
        private AuditTrail $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function saveCategory(User $actor, ?PostCategory $category, array $data): PostCategory
    {
        $category = DB::transaction(function () use ($actor, $category, $data): PostCategory {
            $category ??= new PostCategory;
            $parentId = $this->internalId(PostCategory::class, $data['parent_id'] ?? null);
            $this->guardCategoryParent($category, $parentId);
            $category->fill([
                ...Arr::only($data, ['code', 'is_active', 'sort_order']),
                'parent_id' => $parentId,
                $category->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncTranslations($category, $data['translations']);
            $this->record($category->wasRecentlyCreated ? 'post_category.created' : 'post_category.updated', $actor, $category);

            return $category->fresh(['translations', 'parent.translations'])->loadCount('posts');
        });
        $this->touchCache();

        return $category;
    }

    /** @param array<string, mixed> $data */
    public function saveTag(User $actor, ?PostTag $tag, array $data): PostTag
    {
        $tag = DB::transaction(function () use ($actor, $tag, $data): PostTag {
            $tag ??= new PostTag;
            $tag->fill([
                ...Arr::only($data, ['code', 'is_active', 'sort_order']),
                $tag->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncTranslations($tag, $data['translations']);
            $this->record($tag->wasRecentlyCreated ? 'post_tag.created' : 'post_tag.updated', $actor, $tag);

            return $tag->fresh('translations')->loadCount('posts');
        });
        $this->touchCache();

        return $tag;
    }

    /** @param array<string, mixed> $data */
    public function savePost(User $actor, ?Post $post, array $data): Post
    {
        $post = DB::transaction(function () use ($actor, $post, $data): Post {
            $post ??= new Post;
            $oldMedia = $post->exists ? $post->featuredMedia : null;
            $post->fill([
                ...Arr::only($data, ['code', 'status', 'is_featured', 'scheduled_for', 'published_at', 'unpublished_at']),
                'post_category_id' => $this->internalId(PostCategory::class, $data['category_id'] ?? null),
                'author_id' => $this->internalId(User::class, $data['author_id'] ?? null) ?? $actor->getKey(),
                'featured_media_id' => $this->internalId(Media::class, $data['featured_media_id'] ?? null),
                $post->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncPostTranslations($post, $data['translations']);
            $this->syncTags($post, $data['tag_ids'] ?? []);
            $this->syncFeaturedMedia($post, $oldMedia);
            $this->record($post->wasRecentlyCreated ? 'post.created' : 'post.updated', $actor, $post, ['status' => $post->status]);

            return $this->loadPost($post);
        });
        $this->touchCache();

        return $post;
    }

    public function publish(User $actor, Post $post): Post
    {
        $post->forceFill([
            'status' => 'published',
            'scheduled_for' => null,
            'published_at' => $post->published_at ?? now('UTC'),
            'unpublished_at' => null,
            'updated_by' => $actor->getKey(),
        ])->save();
        $this->record('post.published', $actor, $post);
        $this->touchCache();

        return $this->loadPost($post);
    }

    public function archive(User $actor, Post $post): Post
    {
        $post->forceFill(['status' => 'archived', 'scheduled_for' => null, 'updated_by' => $actor->getKey()])->save();
        $this->record('post.archived', $actor, $post);
        $this->touchCache();

        return $this->loadPost($post);
    }

    public function trashPost(User $actor, Post $post): void
    {
        if ($post->featuredMedia !== null) {
            $this->mediaUsage->release($post->featuredMedia, 'post', $post->public_id, 'featured');
        }
        $post->forceFill(['deleted_by' => $actor->getKey()])->save();
        $post->delete();
        $this->record('post.trashed', $actor, $post);
        $this->touchCache();
    }

    public function restorePost(User $actor, Post $post): Post
    {
        $post->restore();
        $post->forceFill(['deleted_by' => null, 'updated_by' => $actor->getKey()])->save();
        if ($post->featuredMedia !== null) {
            $this->mediaUsage->track($post->featuredMedia, 'post', $post->public_id, 'featured');
        }
        $this->record('post.restored', $actor, $post);
        $this->touchCache();

        return $this->loadPost($post);
    }

    public function trashCategory(User $actor, PostCategory $category): void
    {
        if ($category->children()->exists() || $category->posts()->exists()) {
            throw new ConflictException(__('posts.category_in_use'));
        }
        $category->forceFill(['deleted_by' => $actor->getKey()])->save();
        $category->delete();
        $this->record('post_category.trashed', $actor, $category);
        $this->touchCache();
    }

    public function trashTag(User $actor, PostTag $tag): void
    {
        if ($tag->posts()->exists()) {
            throw new ConflictException(__('posts.tag_in_use'));
        }
        $tag->forceFill(['deleted_by' => $actor->getKey()])->save();
        $tag->delete();
        $this->record('post_tag.trashed', $actor, $tag);
        $this->touchCache();
    }

    /** @param list<array<string, mixed>> $translations */
    private function syncPostTranslations(Post $post, array $translations): void
    {
        $locales = [];
        foreach ($translations as $translation) {
            $locale = $translation['locale'];
            $locales[] = $locale;
            $current = PostTranslation::query()->where('post_id', $post->getKey())->where('locale', $locale)->first();
            if ($current !== null && $current->slug !== $translation['slug']) {
                PostSlugHistory::query()->firstOrCreate(
                    ['locale' => $locale, 'slug' => $current->slug],
                    ['post_id' => $post->getKey(), 'created_at' => now('UTC')],
                );
            }
            $post->translations()->updateOrCreate(
                ['locale' => $locale],
                [...Arr::except($translation, ['locale']), 'content_html' => $this->sanitizer->sanitize($translation['content_html'])],
            );
        }
        $post->translations()->whereNotIn('locale', $locales)->delete();
    }

    /** @param list<array<string, mixed>> $translations */
    private function syncTranslations(TranslatableModel $model, array $translations): void
    {
        $locales = [];
        foreach ($translations as $translation) {
            $locale = $translation['locale'];
            $locales[] = $locale;
            $model->translations()->updateOrCreate(['locale' => $locale], Arr::except($translation, ['locale']));
        }
        $model->translations()->whereNotIn('locale', $locales)->delete();
    }

    /** @param list<string> $publicIds */
    private function syncTags(Post $post, array $publicIds): void
    {
        $ids = PostTag::query()->whereIn('public_id', $publicIds)->pluck('id');
        $post->tags()->sync($ids->mapWithKeys(static fn (int $id): array => [$id => ['created_at' => now('UTC')]])->all());
    }

    private function syncFeaturedMedia(Post $post, ?Media $oldMedia): void
    {
        $newMedia = $post->featuredMedia()->first();
        if ($oldMedia !== null && $oldMedia->getKey() !== $newMedia?->getKey()) {
            $this->mediaUsage->release($oldMedia, 'post', $post->public_id, 'featured');
        }
        if ($newMedia !== null) {
            $this->mediaUsage->track($newMedia, 'post', $post->public_id, 'featured');
        }
    }

    /** @param class-string<Model> $modelClass */
    private function internalId(string $modelClass, mixed $publicId): ?int
    {
        if (! is_string($publicId) || $publicId === '') {
            return null;
        }

        return (int) $modelClass::query()->where('public_id', $publicId)->valueOrFail('id');
    }

    private function loadPost(Post $post): Post
    {
        return $post->fresh(['translations', 'category.translations', 'tags.translations', 'author', 'featuredMedia']);
    }

    private function guardCategoryParent(PostCategory $category, ?int $parentId): void
    {
        while ($category->exists && $parentId !== null) {
            if ($parentId === $category->getKey()) {
                throw ValidationException::withMessages(['parent_id' => [__('posts.category_parent_cycle')]]);
            }
            $parentId = PostCategory::query()->whereKey($parentId)->value('parent_id');
        }
    }

    private function touchCache(): void
    {
        Cache::forever('posts:version', ((int) Cache::get('posts:version', 0)) + 1);
    }

    /** @param array<string, mixed> $details */
    private function record(string $action, User $actor, Model $subject, array $details = []): void
    {
        $this->audit->record(
            action: $action,
            actor: $actor,
            subjectType: $subject->getTable(),
            subjectPublicId: (string) $subject->getAttribute('public_id'),
            after: $details,
        );
    }
}
