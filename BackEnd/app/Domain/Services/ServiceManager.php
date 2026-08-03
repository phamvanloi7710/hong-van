<?php

namespace App\Domain\Services;

use App\Domain\Audit\AuditTrail;
use App\Domain\Localization\TranslatableModel;
use App\Domain\Media\MediaUsageTracker;
use App\Exceptions\ConflictException;
use App\Models\Media;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ServiceManager
{
    public function __construct(
        private MediaUsageTracker $mediaUsage,
        private AuditTrail $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function saveCategory(User $actor, ?ServiceCategory $category, array $data): ServiceCategory
    {
        $category = DB::transaction(function () use ($actor, $category, $data): ServiceCategory {
            $category ??= new ServiceCategory;
            $parentId = $this->internalId(ServiceCategory::class, $data['parent_id'] ?? null);
            $this->guardCategoryParent($category, $parentId);
            $category->fill([
                ...Arr::only($data, ['code', 'is_active', 'sort_order']),
                'parent_id' => $parentId,
                $category->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncTranslations($category, $data['translations']);
            $this->record($category->wasRecentlyCreated ? 'service_category.created' : 'service_category.updated', $actor, $category);

            return $category->fresh(['translations', 'parent.translations'])->loadCount('services');
        });
        $this->touchCache();

        return $category;
    }

    public function trashCategory(User $actor, ServiceCategory $category): void
    {
        if ($category->children()->exists() || $category->services()->exists()) {
            throw new ConflictException(__('services.category_in_use'));
        }
        $category->forceFill(['deleted_by' => $actor->getKey()])->save();
        $category->delete();
        $this->record('service_category.trashed', $actor, $category);
        $this->touchCache();
    }

    /** @param array<string, mixed> $data */
    public function createService(User $actor, array $data): Service
    {
        return $this->saveService($actor, new Service, $data);
    }

    /** @param array<string, mixed> $data */
    public function saveService(User $actor, Service $service, array $data): Service
    {
        $service = DB::transaction(function () use ($actor, $service, $data): Service {
            $oldMedia = $service->exists ? $service->media()->get() : collect();
            $service->fill([
                ...Arr::only($data, ['code', 'service_type', 'status', 'cta_type', 'is_featured', 'sort_order', 'published_at', 'unpublished_at']),
                'service_category_id' => $this->internalId(ServiceCategory::class, $data['category_id'] ?? null),
                $service->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncTranslations($service, $data['translations']);
            $this->syncMedia($service, $oldMedia->all(), $data['media'] ?? []);
            $this->record($service->wasRecentlyCreated ? 'service.created' : 'service.updated', $actor, $service);

            return $this->loadService($service);
        });
        $this->touchCache();

        return $service;
    }

    public function publishService(User $actor, Service $service): Service
    {
        $service->forceFill([
            'status' => 'published',
            'published_at' => $service->published_at ?? now('UTC'),
            'unpublished_at' => null,
            'updated_by' => $actor->getKey(),
        ])->save();
        $this->record('service.published', $actor, $service);
        $this->touchCache();

        return $this->loadService($service);
    }

    public function archiveService(User $actor, Service $service): Service
    {
        $service->forceFill(['status' => 'archived', 'updated_by' => $actor->getKey()])->save();
        $this->record('service.archived', $actor, $service);
        $this->touchCache();

        return $this->loadService($service);
    }

    public function trashService(User $actor, Service $service): void
    {
        foreach ($service->media as $media) {
            $this->mediaUsage->release($media, 'service', $service->public_id, 'media:'.$media->public_id);
        }
        $service->forceFill(['deleted_by' => $actor->getKey()])->save();
        $service->delete();
        $this->record('service.trashed', $actor, $service);
        $this->touchCache();
    }

    public function restoreService(User $actor, Service $service): Service
    {
        $service->restore();
        $service->forceFill(['deleted_by' => null, 'updated_by' => $actor->getKey()])->save();
        foreach ($service->media as $media) {
            $this->mediaUsage->track($media, 'service', $service->public_id, 'media:'.$media->public_id);
        }
        $this->record('service.restored', $actor, $service);
        $this->touchCache();

        return $this->loadService($service);
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

    /**
     * @param  list<Media>  $oldMedia
     * @param  list<array<string, mixed>>  $items
     */
    private function syncMedia(Service $service, array $oldMedia, array $items): void
    {
        $pivot = [];
        foreach ($items as $item) {
            $mediaId = $this->internalId(Media::class, $item['media_id']);
            if ($mediaId !== null) {
                $pivot[$mediaId] = Arr::only($item, ['role', 'sort_order']);
            }
        }
        $service->media()->sync($pivot);
        $newMedia = $service->media()->get();
        foreach ($oldMedia as $media) {
            if (! $newMedia->contains('id', $media->getKey())) {
                $this->mediaUsage->release($media, 'service', $service->public_id, 'media:'.$media->public_id);
            }
        }
        foreach ($newMedia as $media) {
            $this->mediaUsage->track($media, 'service', $service->public_id, 'media:'.$media->public_id);
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

    private function loadService(Service $service): Service
    {
        return $service->fresh(['translations', 'category.translations', 'media']);
    }

    private function guardCategoryParent(ServiceCategory $category, ?int $parentId): void
    {
        while ($category->exists && $parentId !== null) {
            if ($parentId === $category->getKey()) {
                throw ValidationException::withMessages(['parent_id' => [__('services.category_parent_cycle')]]);
            }
            $nextParentId = ServiceCategory::query()->whereKey($parentId)->value('parent_id');
            $parentId = $nextParentId === null ? null : (int) $nextParentId;
        }
    }

    private function touchCache(): void
    {
        Cache::forever('services:version', ((int) Cache::get('services:version', 0)) + 1);
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
