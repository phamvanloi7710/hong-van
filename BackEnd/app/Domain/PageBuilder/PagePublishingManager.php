<?php

namespace App\Domain\PageBuilder;

use App\Domain\Audit\AuditTrail;
use App\Domain\Seo\SitemapCache;
use App\Exceptions\ConflictException;
use App\Models\Page;
use App\Models\PagePublishSchedule;
use App\Models\PageTranslation;
use App\Models\PageVersion;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class PagePublishingManager
{
    public function __construct(
        private PageDocumentValidator $validator,
        private PageBuilderMediaUsageSynchronizer $mediaUsage,
        private AuditTrail $audit,
        private SitemapCache $sitemap,
    ) {}

    public function saveMilestone(User $actor, Page $page, string $expectedChecksum, string $expectedVersionId, ?string $note): PageVersion
    {
        return DB::transaction(function () use ($actor, $page, $expectedChecksum, $expectedVersionId, $note): PageVersion {
            [$lockedPage, $draft] = $this->lockedDraft($page, $expectedChecksum, $expectedVersionId);
            $milestone = $this->cloneVersion($lockedPage, $draft, 'saved', $actor, $note);
            $nextDraft = $this->cloneVersion($lockedPage, $milestone, 'draft', $actor, null);
            $lockedPage->update(['draft_version_id' => $nextDraft->getKey(), 'updated_by' => $actor->getKey()]);
            $this->mediaUsage->sync($milestone, $milestone->document_json);
            $this->mediaUsage->sync($nextDraft, $nextDraft->document_json);
            $this->audit->record('page.version.saved', $actor, 'page', $page->public_id, [], ['version' => $milestone->version_number, 'checksum' => $milestone->checksum]);

            return $milestone;
        });
    }

    public function publish(User $actor, Page $page, string $expectedChecksum, string $expectedVersionId, ?string $note): PageVersion
    {
        $published = DB::transaction(function () use ($actor, $page, $expectedChecksum, $expectedVersionId, $note): PageVersion {
            [$lockedPage, $draft] = $this->lockedDraft($page, $expectedChecksum, $expectedVersionId);
            $this->assertPublishable($lockedPage, $draft);
            $published = $this->cloneVersion($lockedPage, $draft, 'saved', $actor, $note);
            $published->forceFill(['published_by' => $actor->getKey(), 'published_at' => now()])->save();
            $nextDraft = $this->cloneVersion($lockedPage, $published, 'draft', $actor, null);
            $lockedPage->update([
                'status' => 'published', 'published_version_id' => $published->getKey(),
                'draft_version_id' => $nextDraft->getKey(), 'updated_by' => $actor->getKey(),
            ]);
            $this->mediaUsage->sync($published, $published->document_json);
            $this->mediaUsage->sync($nextDraft, $nextDraft->document_json);
            $this->audit->record('page.published', $actor, 'page', $page->public_id, [], ['version' => $published->version_number, 'checksum' => $published->checksum]);

            return $published;
        });
        $this->invalidate($page);

        return $published;
    }

    public function schedule(User $actor, Page $page, string $expectedChecksum, string $expectedVersionId, CarbonImmutable $scheduledAtUtc, ?string $note): PagePublishSchedule
    {
        return DB::transaction(function () use ($actor, $page, $expectedChecksum, $expectedVersionId, $scheduledAtUtc, $note): PagePublishSchedule {
            [$lockedPage, $draft] = $this->lockedDraft($page, $expectedChecksum, $expectedVersionId);
            $this->assertPublishable($lockedPage, $draft);
            $version = $this->cloneVersion($lockedPage, $draft, 'scheduled', $actor, $note);
            $nextDraft = $this->cloneVersion($lockedPage, $version, 'draft', $actor, null);
            $lockedPage->update(['draft_version_id' => $nextDraft->getKey(), 'updated_by' => $actor->getKey()]);
            $schedule = $lockedPage->publishSchedules()->create([
                'page_version_id' => $version->getKey(), 'action' => 'publish', 'status' => 'pending',
                'scheduled_at' => $scheduledAtUtc, 'created_by' => $actor->getKey(),
            ]);
            $this->audit->record('page.publish.scheduled', $actor, 'page', $page->public_id, [], ['version' => $version->version_number, 'scheduled_at' => $scheduledAtUtc->toIso8601String()]);

            return $schedule;
        });
    }

    public function rollback(User $actor, Page $page, PageVersion $source, ?string $note): PageVersion
    {
        abort_unless($source->page_id === $page->getKey(), 404);
        $published = DB::transaction(function () use ($actor, $page, $source, $note): PageVersion {
            $lockedPage = Page::query()->lockForUpdate()->findOrFail($page->getKey());
            $source = PageVersion::query()->whereKey($source->getKey())->lockForUpdate()->firstOrFail();
            $this->assertPublishable($lockedPage, $source);
            $published = $this->cloneVersion($lockedPage, $source, 'saved', $actor, $note ?? 'Rollback v'.$source->version_number);
            $published->forceFill(['published_by' => $actor->getKey(), 'published_at' => now()])->save();
            $nextDraft = $this->cloneVersion($lockedPage, $published, 'draft', $actor, null);
            $lockedPage->update(['status' => 'published', 'published_version_id' => $published->getKey(), 'draft_version_id' => $nextDraft->getKey(), 'updated_by' => $actor->getKey()]);
            $this->mediaUsage->sync($published, $published->document_json);
            $this->mediaUsage->sync($nextDraft, $nextDraft->document_json);
            $this->audit->record('page.version.rolled_back', $actor, 'page', $page->public_id, [], ['source_version' => $source->version_number, 'published_version' => $published->version_number]);

            return $published;
        });
        $this->invalidate($page);

        return $published;
    }

    public function processSchedule(PagePublishSchedule $schedule): bool
    {
        $published = DB::transaction(function () use ($schedule): ?Page {
            $locked = PagePublishSchedule::query()->lockForUpdate()->findOrFail($schedule->getKey());
            if ($locked->status !== 'pending' || $locked->scheduled_at->isFuture()) {
                return null;
            }
            $page = Page::query()->lockForUpdate()->findOrFail($locked->page_id);
            $version = PageVersion::query()->lockForUpdate()->findOrFail($locked->page_version_id);
            $this->assertPublishable($page, $version);
            $version->forceFill(['status' => 'published', 'published_by' => $locked->created_by, 'published_at' => now()])->save();
            $page->update(['status' => 'published', 'published_version_id' => $version->getKey()]);
            $locked->update(['status' => 'completed', 'processed_at' => now(), 'failure_message' => null]);

            return $page;
        });
        if ($published instanceof Page) {
            $this->invalidate($published);
        }

        return $published instanceof Page;
    }

    /** @return Collection<int, PageVersion> */
    public function versions(Page $page): Collection
    {
        return $page->versions()->with('creator')->where('status', '!=', 'draft')->latest('version_number')->get();
    }

    /** @return array{Page, PageVersion} */
    private function lockedDraft(Page $page, string $expectedChecksum, string $expectedVersionId): array
    {
        $lockedPage = Page::query()->lockForUpdate()->findOrFail($page->getKey());
        $draft = $lockedPage->draftVersion()->lockForUpdate()->firstOrFail();
        if (! hash_equals($draft->checksum, $expectedChecksum) || $draft->public_id !== $expectedVersionId) {
            throw new ConflictException('The Page Builder draft was changed by another session.');
        }

        return [$lockedPage, $draft];
    }

    private function cloneVersion(Page $page, PageVersion $source, string $status, User $actor, ?string $note): PageVersion
    {
        return $page->versions()->create([
            'version_number' => ((int) $page->versions()->max('version_number')) + 1, 'status' => $status,
            'schema_version' => $source->schema_version, 'document_json' => $source->document_json,
            'checksum' => $source->checksum, 'note' => $note, 'parent_version_id' => $source->getKey(), 'created_by' => $actor->getKey(),
        ]);
    }

    private function assertPublishable(Page $page, PageVersion $version): void
    {
        $this->validator->validate($version->document_json);
        $translations = PageTranslation::query()->where('page_id', $page->getKey())->get()->keyBy('locale');
        $missing = collect(['vi', 'en', 'zh'])->filter(function (string $locale) use ($translations): bool {
            $translation = $translations->get($locale);

            return ! $translation instanceof PageTranslation || blank($translation->title) || blank($translation->slug);
        });
        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages(['translations' => ['Missing title/slug translations: '.$missing->join(', ').'.']]);
        }
    }

    private function invalidate(Page $page): void
    {
        Cache::forget('page-builder:page:'.$page->public_id);
        $this->sitemap->invalidate();
    }
}
