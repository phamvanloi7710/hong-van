<?php

namespace Tests\Feature\PageBuilder;

use App\Domain\PageBuilder\BlockRegistry;
use App\Domain\PageBuilder\FormRegistry;
use App\Domain\PageBuilder\PageDocumentRenderer;
use App\Domain\PageBuilder\PageDocumentSchema;
use App\Domain\PageBuilder\PageDocumentValidator;
use App\Domain\PageBuilder\PageRenderOptions;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class FormBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_registry_exposes_only_four_versioned_fixed_form_contracts(): void
    {
        $forms = collect(app(FormRegistry::class)->metadata());

        $this->assertSame([
            'form.contact',
            'form.product-quote',
            'form.transport-request',
            'form.warehouse-request',
        ], $forms->pluck('blockType')->all());
        $this->assertSame([1, 1, 1, 1], $forms->pluck('version')->all());
        $forms->each(function (array $form): void {
            $this->assertArrayNotHasKey('action', $form);
            $this->assertArrayNotHasKey('endpointRoute', $form);
            $this->assertNotEmpty($form['labels']['vi']);
            $this->assertNotEmpty($form['labels']['en']);
            $this->assertNotEmpty($form['labels']['zh']);

            foreach ($form['fields'] as $field) {
                $this->assertArrayHasKey('required', $field);
                $this->assertArrayHasKey('validationPreset', $field);
                $this->assertArrayHasKey('consent', $field);
                $this->assertArrayHasKey('layout', $field);
                $this->assertNotEmpty($field['labels']['vi']);
                $this->assertNotEmpty($field['help']['vi']);
            }
        });

        $blocks = collect(app(BlockRegistry::class)->metadata())->where('category', 'form')->values();
        $this->assertSame($forms->pluck('blockType')->all(), $blocks->pluck('type')->all());
        $blocks->each(function (array $block): void {
            $this->assertFalse($block['allowRoot']);
            $this->assertSame([], $block['allowedChildren']);
            $definition = app(BlockRegistry::class)->get($block['type']);
            $this->assertSame(false, $definition->bindingsSchema['additionalProperties']);
        });
    }

    public function test_validator_rejects_arbitrary_fields_actions_and_executable_content(): void
    {
        $document = $this->document($this->formBlock('form.contact'));
        $document['blocks'][0]['children'][0]['children'][0]['children'][0]['bindings']['action'] = 'https://attacker.test/collect';
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.0.children.0.bindings.action');

        $document = $this->document($this->formBlock('form.contact'));
        $document['blocks'][0]['children'][0]['children'][0]['children'][0]['props']['fields'] = [['name' => 'password']];
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.0.children.0.props.fields');

        $document = $this->document($this->formBlock('form.contact'));
        $document['blocks'][0]['children'][0]['children'][0]['children'][0]['props']['title'] = '<script>alert(1)</script>';
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.0.children.0.props.title');
    }

    public function test_contact_form_renders_csrf_honeypot_accessibility_and_fixed_endpoint(): void
    {
        $html = app(PageDocumentRenderer::class)->render(
            $this->document($this->formBlock('form.contact')),
            PageRenderOptions::published('vi'),
        );

        $this->assertStringContainsString('<form class="pb-form" method="POST" action="'.route('public.forms.contact').'"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('name="_form_definition" value="contact@1"', $html);
        $this->assertStringContainsString('name="website"', $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
        $this->assertStringContainsString('aria-describedby=', $html);
        $this->assertStringNotContainsString('attacker.test', $html);
    }

    public function test_product_quote_requires_and_signs_the_product_page_context(): void
    {
        $document = $this->document($this->formBlock('form.product-quote'));
        $missing = app(PageDocumentRenderer::class)->render($document, PageRenderOptions::published('vi'));
        $this->assertStringContainsString('pb-form-block__notice', $missing);
        $this->assertStringNotContainsString('<form class="pb-form"', $missing);

        $product = Product::factory()->published()->create();
        $html = app(PageDocumentRenderer::class)->render(
            $document,
            new PageRenderOptions('en', false, false, 'product', $product->public_id),
        );

        $this->assertStringContainsString('action="'.route('public.forms.localized.quote', ['locale' => 'en']).'"', $html);
        $this->assertStringContainsString('name="items[0][product_id]" value="'.$product->public_id.'"', $html);
        $this->assertStringContainsString('name="form_context_token"', $html);
        $this->assertStringContainsString('name="_form_definition" value="product_quote@1"', $html);
        $this->assertStringContainsString('Request a product quote', $html);
        $this->assertStringContainsString('>Request quote</button>', $html);
        $this->assertStringNotContainsString('Yêu cầu báo giá', $html);
    }

    /** @return array<string, mixed> */
    private function formBlock(string $type): array
    {
        $definition = app(BlockRegistry::class)->get($type);

        return ['id' => str_replace('.', '-', $type).'-01', 'type' => $type, 'version' => $definition->version, ...$definition->defaults];
    }

    /** @return array<string, mixed> */
    private function document(array $form): array
    {
        $document = PageDocumentSchema::emptyDocument();
        $document['blocks'] = [$this->layout('layout.section', 'form-section-01', [
            $this->layout('layout.container', 'form-container-01', [
                $this->layout('layout.stack', 'form-stack-0001', [$form]),
            ]),
        ])];

        return $document;
    }

    /** @param list<array<string, mixed>> $children @return array<string, mixed> */
    private function layout(string $type, string $id, array $children): array
    {
        $definition = app(BlockRegistry::class)->get($type);

        return ['id' => $id, 'type' => $type, 'version' => $definition->version, ...$definition->defaults, 'children' => $children];
    }

    /** @param callable(): mixed $callback */
    private function expectValidationPath(callable $callback, string $path): void
    {
        try {
            $callback();
            $this->fail('Expected validation exception.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($path, $exception->errors());
        }
    }
}
