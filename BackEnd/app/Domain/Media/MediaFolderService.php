<?php

namespace App\Domain\Media;

use App\Domain\Audit\AuditTrail;
use App\Exceptions\ConflictException;
use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final readonly class MediaFolderService
{
    public function __construct(private AuditTrail $auditTrail) {}

    /** @param array{name: string, parent_id?: string|null, sort_order?: int} $data */
    public function create(array $data, User $actor, Request $request): MediaFolder
    {
        $parentId = $data['parent_id'] ?? null;
        $parent = is_string($parentId)
            ? MediaFolder::query()->where('public_id', $parentId)->firstOrFail()
            : null;
        $this->guardUnlocked($parent);
        $baseSlug = Str::slug($data['name']);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'folder';
        $slug = $baseSlug;
        $suffix = 2;

        while (MediaFolder::query()->where('parent_id', $parent?->getKey())->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        $folder = MediaFolder::query()->create([
            'parent_id' => $parent?->getKey(),
            'name' => trim($data['name']),
            'slug' => $slug,
            'sort_order' => $data['sort_order'] ?? 0,
            'created_by' => $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ]);

        $this->auditTrail->record(
            'media.folder.created',
            $actor,
            'media_folder',
            $folder->public_id,
            after: ['name' => $folder->name, 'parent_public_id' => $parent?->public_id],
            request: $request,
        );

        return $folder;
    }

    /** @param array{name: string} $data */
    public function rename(MediaFolder $folder, array $data, User $actor, Request $request): MediaFolder
    {
        $this->guardUnlocked($folder);

        $name = trim($data['name']);
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'folder';
        $slug = $baseSlug;
        $suffix = 2;

        while (MediaFolder::query()
            ->where('parent_id', $folder->parent_id)
            ->whereKeyNot($folder->getKey())
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        $before = $folder->only(['name', 'slug']);
        $folder->forceFill([
            'name' => $name,
            'slug' => $slug,
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->auditTrail->record('media.folder.renamed', $actor, 'media_folder', $folder->public_id, before: $before, after: $folder->only(['name', 'slug']), request: $request);

        return $folder;
    }

    public function setLock(MediaFolder $folder, bool $locked, User $actor, Request $request): MediaFolder
    {
        $before = (bool) $folder->is_locked;
        $folder->forceFill(['is_locked' => $locked, 'updated_by' => $actor->getKey()])->save();
        $this->auditTrail->record('media.folder.lock.changed', $actor, 'media_folder', $folder->public_id, before: ['locked' => $before], after: ['locked' => $locked], request: $request);

        return $folder;
    }

    private function guardUnlocked(?MediaFolder $folder): void
    {
        while ($folder !== null) {
            if ($folder->is_locked) {
                throw new ConflictException(__('media.folder_locked'));
            }

            $folder = $folder->parent;
        }
    }
}
