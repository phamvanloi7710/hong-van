<?php

namespace Tests\Feature\Seo;

use App\Domain\Products\ProductPriceMode;
use App\Domain\Seo\StructuredDataBuilder;
use App\Models\Product;
use App\Models\ProductTranslation;
use Database\Seeders\CompanySettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

final class StructuredDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompanySettingsSeeder::class);
    }

    public function test_product_offer_is_emitted_only_for_public_determined_prices(): void
    {
        $builder = app(StructuredDataBuilder::class);
        $fixed = $this->product(ProductPriceMode::Fixed, '150000.0000', true);
        $this->assertSame('Offer', $builder->product($fixed, 'vi', 'https://hongvan.local/p')['offers']['@type']);

        $contact = $this->product(ProductPriceMode::Contact, null, true);
        $hidden = $this->product(ProductPriceMode::Fixed, '150000.0000', false);
        $zero = $this->product(ProductPriceMode::Fixed, '0.0000', true);
        $this->assertArrayNotHasKey('offers', $builder->product($contact, 'vi', 'https://hongvan.local/c'));
        $this->assertArrayNotHasKey('offers', $builder->product($hidden, 'vi', 'https://hongvan.local/h'));
        $this->assertArrayNotHasKey('offers', $builder->product($zero, 'vi', 'https://hongvan.local/z'));
    }

    public function test_schema_builders_emit_valid_safe_json_from_real_values(): void
    {
        $builder = app(StructuredDataBuilder::class);
        $schema = $builder->breadcrumbs([
            ['name' => '<b>Trang chủ</b>', 'url' => 'https://hongvan.local/'],
            ['name' => '<script>alert(1)</script>Sản phẩm', 'url' => 'https://hongvan.local/products'],
        ]);
        $json = $builder->encode($schema);
        $this->assertSame('BreadcrumbList', $schema['@type']);
        $this->assertJson($json);
        $this->assertStringNotContainsString('</script>', $json);
        $this->assertSame('Organization', $builder->organization()['@type']);
        $this->assertSame('WebSite', $builder->website('zh')['@type']);
    }

    public function test_faq_rejects_empty_or_fabricated_entries(): void
    {
        $this->expectException(InvalidArgumentException::class);
        app(StructuredDataBuilder::class)->faq([['question' => 'Câu hỏi', 'answer' => '']]);
    }

    private function product(ProductPriceMode $mode, ?string $amount, bool $visible): Product
    {
        $product = Product::factory()->create(['price_mode' => $mode, 'price_amount' => $amount, 'currency' => 'VND', 'is_price_visible' => $visible]);
        ProductTranslation::query()->create(['product_id' => $product->getKey(), 'locale' => 'vi', 'name' => 'Phân bón', 'slug' => 'phan-bon-'.$product->getKey()]);

        return $product;
    }
}
