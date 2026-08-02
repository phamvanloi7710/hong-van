<?php

namespace App\Domain\Media;

use App\Domain\Audit\AuditTrail;
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
}
