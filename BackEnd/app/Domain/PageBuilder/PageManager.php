<?php

namespace App\Domain\PageBuilder;

use App\Domain\Audit\AuditTrail;
use App\Models\Page;
use App\Models\PageVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class PageManager
{
    public function __construct(
        private PageDocumentValidator $validator,
        private PageBuilderMediaUsageSynchronizer $mediaUsage,
        private AuditTrail $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function saveMetadata(User $actor, ?Page $page, array $data): Page
    {
        $created = $page === null;
        $before = $page?->only(['code', 'type', 'status', 'is_home']) ?? [];
        $page = DB::transaction(function () use ($actor, $page, $data): Page {
            $page ??= new Page(['created_by' => $actor->getKey()]);
            $page->fill([
                'code' => $data['code'],
                'type' => $data['type'],
                'status' => $page->exists ? $page->status : 'draft',
                'is_home' => $data['is_home'],
                'updated_by' => $actor->getKey(),
            ])->save();
            foreach ($data['translations'] as $translation) {
                $page->translations()->updateOrCreate(
                    ['locale' => $translation['locale']],
                    ['title' => $translation['title'], 'navigation_label' => $translation['navigation_label'] ?? null, 'slug' => $translation['slug']],
                );
            }
            if (! $page->draft_version_id) {
                $document = $this->validator->validate(PageDocumentSchema::emptyDocument());
                $version = $page->versions()->create([
                    'version_number' => 1,
                    'status' => 'draft',
                    'schema_version' => PageDocumentSchema::VERSION,
                    'document_json' => $document,
                    'checksum' => $this->validator->checksum($document),
                    'created_by' => $actor->getKey(),
                ]);
                $page->update(['draft_version_id' => $version->getKey()]);
            }

            return $page->refresh()->load(['translations', 'draftVersion', 'publishedVersion']);
        });
        $this->audit->record($created ? 'page.created' : 'page.metadata.updated', $actor, 'page', $page->public_id, $before, $page->only(['code', 'type', 'status', 'is_home']));

        return $page;
    }

    /** @param array<string, mixed> $document */
    public function saveDraft(User $actor, Page $page, array $document): PageVersion
    {
        $validated = $this->validator->validate($document);
        $checksum = $this->validator->checksum($validated);
        $version = DB::transaction(function () use ($actor, $page, $validated, $checksum): PageVersion {
            $lockedPage = Page::query()->lockForUpdate()->findOrFail($page->getKey());
            $draft = $lockedPage->draftVersion()->lockForUpdate()->first();
            if (! $draft instanceof PageVersion || $draft->status !== 'draft') {
                $draft = $lockedPage->versions()->create([
                    'version_number' => ((int) $lockedPage->versions()->max('version_number')) + 1,
                    'status' => 'draft',
                    'schema_version' => PageDocumentSchema::VERSION,
                    'document_json' => $validated,
                    'checksum' => $checksum,
                    'parent_version_id' => $lockedPage->published_version_id,
                    'created_by' => $actor->getKey(),
                ]);
                $lockedPage->update(['draft_version_id' => $draft->getKey(), 'updated_by' => $actor->getKey()]);
            } else {
                $draft->update(['schema_version' => PageDocumentSchema::VERSION, 'document_json' => $validated, 'checksum' => $checksum]);
                $lockedPage->update(['updated_by' => $actor->getKey()]);
            }
            $this->mediaUsage->sync($draft, $validated);

            return $draft->refresh();
        });
        $this->audit->record('page.draft.updated', $actor, 'page', $page->public_id, [], ['version' => $version->version_number, 'checksum' => $version->checksum]);

        return $version;
    }
}
