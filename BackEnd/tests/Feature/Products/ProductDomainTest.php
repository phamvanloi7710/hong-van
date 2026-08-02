<?php

namespace Tests\Feature\Products;

use App\Domain\Products\ProductPriceData;
use App\Domain\Products\ProductPriceMode;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use Database\Seeders\ProductCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ProductDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_schema_contains_only_presentation_and_quote_entities(): void
    {
        $expectedTables = [
            'hongvan_product_categories',
            'hongvan_product_category_translations',
            'hongvan_brands',
            'hongvan_brand_translations',
            'hongvan_products',
            'hongvan_product_translations',
            'hongvan_product_media',
            'hongvan_product_tags',
            'hongvan_product_tag_links',
            'hongvan_product_attribute_definitions',
            'hongvan_product_attribute_values',
            'hongvan_product_specifications',
            'hongvan_product_related',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(Schema::hasTable($table), $table.' is missing.');
        }

        foreach (['hongvan_inventories', 'hongvan_carts', 'hongvan_orders', 'hongvan_checkouts', 'hongvan_payments'] as $table) {
            $this->assertFalse(Schema::hasTable($table), $table.' must not be part of the catalog domain.');
        }
    }

    public function test_factories_create_prefixed_catalog_models_and_locale_slugs(): void
    {
        $category = ProductCategory::factory()->create();
        $brand = Brand::factory()->create();
        $product = Product::factory()->fixedPrice('150000.0000')->published()->create([
            'product_category_id' => $category->getKey(),
            'brand_id' => $brand->getKey(),
        ]);

        $product->translations()->createMany([
            ['locale' => 'vi', 'name' => 'Phân bón thử nghiệm', 'slug' => 'phan-bon-thu-nghiem'],
            ['locale' => 'en', 'name' => 'Test fertilizer', 'slug' => 'test-fertilizer'],
            ['locale' => 'zh', 'name' => '测试肥料', 'slug' => 'test-fertilizer-zh'],
        ]);

        $tag = ProductTag::query()->create(['name' => 'Nổi bật', 'slug' => 'noi-bat']);
        $product->tags()->attach($tag->getKey(), ['created_at' => now()->utc()]);

        $this->assertTrue(Str::isUlid($product->public_id));
        $this->assertSame($category->getKey(), $product->category->getKey());
        $this->assertSame($brand->getKey(), $product->brand->getKey());
        $this->assertCount(3, $product->translations);
        $this->assertTrue($product->tags->contains($tag));
        $this->assertSame(ProductPriceMode::Fixed, $product->price_mode);
        $this->assertSame('150000.0000', ProductPriceData::fromProduct($product)->amount);
    }

    public function test_minimal_product_seed_is_idempotent(): void
    {
        $this->seed(ProductCatalogSeeder::class);
        $this->seed(ProductCatalogSeeder::class);

        $this->assertDatabaseCount('hongvan_product_attribute_definitions', 3);
        $this->assertDatabaseHas('hongvan_product_attribute_definitions', [
            'code' => 'net_weight',
            'data_type' => 'decimal',
            'unit' => 'kg',
        ]);
    }
}
