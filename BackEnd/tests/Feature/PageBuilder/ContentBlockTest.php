<?php

namespace Tests\Feature\PageBuilder;

use App\Domain\PageBuilder\BlockRegistry;
use App\Domain\PageBuilder\PageDocumentRenderer;
use App\Domain\PageBuilder\PageDocumentSchema;
use App\Domain\PageBuilder\PageDocumentValidator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class ContentBlockTest extends TestCase
{
    public function test_content_registry_contains_typed_defaults_for_every_required_block(): void
    {
        $types = collect(app(BlockRegistry::class)->metadata())
            ->where('category', 'content')
            ->pluck('type')
            ->all();

        $this->assertSame([
            'content.heading', 'content.rich-text', 'content.button', 'content.icon', 'content.list',
            'content.quote', 'content.table', 'content.badge', 'content.card', 'content.faq',
        ], $types);
    }

    public function test_content_fixture_renders_semantic_escaped_and_verified_faq_markup(): void
    {
        $document = $this->document([
            $this->block('content.heading', 'content-heading-01', ['text' => 'Company <safe>', 'level' => 1, 'anchorId' => 'company']),
            $this->block('content.rich-text', 'content-richtext-01', ['html' => '<p><strong>Safe content</strong></p>']),
            $this->block('content.button', 'content-button-001', ['label' => 'Contact', 'url' => '/contact', 'target' => '_blank', 'variant' => 'primary']),
            $this->block('content.icon', 'content-icon-00001', ['name' => 'leaf', 'label' => 'Sustainable', 'decorative' => false, 'tone' => 'brand']),
            $this->block('content.list', 'content-list-00001', ['ordered' => false, 'items' => ['First', 'Second']]),
            $this->block('content.quote', 'content-quote-0001', ['text' => 'A verified quotation.', 'attribution' => 'Source', 'citeUrl' => 'https://example.com/source']),
            $this->block('content.table', 'content-table-0001', ['caption' => 'Specifications', 'headers' => ['Name', 'Value'], 'rows' => [['Type', 'Organic']]]),
            $this->block('content.badge', 'content-badge-0001', ['text' => 'Featured', 'tone' => 'positive']),
            $this->block('content.card', 'content-card-00001', ['title' => 'Card', 'body' => 'Card body', 'linkLabel' => 'Read', 'linkUrl' => '/read', 'target' => '_self', 'tone' => 'surface']),
            $this->block('content.faq', 'content-faq-000001', ['heading' => 'FAQ', 'verified' => true, 'items' => [['question' => 'Question?', 'answer' => '<p>Verified answer.</p>']]]),
        ]);

        $html = app(PageDocumentRenderer::class)->render($document);

        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('Company &lt;safe&gt;', $html);
        $this->assertStringContainsString('<strong>Safe content</strong>', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('<details>', $html);
        $this->assertStringContainsString('"@type":"FAQPage"', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
    }

    public function test_rich_text_removes_unsafe_urls_and_rejects_executable_payloads(): void
    {
        $document = $this->document([
            $this->block('content.rich-text', 'content-richtext-02', ['html' => '<p><a href="data:image/png;base64,bad">Safe label</a></p>']),
        ]);
        $validated = app(PageDocumentValidator::class)->validate($document);
        $this->assertSame('<p><a>Safe label</a></p>', data_get($validated, 'blocks.0.children.0.children.0.children.0.props.html'));

        $document = $this->document([
            $this->block('content.rich-text', 'content-richtext-03', ['html' => '<script>alert(1)</script><p>Text</p>']),
        ]);
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.0.children.0.props.html');

        $document = $this->document([
            $this->block('content.button', 'content-button-002', ['label' => 'Unsafe', 'url' => 'ftp://example.com/file', 'target' => '_self', 'variant' => 'primary']),
        ]);
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.0.children.0.props.url');
    }

    public function test_heading_table_link_and_accessibility_policies_are_enforced(): void
    {
        $document = $this->document([
            $this->block('content.heading', 'content-heading-02', ['text' => 'First', 'level' => 1, 'anchorId' => 'first']),
            $this->block('content.heading', 'content-heading-03', ['text' => 'Second', 'level' => 1, 'anchorId' => 'second']),
        ]);
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.0.children.1.props.level');

        $document = $this->document([
            $this->block('content.table', 'content-table-0002', ['caption' => 'Broken', 'headers' => ['A', 'B'], 'rows' => [['Only one']]]),
        ]);
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.0.children.0.props.rows.0');

        $document = $this->document([
            $this->block('content.icon', 'content-icon-00002', ['name' => 'star', 'label' => '', 'decorative' => false, 'tone' => 'brand']),
        ]);
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.0.children.0.props.label');
    }

    /** @param list<array<string, mixed>> $content */
    private function document(array $content): array
    {
        $document = PageDocumentSchema::emptyDocument();
        $document['blocks'] = [$this->block('layout.section', 'content-section-01', null, [
            $this->block('layout.container', 'content-container-01', null, [
                $this->block('layout.stack', 'content-stack-0001', null, $content),
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
