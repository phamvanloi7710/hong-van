<?php

namespace Tests\Feature\PageBuilder;

use App\Domain\PageBuilder\BlockRegistry;
use App\Domain\PageBuilder\DataSourceRegistry;
use App\Domain\PageBuilder\PageBuilderCacheKeys;
use App\Domain\PageBuilder\PageDocumentRenderer;
use App\Domain\PageBuilder\PageDocumentSchema;
use App\Domain\PageBuilder\PageDocumentValidator;
use App\Domain\PageBuilder\PageRenderOptions;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class DynamicBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_registry_exposes_all_business_blocks_and_allowlisted_data_sources(): void
    {
        $blocks = collect(app(BlockRegistry::class)->metadata())->where('category', 'business')->values();
        $this->assertCount(15, $blocks);
        $this->assertSame([
            'business.hero', 'business.product-grid', 'business.category-grid', 'business.crop-grid',
            'business.service-grid', 'business.fleet', 'business.route-list', 'business.warehouse-cards',
            'business.stats', 'business.partner-logos', 'business.certificate-list', 'business.project-list',
            'business.post-list', 'business.cta', 'business.breadcrumb',
        ], $blocks->pluck('type')->all());
        $this->assertSame([
            'products', 'product_categories', 'crop_solutions', 'services', 'fleet', 'routes', 'warehouses',
            'stats', 'partners', 'certifications', 'projects', 'posts',
        ], collect(app(DataSourceRegistry::class)->metadata())->pluck('key')->all());
        $blocks->each(fn (array $block) => $this->assertArrayHasKey('cacheTags', $block));
    }

    public function test_binding_rejects_query_injection_unknown_columns_and_limits_above_maximum(): void
    {
        $document = $this->document($this->block());
        $document['blocks'][0]['children'][0]['children'][0]['children'][0]['bindings']['filters']['rawSql'] = '1=1; DROP TABLE hongvan_products';
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.0.children.0.bindings.filters.rawSql');

        $document = $this->document($this->block());
        $document['blocks'][0]['children'][0]['children'][0]['children'][0]['bindings']['limit'] = 25;
        $this->expectValidationPath(fn () => app(PageDocumentValidator::class)->validate($document), 'document.blocks.0.children.0.children.0.children.0.bindings.limit');

        try {
            app(DataSourceRegistry::class)->normalize(['source' => 'products', 'filters' => [], 'sort' => 'password', 'limit' => 8, 'preset' => 'default']);
            $this->fail('Expected allowlist validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('bindings.sort', $exception->errors());
        }
    }

    public function test_public_render_returns_only_published_localized_items_and_has_stable_empty_state(): void
    {
        $published = Product::factory()->published()->create();
        ProductTranslation::query()->create(['product_id' => $published->id, 'locale' => 'vi', 'name' => 'Sản phẩm Việt', 'slug' => 'san-pham-viet']);
        ProductTranslation::query()->create(['product_id' => $published->id, 'locale' => 'en', 'name' => 'Published English product', 'slug' => 'published-product']);
        $draft = Product::factory()->draft()->create();
        ProductTranslation::query()->create(['product_id' => $draft->id, 'locale' => 'en', 'name' => 'Draft must stay hidden', 'slug' => 'draft-hidden']);

        $html = app(PageDocumentRenderer::class)->render($this->document($this->block()), PageRenderOptions::published('en'));
        $this->assertStringContainsString('Published English product', $html);
        $this->assertStringNotContainsString('Sản phẩm Việt', $html);
        $this->assertStringNotContainsString('Draft must stay hidden', $html);

        Product::query()->update(['status' => 'draft', 'published_at' => null]);
        $empty = app(PageDocumentRenderer::class)->render($this->document($this->block()), PageRenderOptions::published('en'));
        $this->assertStringContainsString('pb-business__empty', $empty);
        $this->assertStringContainsString('hidden', $empty);
        $this->assertStringNotContainsString('Preview sample item', $empty);
    }

    public function test_sample_data_is_preview_only_and_duplicate_bindings_are_loaded_once(): void
    {
        $document = $this->document($this->block(), $this->block('business-product-grid-02'));
        $public = app(PageDocumentRenderer::class)->render($document, new PageRenderOptions('en', false, true));
        $this->assertStringNotContainsString('Preview sample item', $public);

        $preview = app(PageDocumentRenderer::class)->render($document, new PageRenderOptions('en', true, true));
        $this->assertStringContainsString('Preview sample item', $preview);
        $this->assertSame(2, substr_count($preview, 'data-preview-sample="true"'));

        Product::factory()->published()->create();
        DB::flushQueryLog();
        DB::enableQueryLog();
        app(PageDocumentRenderer::class)->render($document, PageRenderOptions::published('vi'));
        $productQueries = collect(DB::getQueryLog())->filter(function (array $query): bool {
            $sql = strtolower((string) $query['query']);

            return str_starts_with($sql, 'select') && str_contains($sql, 'hongvan_products');
        })->count();
        DB::disableQueryLog();
        $this->assertSame(1, $productQueries);
    }

    public function test_cache_dependency_tags_are_composable_without_arbitrary_tags(): void
    {
        $tags = PageBuilderCacheKeys::withDataSources(['page-builder', 'page:01'], ['data:products', 'unsafe', 'data:products']);
        $this->assertSame(['page-builder', 'page:01', 'data:products'], $tags);
    }

    /** @return array<string,mixed> */
    private function block(string $id = 'business-product-grid-01'): array
    {
        $definition = app(BlockRegistry::class)->get('business.product-grid');

        return ['id' => $id, 'type' => $definition->type, 'version' => $definition->version, ...$definition->defaults];
    }

    /** @param array<string,mixed> ...$blocks @return array<string,mixed> */
    private function document(array ...$blocks): array
    {
        $document = PageDocumentSchema::emptyDocument();
        $document['blocks'] = [$this->layout('layout.section', 'dynamic-section-01', [
            $this->layout('layout.container', 'dynamic-container-01', [
                $this->layout('layout.stack', 'dynamic-stack-0001', $blocks),
            ]),
        ])];

        return $document;
    }

    /** @param list<array<string,mixed>> $children @return array<string,mixed> */
    private function layout(string $type, string $id, array $children): array
    {
        $definition = app(BlockRegistry::class)->get($type);

        return ['id' => $id, 'type' => $type, 'version' => $definition->version, ...$definition->defaults, 'children' => $children];
    }

    /** @param callable():mixed $callback */
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
