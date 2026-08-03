<?php

namespace Tests\Feature\Seo;

use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\SeoMeta;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_sitemap_contains_published_canonical_urls_hreflang_and_x_default(): void
    {
        $published = $this->product('published');
        $draft = $this->product('draft');
        $this->meta($published, 'vi', 'https://hongvan.local/san-pham/xanh');
        $this->meta($published, 'en', 'https://hongvan.local/en/products/green');
        $this->meta($draft, 'vi', 'https://hongvan.local/san-pham/nhap');

        $index = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();
        $this->assertStringContainsString('/sitemaps/product-vi.xml', $index);

        $xml = $this->get('/sitemaps/product-vi.xml')->assertOk()->getContent();
        $this->assertStringContainsString('https://hongvan.local/san-pham/xanh', $xml);
        $this->assertStringContainsString('hreflang="en"', $xml);
        $this->assertStringContainsString('hreflang="x-default"', $xml);
        $this->assertStringNotContainsString('/san-pham/nhap', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_sitemap_excludes_noindex_and_missing_canonical_records(): void
    {
        $hidden = $this->product('published');
        $this->meta($hidden, 'vi', 'https://hongvan.local/san-pham/an', false);
        $this->assertStringNotContainsString('product-vi.xml', $this->get('/sitemap.xml')->assertOk()->getContent());
        $this->get('/sitemaps/product-vi.xml')->assertNotFound();
    }

    private function product(string $status): Product
    {
        $product = Product::factory()->create(['status' => $status, 'published_at' => $status === 'published' ? now('UTC') : null]);
        ProductTranslation::query()->create(['product_id' => $product->getKey(), 'locale' => 'vi', 'name' => 'Sản phẩm', 'slug' => 'san-pham-'.$product->getKey()]);

        return $product;
    }

    private function meta(Product $product, string $locale, string $url, bool $index = true): void
    {
        SeoMeta::query()->create(['seoable_type' => 'product', 'seoable_id' => $product->getKey(), 'locale' => $locale, 'canonical_url' => $url, 'robots_index' => $index, 'robots_follow' => true, 'og_type' => 'product', 'twitter_card' => 'summary_large_image']);
    }
}
