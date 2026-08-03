<?php

namespace Tests\Feature\Search;

use App\Domain\Search\PublicSearchQuery;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SearchLog;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PublicSearchTest extends TestCase
{
    use DatabaseTruncation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    public function test_search_matches_vietnamese_without_accents_and_never_exposes_drafts(): void
    {
        $published = $this->product('PUB-SEARCH', 'published', 'Phân bón hữu cơ Hồng Vân', 'phan-bon-huu-co');
        $this->product('DRAFT-SEARCH', 'draft', 'Phân bón hữu cơ bí mật', 'phan-bon-bi-mat');
        $this->assertSame('phan bon huu co hong van giai phap phan bon cho cay trong', $published->translations()->firstOrFail()->search_text);
        $score = DB::table('hongvan_product_translations')->selectRaw('MATCH (search_text) AGAINST (? IN BOOLEAN MODE) as score', ['+phan* +bon*'])->where('product_id', $published->getKey())->value('score');
        $this->assertGreaterThan(0, (float) $score);
        $this->assertCount(1, app(PublicSearchQuery::class)->paginate('phan bon', 'vi', ['products'])->items());

        $this->getJson('/api/public/v1/search?q=phan%20bon', ['X-Locale' => 'vi'])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.public_id', $published->public_id)
            ->assertJsonPath('data.0.type', 'products')
            ->assertJsonPath('meta.pagination.total', 1);
    }

    public function test_type_filter_is_allowlisted_and_injection_input_does_not_change_query_structure(): void
    {
        $this->product('PUB-TYPE', 'published', 'Phân bón chuyên dùng', 'phan-bon-chuyen-dung');

        $this->getJson('/api/public/v1/search?q=phan%20bon&types[]=posts', ['X-Locale' => 'vi'])
            ->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/public/v1/search?q=phan%20bon&types[]=unknown', ['X-Locale' => 'vi'])
            ->assertUnprocessable()->assertJsonValidationErrors('types.0');
        $this->getJson('/api/public/v1/search?q='.rawurlencode("phan bon' OR 1=1 --"), ['X-Locale' => 'vi'])
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_empty_long_no_result_and_inactive_locale_are_handled_safely(): void
    {
        $this->getJson('/api/public/v1/search?q=', ['X-Locale' => 'vi'])->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/public/v1/search?q=%20%20%20', ['X-Locale' => 'vi'])->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/public/v1/search?q='.str_repeat('a', 101), ['X-Locale' => 'vi'])->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/public/v1/search?q=khongcoketqua', ['X-Locale' => 'vi'])->assertOk()->assertJsonCount(0, 'data');

        Language::query()->where('locale', 'en')->update(['is_active' => false]);
        $this->getJson('/api/public/v1/search?q=fertilizer', ['X-Locale' => 'en'])->assertNotFound();
    }

    public function test_highlight_escapes_stored_and_requested_markup(): void
    {
        $query = app(PublicSearchQuery::class);
        $highlighted = $query->highlight('<script>alert(1)</script> Phân bón', '<img src=x> Phân');

        $this->assertStringNotContainsString('<script>', $highlighted);
        $this->assertStringNotContainsString('<img', $highlighted);
        $this->assertStringContainsString('alert(1)', $highlighted);
        $this->assertStringContainsString('<mark>Phân</mark>', $highlighted);
    }

    public function test_analytics_is_opt_in_and_redacts_personal_data_without_storing_raw_ip(): void
    {
        config()->set('search.analytics_enabled', true);
        config()->set('search.analytics_hash_key', 'test-rotation-key');

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.25'])
            ->getJson('/api/public/v1/search?q='.rawurlencode('phamloi@example.com 0909 123 456'), ['X-Locale' => 'vi'])
            ->assertOk();

        $log = SearchLog::query()->sole();
        $this->assertSame('[email] [phone]', $log->normalized_term);
        $this->assertNotSame('203.0.113.25', $log->visitor_hash);
        $this->assertSame(64, strlen((string) $log->visitor_hash));
    }

    public function test_public_search_has_a_dedicated_rate_limit(): void
    {
        config()->set('security.rate_limits.public_search_per_minute', 2);

        $this->getJson('/api/public/v1/search?q=phan', ['X-Locale' => 'vi'])->assertOk();
        $this->getJson('/api/public/v1/search?q=bon', ['X-Locale' => 'vi'])->assertOk();
        $this->getJson('/api/public/v1/search?q=lua', ['X-Locale' => 'vi'])->assertTooManyRequests();
    }

    public function test_related_products_use_explicit_category_taxonomy_and_exclude_drafts(): void
    {
        $category = ProductCategory::query()->create(['code' => 'RELATED', 'is_active' => true, 'sort_order' => 1]);
        $source = $this->product('RELATED-SOURCE', 'published', 'Nguồn liên quan', 'nguon-lien-quan', $category->getKey());
        $related = $this->product('RELATED-PUBLISHED', 'published', 'Sản phẩm liên quan', 'san-pham-lien-quan', $category->getKey());
        $draft = $this->product('RELATED-DRAFT', 'draft', 'Bản nháp liên quan', 'ban-nhap-lien-quan', $category->getKey());

        $this->getJson('/api/public/v1/related/products/'.$source->public_id, ['X-Locale' => 'vi'])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.public_id', $related->public_id);
        $this->getJson('/api/public/v1/related/products/'.$draft->public_id, ['X-Locale' => 'vi'])
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_reindex_health_command_reports_all_fulltext_indexes(): void
    {
        $this->artisan('search:reindex', ['--health' => true])
            ->expectsOutputToContain('[OK] hongvan_product_translations')
            ->expectsOutputToContain('[OK] hongvan_post_translations')
            ->expectsOutputToContain('[PLAN] hongvan_product_translations: access=fulltext')
            ->assertSuccessful();
    }

    private function product(string $sku, string $status, string $name, string $slug, ?int $categoryId = null): Product
    {
        $product = Product::query()->create([
            'sku' => $sku,
            'status' => $status,
            'product_category_id' => $categoryId,
            'is_featured' => false,
            'price_mode' => 'contact',
            'currency' => 'VND',
            'is_price_visible' => false,
            'published_at' => $status === 'published' ? now('UTC')->subMinute() : null,
        ]);
        $product->translations()->create([
            'locale' => 'vi',
            'name' => $name,
            'slug' => $slug.'-'.Str::lower($sku),
            'short_description' => 'Giải pháp phân bón cho cây trồng',
        ]);

        return $product;
    }
}
