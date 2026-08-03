<?php

namespace Tests\Feature\Services;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\Services\ServiceDataSource;
use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class ServiceAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_service_admin_api_requires_permissions_and_allowlisted_filters(): void
    {
        $this->actingAs(User::factory()->create());
        $this->getJson('/api/admin/v1/services')->assertForbidden();
        $this->getJson('/api/admin/v1/services/categories')->assertForbidden();

        $this->actingAs($this->superAdmin());
        $this->getJson('/api/admin/v1/services?filter[unsafe]=1')->assertUnprocessable();
        $this->getJson('/api/admin/v1/services?sort=unsafe')->assertUnprocessable();
    }

    public function test_admin_can_manage_general_service_media_cta_publish_trash_restore_and_audit(): void
    {
        $this->actingAs($this->superAdmin())->withHeader('X-Locale', 'vi');
        $categoryId = $this->postJson('/api/admin/v1/services/categories', $this->categoryPayload())
            ->assertCreated()->assertJsonCount(3, 'data.translations')->json('data.public_id');
        $media = $this->media();
        $serviceId = $this->postJson('/api/admin/v1/services', $this->servicePayload($categoryId, $media->public_id))
            ->assertCreated()
            ->assertJsonPath('data.cta_type', 'quote')
            ->assertJsonPath('data.cta_source.source_type', 'service')
            ->assertJsonPath('data.media.0.public_id', $media->public_id)
            ->json('data.public_id');

        Model::preventLazyLoading();
        try {
            $this->getJson('/api/admin/v1/services?filter[category_id]='.$categoryId.'&filter[status]=draft')
                ->assertOk()->assertJsonPath('meta.pagination.total', 1)->assertJsonPath('data.0.public_id', $serviceId);
        } finally {
            Model::preventLazyLoading(false);
        }
        $this->postJson('/api/admin/v1/services/'.$serviceId.'/publish')
            ->assertOk()->assertJsonPath('data.status', 'published');
        $this->assertDatabaseHas('hongvan_media_usages', [
            'media_id' => $media->getKey(), 'owner_type' => 'service', 'owner_public_id' => $serviceId,
            'field' => 'media:'.$media->public_id,
        ]);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'service.published', 'subject_public_id' => $serviceId]);

        $this->deleteJson('/api/admin/v1/services/'.$serviceId)->assertOk();
        $this->assertSoftDeleted('hongvan_services', ['public_id' => $serviceId]);
        $this->assertDatabaseMissing('hongvan_media_usages', ['owner_type' => 'service', 'owner_public_id' => $serviceId]);
        $this->postJson('/api/admin/v1/services/'.$serviceId.'/restore')->assertOk()->assertJsonPath('data.public_id', $serviceId);
        $this->assertDatabaseHas('hongvan_media_usages', ['owner_type' => 'service', 'owner_public_id' => $serviceId]);
    }

    public function test_service_data_source_uses_locale_cta_and_cache_without_n_plus_one(): void
    {
        $this->actingAs($this->superAdmin());
        $categoryId = $this->postJson('/api/admin/v1/services/categories', $this->categoryPayload())->json('data.public_id');
        $serviceId = $this->postJson('/api/admin/v1/services', $this->servicePayload($categoryId))->json('data.public_id');
        $this->postJson('/api/admin/v1/services/'.$serviceId.'/publish')->assertOk();

        Cache::flush();
        Model::preventLazyLoading();
        try {
            $items = app(ServiceDataSource::class)->resolve('en', 8, $categoryId, true);
        } finally {
            Model::preventLazyLoading(false);
        }
        $this->assertSame('General service', $items[0]['name']);
        $this->assertSame('quote', $items[0]['cta']['type']);
        $this->assertSame($serviceId, $items[0]['cta']['source_public_id']);
        $this->assertSame($items, app(ServiceDataSource::class)->resolve('en', 8, $categoryId, true));
    }

    public function test_specialized_links_cannot_duplicate_content_media_or_general_cta(): void
    {
        $this->actingAs($this->superAdmin());
        $media = $this->media();
        $invalid = $this->servicePayload(null, $media->public_id);
        $invalid['service_type'] = 'transportation_link';
        $this->postJson('/api/admin/v1/services', $invalid)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cta_type', 'media', 'translations.0.content']);

        $valid = $this->servicePayload();
        $valid['code'] = 'SERVICE-TRANSPORT-LINK';
        $valid['service_type'] = 'transportation_link';
        $valid['cta_type'] = 'none';
        $valid['media'] = [];
        foreach ($valid['translations'] as &$translation) {
            $translation['content'] = null;
            $translation['content_sections'] = [];
        }
        unset($translation);
        $serviceId = $this->postJson('/api/admin/v1/services', $valid)
            ->assertCreated()->assertJsonPath('data.specialized_module', 'transportation')->json('data.public_id');
        $this->postJson('/api/admin/v1/services/'.$serviceId.'/publish')->assertOk();

        Cache::flush();
        $this->assertSame('transportation', app(ServiceDataSource::class)->resolve('vi')[0]['specialized_module']);
    }

    /** @return array<string, mixed> */
    private function categoryPayload(): array
    {
        return [
            'parent_id' => null, 'code' => 'SERVICE-CAT', 'is_active' => true, 'sort_order' => 10,
            'translations' => $this->categoryTranslations(),
        ];
    }

    /** @return array<string, mixed> */
    private function servicePayload(?string $categoryId = null, ?string $mediaId = null): array
    {
        return [
            'category_id' => $categoryId, 'code' => 'SERVICE-01', 'service_type' => 'general', 'status' => 'draft',
            'cta_type' => 'quote', 'is_featured' => true, 'sort_order' => 10, 'published_at' => null, 'unpublished_at' => null,
            'translations' => [
                ['locale' => 'vi', 'name' => 'Dịch vụ chung', 'slug' => 'dich-vu-chung', 'summary' => 'Tóm tắt', 'content' => 'Nội dung', 'content_sections' => [['title' => 'Phạm vi', 'body' => 'Nội dung tham khảo.']], 'cta_label' => 'Yêu cầu báo giá', 'meta_title' => null, 'meta_description' => null],
                ['locale' => 'en', 'name' => 'General service', 'slug' => 'general-service', 'summary' => 'Summary', 'content' => 'Content', 'content_sections' => [], 'cta_label' => 'Request a quote', 'meta_title' => null, 'meta_description' => null],
                ['locale' => 'zh', 'name' => '通用服务', 'slug' => 'general-service-zh', 'summary' => '摘要', 'content' => '内容', 'content_sections' => [], 'cta_label' => '请求报价', 'meta_title' => null, 'meta_description' => null],
            ],
            'media' => $mediaId === null ? [] : [['media_id' => $mediaId, 'role' => 'hero', 'sort_order' => 0]],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function categoryTranslations(): array
    {
        return [
            ['locale' => 'vi', 'name' => 'Nhóm dịch vụ', 'slug' => 'nhom-dich-vu', 'summary' => null, 'meta_title' => null, 'meta_description' => null],
            ['locale' => 'en', 'name' => 'Service group', 'slug' => 'service-group', 'summary' => null, 'meta_title' => null, 'meta_description' => null],
            ['locale' => 'zh', 'name' => '服务组', 'slug' => 'service-group-zh', 'summary' => null, 'meta_title' => null, 'meta_description' => null],
        ];
    }

    private function media(): Media
    {
        return Media::query()->create([
            'disk' => 'public', 'path' => 'media/originals/service.png', 'original_filename' => 'service.png',
            'normalized_filename' => 'service.png', 'extension' => 'png', 'mime_type' => 'image/png', 'size_bytes' => 128,
            'checksum_sha256' => hash('sha256', 'service.png'), 'width' => 100, 'height' => 100,
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
