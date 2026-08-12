<?php

namespace Tests\Feature\Products;

use App\Domain\Identity\PermissionRegistry;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_product_admin_api_enforces_permissions_and_query_allowlists(): void
    {
        $viewer = User::factory()->create();
        $this->actingAs($viewer);

        $this->getJson('/api/admin/v1/products')->assertForbidden();
        $viewer->permissionOverrides()->attach(
            Permission::query()->where('key', 'products.view')->firstOrFail(),
            ['is_allowed' => true],
        );

        $this->getJson('/api/admin/v1/products')->assertOk();
        $this->getJson('/api/admin/v1/products?filter[unsafe]=value')->assertUnprocessable();
        $this->postJson('/api/admin/v1/products', [])->assertForbidden();
    }

    public function test_bulk_status_cannot_exceed_the_actor_resource_permissions(): void
    {
        $viewer = User::factory()->create();
        $viewer->permissionOverrides()->attach(
            Permission::query()->where('key', 'products.view')->firstOrFail(),
            ['is_allowed' => true],
        );
        $product = Product::factory()->draft()->create();
        $this->actingAs($viewer);

        foreach (['publish', 'archive'] as $action) {
            $this->postJson('/api/admin/v1/products/bulk', [
                'action' => $action,
                'product_ids' => [$product->public_id],
            ])->assertForbidden();
        }

        $this->assertDatabaseHas('hongvan_products', [
            'id' => $product->getKey(),
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_manage_taxonomy_product_media_price_and_bulk_status(): void
    {
        $this->actingAs($this->superAdmin());
        $this->withHeader('X-Locale', 'vi');
        $categoryId = $this->postJson('/api/admin/v1/products/categories', $this->categoryPayload())
            ->assertCreated()
            ->assertJsonFragment(['locale' => 'vi', 'name' => 'Phân NPK'])
            ->json('data.public_id');
        $brandId = $this->postJson('/api/admin/v1/products/brands', $this->brandPayload())
            ->assertCreated()
            ->json('data.public_id');
        $tagId = $this->postJson('/api/admin/v1/products/tags', ['name' => 'NPK', 'slug' => 'npk'])
            ->assertCreated()
            ->json('data.public_id');
        $attributeId = $this->postJson('/api/admin/v1/products/attributes', [
            'code' => 'nitrogen_percent',
            'name' => 'Hàm lượng đạm',
            'data_type' => 'decimal',
            'unit' => '%',
            'options' => null,
            'is_filterable' => true,
            'is_required' => false,
            'sort_order' => 10,
        ])->assertCreated()->json('data.public_id');
        $media = $this->media();

        $productId = $this->postJson('/api/admin/v1/products', $this->productPayload(
            categoryId: $categoryId,
            brandId: $brandId,
            tagId: $tagId,
            attributeId: $attributeId,
            mediaId: $media->public_id,
        ))->assertCreated()
            ->assertJsonPath('data.price.display.label', '125.000 ₫')
            ->assertJsonPath('data.media.0.media_id', $media->public_id)
            ->assertJsonCount(3, 'data.translations')
            ->json('data.public_id');

        $this->assertDatabaseHas('hongvan_media_usages', [
            'media_id' => $media->getKey(),
            'owner_type' => 'product',
            'owner_public_id' => $productId,
            'field' => 'catalog_media',
        ]);
        Model::preventLazyLoading();
        try {
            $this->getJson('/api/admin/v1/products?search=HV-P33&filter[status]=draft&filter[category_id]='.$categoryId)
                ->assertOk()
                ->assertJsonPath('meta.pagination.total', 1)
                ->assertJsonPath('data.0.public_id', $productId);
        } finally {
            Model::preventLazyLoading(false);
        }
        $this->getJson('/api/admin/v1/products/'.$productId)
            ->assertOk()
            ->assertJsonPath('data.attributes.0.definition_id', $attributeId)
            ->assertJsonPath('data.tags.0.public_id', $tagId);

        $this->postJson('/api/admin/v1/products/'.$productId.'/publish')
            ->assertOk()
            ->assertJsonPath('data.status', 'published');
        $this->postJson('/api/admin/v1/products/bulk', [
            'action' => 'archive',
            'product_ids' => [$productId],
        ])->assertOk()->assertJsonPath('data.updated', 1);
        $this->assertDatabaseHas('hongvan_products', ['public_id' => $productId, 'status' => 'archived']);

        $this->deleteJson('/api/admin/v1/products/'.$productId)->assertOk();
        $this->postJson('/api/admin/v1/products/'.$productId.'/restore')->assertOk();
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'product.restored', 'subject_public_id' => $productId]);
    }

    public function test_price_slug_and_media_primary_validation_are_safe(): void
    {
        $this->actingAs($this->superAdmin());
        $payload = $this->productPayload();
        $payload['price']['amount'] = '0';
        $this->postJson('/api/admin/v1/products', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('price');

        $payload = $this->productPayload();
        $payload['price'] = [
            'mode' => 'range', 'amount' => null, 'minimum' => '200', 'maximum' => '100',
            'currency' => 'VND', 'unit' => null, 'note' => null, 'visible' => true,
        ];
        $this->postJson('/api/admin/v1/products', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('price');

        $this->postJson('/api/admin/v1/products/categories', $this->categoryPayload())->assertCreated();
        $duplicate = $this->categoryPayload('CAT-SECOND');
        $this->postJson('/api/admin/v1/products/categories', $duplicate)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('translations.0.slug');

        $mediaOne = $this->media('one.png');
        $mediaTwo = $this->media('two.png');
        $payload = $this->productPayload();
        $payload['media'] = [
            $this->mediaPayload($mediaOne->public_id, 0, true),
            $this->mediaPayload($mediaTwo->public_id, 1, true),
        ];
        $this->postJson('/api/admin/v1/products', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('media.1.is_primary');
    }

    /** @return array<string, mixed> */
    private function productPayload(
        ?string $categoryId = null,
        ?string $brandId = null,
        ?string $tagId = null,
        ?string $attributeId = null,
        ?string $mediaId = null,
    ): array {
        return [
            'sku' => 'HV-P33-001',
            'code' => 'SP-P33-001',
            'status' => 'draft',
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'origin' => 'Việt Nam',
            'packaging' => 'Bao 50 kg',
            'is_featured' => true,
            'published_at' => null,
            'unpublished_at' => null,
            'price' => [
                'mode' => 'fixed',
                'amount' => '125000.0000',
                'minimum' => null,
                'maximum' => null,
                'currency' => 'VND',
                'unit' => 'bao 50 kg',
                'note' => null,
                'visible' => true,
            ],
            'translations' => [
                $this->translation('vi', 'Phân bón P33', 'phan-bon-p33'),
                $this->translation('en', 'P33 fertilizer', 'p33-fertilizer'),
                $this->translation('zh', 'P33 肥料', 'p33-fertilizer-zh'),
            ],
            'media' => $mediaId === null ? [] : [$this->mediaPayload($mediaId, 0, true)],
            'tag_ids' => $tagId === null ? [] : [$tagId],
            'attributes' => $attributeId === null ? [] : [[
                'definition_id' => $attributeId,
                'locale' => '*',
                'value_text' => null,
                'value_decimal' => '16.0000',
                'value_boolean' => null,
                'value_json' => null,
            ]],
            'specifications' => [[
                'locale' => 'vi', 'label' => 'Khối lượng', 'value' => '50', 'unit' => 'kg', 'sort_order' => 0,
            ]],
            'related_product_ids' => [],
        ];
    }

    /** @return array<string, mixed> */
    private function categoryPayload(string $code = 'CAT-P33'): array
    {
        return [
            'parent_id' => null,
            'code' => $code,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 10,
            'translations' => [
                ['locale' => 'vi', 'name' => 'Phân NPK', 'slug' => 'phan-npk', 'summary' => null, 'meta_title' => null, 'meta_description' => null],
                ['locale' => 'en', 'name' => 'NPK fertilizer', 'slug' => 'npk-fertilizer', 'summary' => null, 'meta_title' => null, 'meta_description' => null],
                ['locale' => 'zh', 'name' => 'NPK 肥料', 'slug' => 'npk-fertilizer-zh', 'summary' => null, 'meta_title' => null, 'meta_description' => null],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function brandPayload(): array
    {
        return [
            'code' => 'BRAND-P33',
            'logo_media_id' => null,
            'is_active' => true,
            'sort_order' => 10,
            'translations' => [
                ['locale' => 'vi', 'name' => 'Hồng Vân', 'slug' => 'hong-van', 'description' => null, 'meta_title' => null, 'meta_description' => null],
                ['locale' => 'en', 'name' => 'Hong Van', 'slug' => 'hong-van-en', 'description' => null, 'meta_title' => null, 'meta_description' => null],
                ['locale' => 'zh', 'name' => '鸿运', 'slug' => 'hong-van-zh', 'description' => null, 'meta_title' => null, 'meta_description' => null],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function translation(string $locale, string $name, string $slug): array
    {
        return [
            'locale' => $locale, 'name' => $name, 'slug' => $slug, 'short_description' => null,
            'description' => null, 'benefits' => null, 'usage_instructions' => null,
            'meta_title' => null, 'meta_description' => null,
        ];
    }

    /** @return array<string, mixed> */
    private function mediaPayload(string $mediaId, int $sortOrder, bool $primary): array
    {
        return [
            'media_id' => $mediaId, 'role' => 'gallery', 'locale' => '*',
            'is_primary' => $primary, 'sort_order' => $sortOrder, 'alt_text' => null,
        ];
    }

    private function media(string $filename = 'product.png'): Media
    {
        return Media::query()->create([
            'disk' => 'public',
            'path' => 'media/originals/'.$filename,
            'original_filename' => $filename,
            'normalized_filename' => $filename,
            'extension' => 'png',
            'mime_type' => 'image/png',
            'size_bytes' => 128,
            'checksum_sha256' => hash('sha256', $filename),
            'width' => 100,
            'height' => 100,
            'status' => 'ready',
            'visibility' => 'public',
            'is_locked' => false,
        ]);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now('UTC')]);

        return $user;
    }
}
