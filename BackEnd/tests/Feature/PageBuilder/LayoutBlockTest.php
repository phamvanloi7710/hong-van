<?php

namespace Tests\Feature\PageBuilder;

use App\Domain\PageBuilder\BlockRegistry;
use App\Domain\PageBuilder\LayoutPreviewFixture;
use App\Domain\PageBuilder\PageDocumentRenderer;
use App\Domain\PageBuilder\PageDocumentValidator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class LayoutBlockTest extends TestCase
{
    public function test_registry_exposes_the_seven_allowlisted_layout_blocks_without_server_class_names(): void
    {
        $metadata = collect(app(BlockRegistry::class)->metadata());
        $layout = $metadata->where('category', 'layout')->values();

        $this->assertSame([
            'layout.section',
            'layout.container',
            'layout.stack',
            'layout.grid',
            'layout.columns',
            'layout.spacer',
            'layout.divider',
        ], $layout->pluck('type')->all());
        $layout->each(function (array $definition): void {
            $this->assertArrayHasKey('schema', $definition);
            $this->assertArrayHasKey('defaults', $definition);
            $this->assertArrayHasKey('maxDepth', $definition);
            $this->assertArrayHasKey('minChildren', $definition);
            $this->assertArrayHasKey('maxChildren', $definition);
            $this->assertArrayNotHasKey('renderer', $definition);
            $this->assertArrayNotHasKey('sanitizer', $definition);
        });
    }

    public function test_preview_fixture_validates_and_renders_with_semantic_mobile_layout_classes(): void
    {
        $fixture = app(LayoutPreviewFixture::class)->document();
        $validated = app(PageDocumentValidator::class)->validate($fixture);
        $html = app(PageDocumentRenderer::class)->render($validated);

        $this->assertStringContainsString('<section', $html);
        $this->assertStringContainsString('<hr', $html);
        $this->assertStringContainsString('pb-mobile-columns-1', $html);
        $this->assertStringContainsString('pb-mobile-direction-vertical', $html);
        $this->assertStringContainsString('pb-columns--mobile-stack', $html);
        $this->assertSame($this->countBlocks($fixture['blocks']), substr_count($html, 'data-block-id='));
        $this->assertStringNotContainsString('<script', $html);
    }

    public function test_renderer_escapes_section_labels_and_placeholder_content(): void
    {
        $document = app(LayoutPreviewFixture::class)->document();
        $document['blocks'][0]['props']['ariaLabel'] = 'Company <b>layout & content</b>';
        $document['blocks'][0]['children'][0]['children'][0]['children'][0]['props']['label'] = '<b>Safe & escaped</b>';

        $html = app(PageDocumentRenderer::class)->render($document);

        $this->assertStringContainsString('aria-label="Company &lt;b&gt;layout &amp; content&lt;/b&gt;"', $html);
        $this->assertStringContainsString('&lt;b&gt;Safe &amp; escaped&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>Safe', $html);
    }

    public function test_validator_rejects_invalid_layout_nesting_and_arbitrary_css(): void
    {
        $document = app(LayoutPreviewFixture::class)->document();
        $document['blocks'][0]['children'][0]['children'][] = $this->block('layout.section', 'nested-section-0001');
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.4.type');

        $document = app(LayoutPreviewFixture::class)->document();
        $document['blocks'][0]['children'][0]['children'][2]['style']['desktop']['gridTemplateColumns'] = 'repeat(auto-fit, 1fr)';
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.2.style.desktop.gridTemplateColumns');

        $document = app(LayoutPreviewFixture::class)->document();
        $document['blocks'][0]['children'][0]['children'][0]['style']['desktop']['gap'] = '12px';
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.0.style.desktop.gap');
    }

    public function test_validator_enforces_mobile_grid_columns_column_presets_and_media_background(): void
    {
        $document = app(LayoutPreviewFixture::class)->document();
        $document['blocks'][0]['children'][0]['children'][2]['style']['mobile']['columns'] = '4';
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.2.style.mobile.columns');

        $document = app(LayoutPreviewFixture::class)->document();
        $document['blocks'][0]['children'][0]['children'][1]['props']['desktopPreset'] = 'equal-3';
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.1.children');

        $document = app(LayoutPreviewFixture::class)->document();
        $document['blocks'][0]['props']['background'] = 'media';
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.props.backgroundMediaId');
    }

    /** @return array<string, mixed> */
    private function block(string $type, string $id): array
    {
        $definition = app(BlockRegistry::class)->get($type);

        return ['id' => $id, 'type' => $type, 'version' => $definition->version, ...$definition->defaults];
    }

    /** @param list<array<string, mixed>> $blocks */
    private function countBlocks(array $blocks): int
    {
        $count = count($blocks);
        foreach ($blocks as $block) {
            $children = is_array($block['children'] ?? null) ? $block['children'] : [];
            $count += $this->countBlocks($children);
        }

        return $count;
    }

    /** @param callable(): mixed $callback */
    private function expectValidationPath(callable $callback, string $path): void
    {
        try {
            $callback();
            $this->fail('Expected a Page Document validation exception.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($path, $exception->errors());
        }
    }
}
