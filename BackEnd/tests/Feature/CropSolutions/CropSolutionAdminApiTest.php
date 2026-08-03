<?php

namespace Tests\Feature\CropSolutions;

use App\Domain\CropSolutions\CropSolutionDataSource;
use App\Domain\Identity\PermissionRegistry;
use App\Models\Media;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class CropSolutionAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_crop_solution_admin_api_requires_permissions_and_allowlisted_filters(): void
    {
        $this->actingAs(User::factory()->create());

        $this->getJson('/api/admin/v1/crop-solutions')->assertForbidden();
        $this->getJson('/api/admin/v1/crop-solutions/categories')->assertForbidden();

        $this->actingAs($this->superAdmin());
        $this->getJson('/api/admin/v1/crop-solutions?filter[unsafe]=1')->assertUnprocessable();
        $this->getJson('/api/admin/v1/crop-solutions?sort=unsafe')->assertUnprocessable();
    }

    public function test_admin_can_manage_ordered_crop_timeline_solution_media_products_and_publish(): void
    {
        $this->actingAs($this->superAdmin())->withHeader('X-Locale', 'vi');
        $media = $this->media();
        $categoryId = $this->postJson('/api/admin/v1/crop-solutions/categories', $this->categoryPayload($media->public_id))
            ->assertCreated()->assertJsonCount(3, 'data.translations')->json('data.public_id');
        $cropId = $this->postJson('/api/admin/v1/crop-solutions/crops', $this->cropPayload($categoryId, $media->public_id))
            ->assertCreated()->json('data.public_id');
        $stageLaterId = $this->postJson('/api/admin/v1/crop-solutions/stages', $this->stagePayload($cropId, 'later', 20))
            ->assertCreated()->json('data.public_id');
        $stageFirstId = $this->postJson('/api/admin/v1/crop-solutions/stages', $this->stagePayload($cropId, 'first', 10))
            ->assertCreated()->json('data.public_id');
        $product = $this->publishedProduct();

        $solutionId = $this->postJson('/api/admin/v1/crop-solutions', $this->solutionPayload($cropId, $stageFirstId, $media->public_id, $product->public_id))
            ->assertCreated()
            ->assertJsonCount(3, 'data.translations')
            ->assertJsonPath('data.products.0.public_id', $product->public_id)
            ->json('data.public_id');

        $this->getJson('/api/admin/v1/crop-solutions/stages')->assertOk()
            ->assertJsonPath('data.0.public_id', $stageFirstId)
            ->assertJsonPath('data.1.public_id', $stageLaterId);
        Model::preventLazyLoading();
        try {
            $this->getJson('/api/admin/v1/crop-solutions?filter[crop_id]='.$cropId.'&filter[status]=draft')
                ->assertOk()->assertJsonPath('meta.pagination.total', 1)->assertJsonPath('data.0.public_id', $solutionId);
        } finally {
            Model::preventLazyLoading(false);
        }
        $this->postJson('/api/admin/v1/crop-solutions/'.$solutionId.'/publish')
            ->assertOk()->assertJsonPath('data.status', 'published');
        $this->assertDatabaseHas('hongvan_media_usages', [
            'media_id' => $media->getKey(), 'owner_type' => 'crop_solution', 'owner_public_id' => $solutionId, 'field' => 'hero',
        ]);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'crop_solution.published', 'subject_public_id' => $solutionId]);
    }

    public function test_data_source_uses_locale_order_and_excludes_archived_or_deleted_products_without_n_plus_one(): void
    {
        $this->actingAs($this->superAdmin());
        $categoryId = $this->postJson('/api/admin/v1/crop-solutions/categories', $this->categoryPayload())->json('data.public_id');
        $cropId = $this->postJson('/api/admin/v1/crop-solutions/crops', $this->cropPayload($categoryId))->json('data.public_id');
        $stageId = $this->postJson('/api/admin/v1/crop-solutions/stages', $this->stagePayload($cropId, 'main', 1))->json('data.public_id');
        $product = $this->publishedProduct();
        $solutionId = $this->postJson('/api/admin/v1/crop-solutions', $this->solutionPayload($cropId, $stageId, null, $product->public_id))->json('data.public_id');
        $this->postJson('/api/admin/v1/crop-solutions/'.$solutionId.'/publish')->assertOk();

        Cache::flush();
        Model::preventLazyLoading();
        try {
            $items = app(CropSolutionDataSource::class)->resolve('en');
        } finally {
            Model::preventLazyLoading(false);
        }
        $this->assertSame('Crop solution', $items[0]['title']);
        $this->assertSame($product->public_id, $items[0]['products'][0]['public_id']);

        $product->forceFill(['status' => 'archived'])->save();
        Cache::flush();
        $this->assertSame([], app(CropSolutionDataSource::class)->resolve('en')[0]['products']);
        $product->delete();
        Cache::flush();
        $this->assertSame([], app(CropSolutionDataSource::class)->resolve('en')[0]['products']);
    }

    /** @return array<string, mixed> */
    private function categoryPayload(?string $imageId = null): array
    {
        return [
            'parent_id' => null, 'code' => 'CROP-CAT', 'image_media_id' => $imageId, 'is_active' => true, 'sort_order' => 10,
            'translations' => $this->translations('Nhóm cây', 'Crop group', '作物组', 'crop-group'),
        ];
    }

    /** @return array<string, mixed> */
    private function cropPayload(string $categoryId, ?string $imageId = null): array
    {
        return [
            'category_id' => $categoryId, 'code' => 'CROP-01', 'image_media_id' => $imageId, 'is_active' => true, 'sort_order' => 10,
            'translations' => [
                ['locale' => 'vi', 'name' => 'Cây thử nghiệm', 'slug' => 'cay-thu-nghiem', 'summary' => null, 'description' => null, 'meta_title' => null, 'meta_description' => null],
                ['locale' => 'en', 'name' => 'Test crop', 'slug' => 'test-crop', 'summary' => null, 'description' => null, 'meta_title' => null, 'meta_description' => null],
                ['locale' => 'zh', 'name' => '测试作物', 'slug' => 'test-crop-zh', 'summary' => null, 'description' => null, 'meta_title' => null, 'meta_description' => null],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function stagePayload(string $cropId, string $code, int $order): array
    {
        return [
            'crop_id' => $cropId, 'code' => $code, 'image_media_id' => null, 'is_active' => true, 'sort_order' => $order,
            'translations' => [
                ['locale' => 'vi', 'name' => 'Giai đoạn '.$code, 'summary' => null, 'content' => null],
                ['locale' => 'en', 'name' => 'Stage '.$code, 'summary' => null, 'content' => null],
                ['locale' => 'zh', 'name' => '阶段 '.$code, 'summary' => null, 'content' => null],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function solutionPayload(string $cropId, string $stageId, ?string $mediaId, string $productId): array
    {
        return [
            'crop_id' => $cropId, 'stage_id' => $stageId, 'code' => 'SOLUTION-01', 'status' => 'draft',
            'hero_media_id' => $mediaId, 'is_featured' => true, 'sort_order' => 10, 'published_at' => null, 'unpublished_at' => null,
            'translations' => [
                ['locale' => 'vi', 'title' => 'Giải pháp cây trồng', 'slug' => 'giai-phap-cay-trong', 'summary' => null, 'content' => null, 'content_sections' => [['title' => 'Lưu ý', 'body' => 'Tham khảo điều kiện thực tế trước khi áp dụng.']], 'meta_title' => null, 'meta_description' => null],
                ['locale' => 'en', 'title' => 'Crop solution', 'slug' => 'crop-solution', 'summary' => null, 'content' => null, 'content_sections' => [], 'meta_title' => null, 'meta_description' => null],
                ['locale' => 'zh', 'title' => '作物解决方案', 'slug' => 'crop-solution-zh', 'summary' => null, 'content' => null, 'content_sections' => [], 'meta_title' => null, 'meta_description' => null],
            ],
            'products' => [['product_id' => $productId, 'sort_order' => 0, 'recommendation_note' => null]],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function translations(string $vi, string $en, string $zh, string $slug): array
    {
        return [
            ['locale' => 'vi', 'name' => $vi, 'slug' => $slug, 'summary' => null, 'meta_title' => null, 'meta_description' => null],
            ['locale' => 'en', 'name' => $en, 'slug' => $slug.'-en', 'summary' => null, 'meta_title' => null, 'meta_description' => null],
            ['locale' => 'zh', 'name' => $zh, 'slug' => $slug.'-zh', 'summary' => null, 'meta_title' => null, 'meta_description' => null],
        ];
    }

    private function publishedProduct(): Product
    {
        $product = Product::factory()->published()->create();
        $product->translations()->createMany([
            ['locale' => 'vi', 'name' => 'Sản phẩm thử nghiệm', 'slug' => 'san-pham-thu-nghiem'],
            ['locale' => 'en', 'name' => 'Test product', 'slug' => 'test-product'],
            ['locale' => 'zh', 'name' => '测试产品', 'slug' => 'test-product-zh'],
        ]);

        return $product;
    }

    private function media(): Media
    {
        return Media::query()->create([
            'disk' => 'public', 'path' => 'media/originals/crop.png', 'original_filename' => 'crop.png',
            'normalized_filename' => 'crop.png', 'extension' => 'png', 'mime_type' => 'image/png', 'size_bytes' => 128,
            'checksum_sha256' => hash('sha256', 'crop.png'), 'width' => 100, 'height' => 100,
            'status' => 'ready', 'visibility' => 'public', 'is_locked' => false,
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
