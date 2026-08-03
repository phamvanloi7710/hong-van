<?php

namespace Tests\Feature\Seo;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\Seo\SeoMetaResolver;
use App\Models\Media;
use App\Models\MediaVariant;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CompanySettingsSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PermissionSeeder::class, CompanySettingsSeeder::class]);
    }

    public function test_super_admin_can_manage_typed_locale_metadata_with_media_usage(): void
    {
        $this->actingAs($this->superAdmin());
        $product = $this->product();
        $image = $this->image();

        $this->getJson('/api/admin/v1/seo-meta/entities?type=product&locale=vi')
            ->assertOk()
            ->assertJsonPath('data.0.public_id', $product->public_id)
            ->assertJsonPath('data.0.label', 'Phân bón Hồng Vân');

        $payload = $this->payload($image->public_id);
        $this->putJson('/api/admin/v1/seo-meta/product/'.$product->public_id, $payload)
            ->assertOk()
            ->assertJsonPath('data.meta_title', 'Phân bón chuyên dụng')
            ->assertJsonPath('data.og_image.public_id', $image->public_id)
            ->assertJsonPath('data.og_image.variants.0.key', 'preview');

        $this->assertDatabaseHas('hongvan_seo_meta', ['seoable_type' => 'product', 'seoable_id' => $product->getKey(), 'locale' => 'vi']);
        $this->assertDatabaseHas('hongvan_media_usages', ['media_id' => $image->getKey(), 'owner_type' => 'seo_meta', 'field' => 'og_image']);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'seo.meta.created', 'subject_type' => 'seo_meta']);

        $this->getJson('/api/admin/v1/seo-meta/product/'.$product->public_id.'?locale=vi')
            ->assertOk()
            ->assertJsonPath('data.canonical_url', 'https://hongvan.local/san-pham/phan-bon-hong-van');
    }

    public function test_permissions_type_allowlist_and_malicious_canonical_are_enforced(): void
    {
        $product = $this->product();
        $this->actingAs(User::factory()->create());
        $this->getJson('/api/admin/v1/seo-meta/entities?type=product&locale=vi')->assertForbidden();

        $this->actingAs($this->superAdmin());
        $payload = $this->payload(null);
        $payload['canonical_url'] = 'javascript:alert(1)';
        $this->putJson('/api/admin/v1/seo-meta/product/'.$product->public_id, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('canonical_url');
        $this->getJson('/api/admin/v1/seo-meta/unknown/'.$product->public_id.'?locale=vi')->assertNotFound();
    }

    public function test_resolver_applies_fallback_noindex_and_hreflang_hooks(): void
    {
        $product = $this->product('draft');
        $resolved = app(SeoMetaResolver::class)->resolve(
            'product',
            $product,
            'vi',
            'https://hongvan.local/san-pham/phan-bon-hong-van',
            'preview',
            ['meta_title' => 'Tiêu đề trang'],
            ['vi' => 'https://hongvan.local/vi/san-pham', 'en' => 'https://hongvan.local/en/products', 'evil' => 'javascript:alert(1)'],
        );

        $this->assertSame('Tiêu đề trang', $resolved['title']);
        $this->assertSame('Mô tả cũ', $resolved['description']);
        $this->assertSame('noindex, nofollow', $resolved['robots']);
        $this->assertCount(2, $resolved['alternates']);
    }

    public function test_blade_component_escapes_values_and_emits_each_tag_once(): void
    {
        $meta = [
            'title' => '<script>alert(1)</script>',
            'description' => 'Mô tả "an toàn"',
            'canonical_url' => 'https://hongvan.local/products',
            'robots' => 'index, follow',
            'og' => ['title' => '<b>OG</b>', 'description' => 'Mô tả', 'type' => 'product', 'image' => null],
            'twitter' => ['card' => 'summary', 'title' => 'Twitter', 'description' => 'Mô tả'],
            'alternates' => [],
        ];
        $html = Blade::render('<x-seo.meta :meta="$meta" /><x-seo.meta :meta="$meta" />', compact('meta'));

        $this->assertSame(1, substr_count($html, '<title>'));
        $this->assertSame(1, substr_count($html, 'rel="canonical"'));
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_admin_and_preview_responses_send_noindex_header(): void
    {
        $this->actingAs($this->superAdmin());
        $this->getJson('/api/admin/v1/seo-meta/entities?type=product&locale=vi')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_seo_table_and_every_column_have_database_comments(): void
    {
        $table = DB::selectOne(
            'SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            ['hongvan_seo_meta'],
        );
        $columns = DB::select(
            'SELECT COLUMN_NAME, COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            ['hongvan_seo_meta'],
        );

        $this->assertNotEmpty($table?->TABLE_COMMENT);
        $this->assertCount(22, $columns);
        foreach ($columns as $column) {
            $this->assertNotEmpty($column->COLUMN_COMMENT, 'Missing comment for hongvan_seo_meta.'.$column->COLUMN_NAME);
        }
    }

    private function product(string $status = 'published'): Product
    {
        $product = Product::factory()->create(['status' => $status, 'published_at' => $status === 'published' ? now('UTC') : null]);
        ProductTranslation::query()->create([
            'product_id' => $product->getKey(), 'locale' => 'vi', 'name' => 'Phân bón Hồng Vân',
            'slug' => 'phan-bon-hong-van', 'short_description' => null, 'description' => null,
            'benefits' => null, 'usage_instructions' => null, 'meta_title' => 'Tiêu đề cũ', 'meta_description' => 'Mô tả cũ',
        ]);

        return $product;
    }

    private function image(): Media
    {
        $media = Media::query()->create([
            'disk' => 'public', 'path' => 'media/seo.png', 'original_filename' => 'seo.png',
            'normalized_filename' => 'seo.png', 'extension' => 'png', 'mime_type' => 'image/png',
            'size_bytes' => 100, 'checksum_sha256' => hash('sha256', 'seo'), 'width' => 1200, 'height' => 630,
            'status' => 'ready', 'visibility' => 'public', 'is_locked' => false,
        ]);
        MediaVariant::query()->create([
            'media_id' => $media->getKey(), 'variant_key' => 'preview', 'disk' => 'public', 'path' => 'media/seo-preview.webp',
            'extension' => 'webp', 'mime_type' => 'image/webp', 'size_bytes' => 80, 'checksum_sha256' => hash('sha256', 'seo-preview'),
            'width' => 1200, 'height' => 630, 'status' => 'ready', 'generated_at' => now('UTC'),
        ]);

        return $media;
    }

    /** @return array<string, mixed> */
    private function payload(?string $imageId): array
    {
        return [
            'locale' => 'vi', 'meta_title' => 'Phân bón chuyên dụng', 'meta_description' => 'Mô tả SEO',
            'canonical_url' => 'https://hongvan.local/san-pham/phan-bon-hong-van', 'robots_index' => true,
            'robots_follow' => true, 'og_title' => null, 'og_description' => null, 'og_image_media_id' => $imageId,
            'og_type' => 'product', 'twitter_card' => 'summary_large_image', 'twitter_title' => null,
            'twitter_description' => null, 'focus_keywords' => ['phân bón', 'Hồng Vân'],
        ];
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail(), ['created_at' => now('UTC')]);

        return $user;
    }
}
