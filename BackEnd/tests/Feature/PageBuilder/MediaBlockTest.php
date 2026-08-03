<?php

namespace Tests\Feature\PageBuilder;

use App\Domain\PageBuilder\BlockRegistry;
use App\Domain\PageBuilder\PageDocumentRenderer;
use App\Domain\PageBuilder\PageDocumentSchema;
use App\Domain\PageBuilder\PageDocumentValidator;
use App\Domain\PageBuilder\PageManager;
use App\Models\Media;
use App\Models\MediaVariant;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class MediaBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_blocks_render_responsive_accessible_and_privacy_friendly_markup(): void
    {
        [$first, $second] = [$this->media('first'), $this->media('second')];
        $document = $this->document($this->mediaBlocks($first, $second));

        $html = app(PageDocumentRenderer::class)->render($document);

        $this->assertStringContainsString('<picture>', $html);
        $this->assertStringContainsString('type="image/webp"', $html);
        $this->assertStringContainsString('width="1200"', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('role="list" aria-label="Gallery"', $html);
        $this->assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ', $html);
        $this->assertStringContainsString('referrerpolicy="strict-origin-when-cross-origin"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_alt_decorative_and_public_ready_media_contracts_are_enforced(): void
    {
        $media = $this->media('alt');
        $blocks = [$this->block('media.image', 'media-image-alt01', [
            'mediaId' => $media->public_id, 'alt' => '', 'decorative' => false, 'caption' => '', 'loading' => 'lazy', 'width' => 'intrinsic',
        ])];
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($this->document($blocks)), 'document.blocks.0.children.0.children.0.children.0.props.alt');

        $blocks[0]['props']['decorative'] = true;
        app(PageDocumentValidator::class)->validate($this->document($blocks));

        $media->update(['visibility' => 'private']);
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($this->document($blocks)), 'document.blocks.0.children.0.children.0.children.0.props.mediaId');
    }

    public function test_media_resolution_query_count_does_not_grow_with_the_number_of_references(): void
    {
        $first = $this->media('query-first');
        $second = $this->media('query-second');
        $single = $this->document([$this->imageBlock($first, 'media-query-one01')]);
        $multiple = $this->document($this->mediaBlocks($first, $second));

        DB::flushQueryLog();
        DB::enableQueryLog();
        app(PageDocumentRenderer::class)->render($single);
        $singleQueries = count(DB::getQueryLog());
        DB::flushQueryLog();
        app(PageDocumentRenderer::class)->render($multiple);
        $multipleQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($singleQueries, $multipleQueries);
        $this->assertLessThanOrEqual(4, $multipleQueries);
    }

    public function test_saving_a_draft_synchronizes_page_version_media_usage(): void
    {
        $actor = User::factory()->create();
        $first = $this->media('usage-first');
        $second = $this->media('usage-second');
        $page = Page::query()->create(['code' => 'media-page', 'type' => 'standard', 'status' => 'draft', 'is_home' => false, 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey()]);
        $version = $page->versions()->create([
            'version_number' => 1, 'status' => 'draft', 'schema_version' => 1,
            'document_json' => PageDocumentSchema::emptyDocument(), 'checksum' => hash('sha256', 'empty'), 'created_by' => $actor->getKey(),
        ]);
        $page->update(['draft_version_id' => $version->getKey()]);

        app(PageManager::class)->saveDraft($actor, $page->fresh(), $this->document($this->mediaBlocks($first, $second)));
        $this->assertDatabaseCount('hongvan_media_usages', 5);
        $this->assertDatabaseHas('hongvan_media_usages', ['owner_type' => 'page_version', 'owner_public_id' => $version->public_id, 'media_id' => $first->getKey()]);

        app(PageManager::class)->saveDraft($actor, $page->fresh(), $this->document([$this->imageBlock($second, 'media-only-second')]));
        $this->assertDatabaseCount('hongvan_media_usages', 1);
        $this->assertDatabaseMissing('hongvan_media_usages', ['owner_public_id' => $version->public_id, 'media_id' => $first->getKey()]);
    }

    /** @return list<array<string, mixed>> */
    private function mediaBlocks(Media $first, Media $second): array
    {
        return [
            $this->imageBlock($first, 'media-image-00001'),
            $this->block('media.image-text', 'media-image-text1', [
                'mediaId' => $second->public_id, 'alt' => 'Image with text', 'decorative' => false, 'heading' => 'Heading', 'text' => 'Text',
                'imagePosition' => 'right', 'linkLabel' => 'Details', 'linkUrl' => 'https://example.com/details', 'target' => '_blank',
            ]),
            $this->block('media.gallery', 'media-gallery-001', ['label' => 'Gallery', 'columns' => 2, 'items' => [
                ['mediaId' => $first->public_id, 'alt' => 'First gallery image', 'decorative' => false, 'caption' => 'First'],
                ['mediaId' => $second->public_id, 'alt' => 'Second gallery image', 'decorative' => false, 'caption' => 'Second'],
            ]]),
            $this->block('media.video-embed', 'media-video-00001', ['provider' => 'youtube-nocookie', 'videoId' => 'dQw4w9WgXcQ', 'title' => 'Video title', 'loading' => 'lazy']),
            $this->block('media.logo-cloud', 'media-logo-cloud1', ['label' => 'Partners', 'items' => [
                ['mediaId' => $first->public_id, 'alt' => 'First logo', 'linkUrl' => '', 'target' => '_self'],
            ]]),
        ];
    }

    /** @return array<string, mixed> */
    private function imageBlock(Media $media, string $id): array
    {
        return $this->block('media.image', $id, [
            'mediaId' => $media->public_id, 'alt' => 'Accessible image', 'decorative' => false, 'caption' => '', 'loading' => 'lazy', 'width' => 'intrinsic',
        ]);
    }

    /** @param list<array<string, mixed>> $content @return array<string, mixed> */
    private function document(array $content): array
    {
        $document = PageDocumentSchema::emptyDocument();
        $document['blocks'] = [$this->block('layout.section', 'media-section-001', null, [
            $this->block('layout.container', 'media-container01', null, [
                $this->block('layout.stack', 'media-stack-00001', null, $content),
            ]),
        ])];

        return $document;
    }

    /** @param array<string, mixed>|null $props @param list<array<string, mixed>> $children @return array<string, mixed> */
    private function block(string $type, string $id, ?array $props = null, array $children = []): array
    {
        $definition = app(BlockRegistry::class)->get($type);
        $block = ['id' => $id, 'type' => $type, 'version' => 1, ...$definition->defaults];
        if ($props !== null) {
            $block['props'] = $props;
        }
        $block['children'] = $children;

        return $block;
    }

    private function media(string $name): Media
    {
        $media = Media::query()->create([
            'disk' => 'public', 'path' => "media/{$name}.png", 'original_filename' => "{$name}.png", 'normalized_filename' => "{$name}.png",
            'extension' => 'png', 'mime_type' => 'image/png', 'size_bytes' => 100, 'checksum_sha256' => hash('sha256', $name),
            'width' => 1200, 'height' => 800, 'status' => 'ready', 'visibility' => 'public',
        ]);
        MediaVariant::query()->create([
            'media_id' => $media->getKey(), 'variant_key' => 'preview_webp', 'disk' => 'public', 'path' => "media/{$name}-preview.webp",
            'extension' => 'webp', 'mime_type' => 'image/webp', 'size_bytes' => 80, 'checksum_sha256' => hash('sha256', $name.'variant'),
            'width' => 800, 'height' => 533, 'status' => 'ready', 'generated_at' => now('UTC'),
        ]);

        return $media;
    }

    /** @param callable(): mixed $callback */
    private function expectValidationPath(callable $callback, string $path): void
    {
        try {
            $callback();
            $this->fail('Expected a PageDocument validation exception.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($path, $exception->errors());
        }
    }
}
