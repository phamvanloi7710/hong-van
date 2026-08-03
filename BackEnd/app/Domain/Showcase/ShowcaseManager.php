<?php

namespace App\Domain\Showcase;

use App\Domain\Audit\AuditTrail;
use App\Domain\Localization\TranslatableModel;
use App\Domain\Media\MediaUsageTracker;
use App\Exceptions\ConflictException;
use App\Models\Certification;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\Partner;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final readonly class ShowcaseManager
{
    public function __construct(private MediaUsageTracker $mediaUsage, private AuditTrail $audit) {}

    /** @param array<string, mixed> $data */
    public function save(string $kind, User $actor, ?TranslatableModel $model, array $data): TranslatableModel
    {
        $model = DB::transaction(function () use ($kind, $actor, $model, $data): TranslatableModel {
            $model ??= $this->newModel($kind);
            $oldMedia = $this->ownedMedia($kind, $model);
            $attributes = Arr::only($data, $this->fields($kind));
            $attributes = [...$attributes, ...$this->foreignKeys($kind, $data)];
            if (array_key_exists('status', $attributes) && $attributes['status'] === 'published') {
                $attributes['published_at'] = $model->getAttribute('published_at') ?? now('UTC');
            } elseif ($kind !== 'gallery-items') {
                $attributes['published_at'] = null;
            }
            $attributes[$model->exists ? 'updated_by' : 'created_by'] = $actor->getKey();
            $attributes['updated_by'] = $actor->getKey();
            $model->fill($attributes)->save();
            $this->syncTranslations($model, (array) $data['translations']);
            if ($model instanceof Project) {
                $this->syncProjectMedia($model, (array) ($data['media_items'] ?? []));
            }
            $this->syncOwnedMedia($kind, $model, $oldMedia);
            $this->record($kind.'.'.($model->wasRecentlyCreated ? 'created' : 'updated'), $actor, $model);

            return $this->load($kind, $model);
        });
        $this->touchCache();

        return $model;
    }

    public function publish(string $kind, User $actor, TranslatableModel $model): TranslatableModel
    {
        $values = ['status' => 'published', 'updated_by' => $actor->getKey()];
        if ($kind !== 'gallery-items') {
            $values['published_at'] = $model->getAttribute('published_at') ?? now('UTC');
        }
        $model->forceFill($values)->save();
        $this->record($kind.'.published', $actor, $model);
        $this->touchCache();

        return $this->load($kind, $model);
    }

    public function archive(string $kind, User $actor, TranslatableModel $model): TranslatableModel
    {
        $model->forceFill(['status' => 'archived', 'updated_by' => $actor->getKey()])->save();
        $this->record($kind.'.archived', $actor, $model);
        $this->touchCache();

        return $this->load($kind, $model);
    }

    public function trash(string $kind, User $actor, TranslatableModel $model): void
    {
        if ($model instanceof Gallery && $model->items()->exists()) {
            throw new ConflictException(__('showcase.gallery_in_use'));
        }
        foreach ($this->ownedMedia($kind, $model) as $field => $media) {
            $this->mediaUsage->release($media, $this->ownerType($kind), (string) $model->getAttribute('public_id'), $field);
        }
        $model->forceFill(['deleted_by' => $actor->getKey()])->save();
        $model->delete();
        $this->record($kind.'.trashed', $actor, $model);
        $this->touchCache();
    }

    public function restore(string $kind, User $actor, TranslatableModel $model): TranslatableModel
    {
        if ($model instanceof Gallery || $model instanceof GalleryItem || $model instanceof Partner || $model instanceof Certification || $model instanceof Project) {
            $model->restore();
        }
        $model->forceFill(['deleted_by' => null, 'updated_by' => $actor->getKey()])->save();
        foreach ($this->ownedMedia($kind, $model) as $field => $media) {
            $this->mediaUsage->track($media, $this->ownerType($kind), (string) $model->getAttribute('public_id'), $field);
        }
        $this->record($kind.'.restored', $actor, $model);
        $this->touchCache();

        return $this->load($kind, $model);
    }

    private function newModel(string $kind): TranslatableModel
    {
        return match ($kind) {
            'galleries' => new Gallery,
            'gallery-items' => new GalleryItem,
            'partners' => new Partner,
            'certifications' => new Certification,
            'projects' => new Project,
            default => throw new \InvalidArgumentException('Unknown showcase kind.'),
        };
    }

    /** @return list<string> */
    private function fields(string $kind): array
    {
        return match ($kind) {
            'galleries' => ['code', 'status', 'is_featured', 'sort_order'],
            'gallery-items' => ['status', 'is_featured', 'sort_order'],
            'partners' => ['code', 'website_url', 'status', 'is_featured', 'sort_order'],
            'certifications' => ['code', 'document_visibility', 'issued_on', 'expires_on', 'status', 'is_featured', 'sort_order'],
            'projects' => ['code', 'started_on', 'completed_on', 'status', 'is_featured', 'sort_order'],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function foreignKeys(string $kind, array $data): array
    {
        return match ($kind) {
            'gallery-items' => ['gallery_id' => $this->id(Gallery::class, $data['gallery_id'] ?? null), 'media_id' => $this->id(Media::class, $data['media_id'] ?? null)],
            'partners' => ['logo_media_id' => $this->id(Media::class, $data['logo_media_id'] ?? null)],
            'certifications' => ['image_media_id' => $this->id(Media::class, $data['image_media_id'] ?? null), 'document_media_id' => $this->id(Media::class, $data['document_media_id'] ?? null)],
            default => [],
        };
    }

    /** @param list<array<string, mixed>> $translations */
    private function syncTranslations(TranslatableModel $model, array $translations): void
    {
        $locales = [];
        foreach ($translations as $translation) {
            $locales[] = $translation['locale'];
            $model->translations()->updateOrCreate(['locale' => $translation['locale']], Arr::except($translation, ['locale']));
        }
        $model->translations()->whereNotIn('locale', $locales)->delete();
    }

    /** @param list<array<string, mixed>> $items */
    private function syncProjectMedia(Project $project, array $items): void
    {
        foreach ($project->mediaItems()->with('media')->get() as $old) {
            $this->mediaUsage->release($old->media, 'project', $project->public_id, 'media:'.$old->public_id);
        }
        $project->mediaItems()->delete();
        foreach ($items as $item) {
            $assignment = $project->mediaItems()->create(['media_id' => $this->id(Media::class, $item['media_id']), 'role' => $item['role'], 'sort_order' => $item['sort_order']]);
            $this->syncTranslations($assignment, $item['translations']);
            $media = $assignment->media()->firstOrFail();
            $this->mediaUsage->track($media, 'project', $project->public_id, 'media:'.$assignment->public_id, ['role' => $assignment->role]);
        }
    }

    /** @return array<string, Media> */
    private function ownedMedia(string $kind, TranslatableModel $model): array
    {
        if (! $model->exists) {
            return [];
        }
        if ($model instanceof Project) {
            $result = [];
            foreach ($model->mediaItems()->with('media')->get() as $item) {
                $result['media:'.$item->public_id] = $item->media;
            }

            return $result;
        }
        $result = [];
        if ($model instanceof GalleryItem) {
            $media = $model->media()->first();
            if ($media !== null) {
                $result['media'] = $media;
            }
        }
        if ($model instanceof Partner) {
            $media = $model->logo()->first();
            if ($media !== null) {
                $result['logo'] = $media;
            }
        }
        if ($model instanceof Certification) {
            $image = $model->image()->first();
            if ($image !== null) {
                $result['image'] = $image;
            }
            $document = $model->document()->first();
            if ($document !== null) {
                $result['document'] = $document;
            }
        }

        return $result;
    }

    /** @param array<string, Media> $old */
    private function syncOwnedMedia(string $kind, TranslatableModel $model, array $old): void
    {
        if ($model instanceof Project) {
            return;
        }
        $current = $this->ownedMedia($kind, $model);
        foreach ($old as $field => $media) {
            if (! isset($current[$field]) || $current[$field]->getKey() !== $media->getKey()) {
                $this->mediaUsage->release($media, $this->ownerType($kind), (string) $model->getAttribute('public_id'), $field);
            }
        }
        foreach ($current as $field => $media) {
            $this->mediaUsage->track($media, $this->ownerType($kind), (string) $model->getAttribute('public_id'), $field);
        }
    }

    /** @param class-string<Model> $class */
    private function id(string $class, mixed $publicId): ?int
    {
        return is_string($publicId) && $publicId !== '' ? (int) $class::query()->where('public_id', $publicId)->valueOrFail('id') : null;
    }

    private function ownerType(string $kind): string
    {
        return match ($kind) {
            'gallery-items' => 'gallery_item', 'partners' => 'partner', 'certifications' => 'certification', 'projects' => 'project', default => 'gallery'
        };
    }

    private function load(string $kind, TranslatableModel $model): TranslatableModel
    {
        return $model->fresh(match ($kind) {
            'gallery-items' => ['translations', 'gallery.translations', 'media'], 'partners' => ['translations', 'logo'], 'certifications' => ['translations', 'image', 'document'], 'projects' => ['translations', 'mediaItems.translations', 'mediaItems.media'], default => ['translations', 'items.translations', 'items.media']
        });
    }

    private function touchCache(): void
    {
        Cache::forever('showcase:version', ((int) Cache::get('showcase:version', 0)) + 1);
    }

    private function record(string $action, User $actor, Model $model): void
    {
        $this->audit->record($action, $actor, $model->getTable(), (string) $model->getAttribute('public_id'));
    }
}
