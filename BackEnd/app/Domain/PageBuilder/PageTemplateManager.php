<?php

namespace App\Domain\PageBuilder;

use App\Domain\Audit\AuditTrail;
use App\Models\Page;
use App\Models\PageTemplate;
use App\Models\PageTemplateCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class PageTemplateManager
{
    public function __construct(
        private PageDocumentValidator $validator,
        private PageManager $pages,
        private AuditTrail $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function saveFromPage(User $actor, Page $page, array $data): PageTemplate
    {
        $source = $page->draftVersion;
        abort_unless($source !== null, 422, 'The page has no draft document to save as a template.');
        $document = $this->validator->validate((array) $source->document_json);

        $template = DB::transaction(function () use ($actor, $data, $document): PageTemplate {
            $category = $this->category((string) ($data['category_key'] ?? ''));
            $template = PageTemplate::query()->create([
                'key' => $data['key'], 'name' => $data['name'], 'description' => $data['description'] ?? null,
                'page_template_category_id' => $category?->getKey(), 'is_system' => false, 'is_active' => true,
                'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey(),
            ]);
            $version = $template->versions()->create([
                'version_number' => 1, 'status' => 'published', 'schema_version' => PageDocumentSchema::VERSION,
                'document_json' => $document, 'checksum' => $this->validator->checksum($document),
                'created_by' => $actor->getKey(), 'published_at' => now('UTC'),
            ]);
            $template->update(['published_version_id' => $version->getKey()]);

            return $template->refresh()->load(['category', 'publishedVersion']);
        });
        $this->audit->record('page.template.created', $actor, 'page_template', $template->public_id, [], ['page_id' => $page->public_id]);

        return $template;
    }

    /** @param array<string, mixed> $metadata */
    public function createPage(User $actor, PageTemplate $template, array $metadata): Page
    {
        $version = $template->publishedVersion;
        abort_unless($template->is_active && $version !== null, 422, 'This template is not available.');
        $page = $this->pages->saveMetadata($actor, null, $metadata);
        $page->update(['page_template_id' => $template->getKey()]);
        $this->pages->saveDraft($actor, $page, $this->copyDocument((array) $version->document_json));
        $this->audit->record('page.created_from_template', $actor, 'page', $page->public_id, [], ['template_id' => $template->public_id]);

        return $page->refresh()->load(['translations', 'draftVersion', 'publishedVersion']);
    }

    /** @param array<string, mixed> $metadata */
    public function duplicatePage(User $actor, Page $source, array $metadata): Page
    {
        $draft = $source->draftVersion;
        abort_unless($draft !== null, 422, 'The source page has no draft document.');
        $page = $this->pages->saveMetadata($actor, null, $metadata);
        $page->update(['page_template_id' => $source->page_template_id]);
        $this->pages->saveDraft($actor, $page, $this->copyDocument((array) $draft->document_json));
        $this->audit->record('page.duplicated', $actor, 'page', $page->public_id, [], ['source_page_id' => $source->public_id]);

        return $page->refresh()->load(['translations', 'draftVersion', 'publishedVersion']);
    }

    /** @return Collection<int, PageTemplate> */
    public function library()
    {
        return PageTemplate::query()->with(['category', 'publishedVersion'])->where('is_active', true)->orderBy('name')->get();
    }

    private function category(string $key): ?PageTemplateCategory
    {
        $key = trim($key);
        if ($key === '') {
            return null;
        }

        return PageTemplateCategory::query()->firstOrCreate(
            ['key' => $key],
            ['name' => str($key)->replace(['-', '_'], ' ')->title()->toString(), 'sort_order' => 0],
        );
    }

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    private function copyDocument(array $document): array
    {
        $ids = [];
        $collect = function (array $blocks) use (&$collect, &$ids): void {
            foreach ($blocks as $block) {
                if (! is_array($block)) {
                    continue;
                }
                if (is_string($block['id'] ?? null)) {
                    $ids[$block['id']] = 'block_'.str()->lower(str()->random(18));
                }
                $collect(is_array($block['children'] ?? null) ? $block['children'] : []);
            }
        };
        $collect(is_array($document['blocks'] ?? null) ? $document['blocks'] : []);
        $rewrite = function (array $blocks) use (&$rewrite, $ids): array {
            return array_map(function (mixed $block) use (&$rewrite, $ids): mixed {
                if (! is_array($block)) {
                    return $block;
                }
                if (is_string($block['id'] ?? null) && isset($ids[$block['id']])) {
                    $block['id'] = $ids[$block['id']];
                }
                $block['bindings'] = $this->rewriteBindingReferences(is_array($block['bindings'] ?? null) ? $block['bindings'] : [], $ids);
                $block['children'] = $rewrite(is_array($block['children'] ?? null) ? $block['children'] : []);

                return $block;
            }, $blocks);
        };
        $document['blocks'] = $rewrite(is_array($document['blocks'] ?? null) ? $document['blocks'] : []);

        return $document;
    }

    /**
     * @param  array<string, mixed>  $value
     * @param  array<string, string>  $ids
     * @return array<string, mixed>
     */
    private function rewriteBindingReferences(array $value, array $ids): array
    {
        foreach ($value as $key => $child) {
            if ($key === 'sourceBlockId' && is_string($child) && isset($ids[$child])) {
                $value[$key] = $ids[$child];
            } elseif (is_array($child)) {
                $value[$key] = $this->rewriteBindingReferences($child, $ids);
            }
        }

        return $value;
    }
}
