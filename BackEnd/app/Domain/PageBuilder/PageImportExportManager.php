<?php

namespace App\Domain\PageBuilder;

use App\Domain\Audit\AuditTrail;
use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final readonly class PageImportExportManager
{
    private const FORMAT = 'hongvan.page-builder.export';

    public function __construct(
        private PageDocumentMigrator $migrator,
        private PageBuilderMediaResolver $media,
        private PageManager $pages,
        private AuditTrail $audit,
    ) {}

    /** @return array<string, mixed> */
    public function export(Page $page): array
    {
        $draft = $page->draftVersion;
        abort_unless($draft !== null, 422, 'The page has no draft document to export.');
        $document = (array) $draft->document_json;
        $media = array_values(array_unique(array_column($this->media->references($document), 'publicId')));

        return [
            'manifest' => [
                'format' => self::FORMAT, 'format_version' => 1,
                'document_schema_version' => $draft->schema_version,
                'block_versions' => $this->blockVersions($document),
                'media_public_ids' => $media,
            ],
            'page' => [
                'code' => $page->code, 'type' => $page->type,
                'translations' => $page->translations->map(fn ($translation): array => [
                    'locale' => $translation->locale, 'title' => $translation->title,
                    'navigation_label' => $translation->navigation_label, 'slug' => $translation->slug,
                ])->values()->all(),
            ],
            'document' => $document,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $mediaMap
     * @return array{document: array<string, mixed>, report: array{valid: bool, migrated_schema_version: int, media_references: list<string>, missing_media: list<string>}}
     */
    public function validate(array $payload, array $mediaMap = []): array
    {
        $manifest = $payload['manifest'] ?? null;
        $document = $payload['document'] ?? null;
        if (! is_array($manifest) || ($manifest['format'] ?? null) !== self::FORMAT || ($manifest['format_version'] ?? null) !== 1 || ! is_array($document)) {
            throw ValidationException::withMessages(['payload' => ['Unsupported Page Builder export manifest.']]);
        }
        $document = $this->replaceMediaReferences($document, $mediaMap);
        $references = array_values(array_unique(array_column($this->media->references($document), 'publicId')));
        $available = Media::query()->whereIn('public_id', $references)->where('status', 'ready')->where('visibility', 'public')->pluck('public_id')->all();
        $missing = array_values(array_diff($references, $available));
        if ($missing !== []) {
            throw ValidationException::withMessages(['media' => ['Missing or unavailable media: '.implode(', ', $missing)]]);
        }
        $migrated = $this->migrator->migrate($document);

        return ['document' => $migrated, 'report' => [
            'valid' => true, 'migrated_schema_version' => PageDocumentSchema::VERSION,
            'media_references' => $references, 'missing_media' => [],
        ]];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     * @param  array<string, string>  $mediaMap
     */
    public function import(User $actor, array $payload, array $metadata, array $mediaMap = []): Page
    {
        $validated = $this->validate($payload, $mediaMap);
        $page = $this->pages->saveMetadata($actor, null, $metadata);
        $this->pages->saveDraft($actor, $page, $validated['document']);
        $this->audit->record('page.imported', $actor, 'page', $page->public_id, [], ['format_version' => 1, 'report' => $validated['report']]);

        return $page->refresh()->load(['translations', 'draftVersion', 'publishedVersion']);
    }

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, int>
     */
    private function blockVersions(array $document): array
    {
        $versions = [];
        $collect = function (array $blocks) use (&$collect, &$versions): void {
            foreach ($blocks as $block) {
                if (! is_array($block)) {
                    continue;
                }
                if (is_string($block['type'] ?? null) && is_int($block['version'] ?? null)) {
                    $versions[$block['type']] = $block['version'];
                }
                $collect(is_array($block['children'] ?? null) ? $block['children'] : []);
            }
        };
        $collect(is_array($document['blocks'] ?? null) ? $document['blocks'] : []);
        ksort($versions);

        return $versions;
    }

    /**
     * @param  array<string, mixed>  $document
     * @param  array<string, string>  $mediaMap
     * @return array<string, mixed>
     */
    private function replaceMediaReferences(array $document, array $mediaMap): array
    {
        $rewrite = function (array $value) use (&$rewrite, $mediaMap): array {
            foreach ($value as $key => $child) {
                if (in_array($key, ['mediaId', 'backgroundMediaId'], true) && is_string($child) && isset($mediaMap[$child])) {
                    $value[$key] = $mediaMap[$child];
                } elseif (is_array($child)) {
                    $value[$key] = $rewrite($child);
                }
            }

            return $value;
        };

        return $rewrite($document);
    }
}
