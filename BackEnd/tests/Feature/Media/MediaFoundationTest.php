<?php

namespace Tests\Feature\Media;

use App\Domain\Audit\AuditTrail;
use App\Domain\Identity\PermissionRegistry;
use App\Domain\Media\ImageVariantGenerator;
use App\Domain\Media\MediaLibraryService;
use App\Domain\Media\MediaUploadService;
use App\Domain\Media\MediaUsageTracker;
use App\Jobs\Media\GenerateMediaVariants;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\MediaOperation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Throwable;

class MediaFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        Storage::fake('public');
    }

    public function test_valid_upload_uses_detected_metadata_server_path_queue_and_audit(): void
    {
        Queue::fake();
        $actor = $this->superAdmin();
        $this->actingAs($actor);

        $publicId = $this->post('/api/admin/v1/media', [
            'file' => UploadedFile::fake()->image('My Safe Image.png', 640, 480),
            'alt_text' => 'Safe image',
        ])->assertCreated()
            ->assertJsonPath('data.mime_type', 'image/png')
            ->assertJsonPath('data.width', 640)
            ->assertJsonPath('data.height', 480)
            ->assertJsonPath('data.status', 'processing')
            ->json('data.public_id');

        $media = Media::query()->where('public_id', $publicId)->firstOrFail();
        $this->assertStringStartsWith('media/originals/', $media->path);
        $this->assertStringNotContainsString('My Safe Image', $media->path);
        $this->assertSame('my-safe-image.png', $media->normalized_filename);
        $this->assertSame(64, strlen($media->checksum_sha256));
        Storage::disk('public')->assertExists($media->path);
        Queue::assertPushed(GenerateMediaVariants::class, fn (GenerateMediaVariants $job): bool => $job->mediaId === $media->getKey());
        $this->assertDatabaseHas('hongvan_media_operations', ['media_id' => $media->getKey(), 'status' => 'queued']);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'media.uploaded', 'subject_public_id' => $media->public_id]);
        $this->assertFalse($media->getConnection()->getSchemaBuilder()->hasColumn('hongvan_media', 'url'));
    }

    public function test_svg_executable_and_spoofed_image_uploads_are_rejected(): void
    {
        $this->actingAs($this->superAdmin());

        foreach ([
            UploadedFile::fake()->createWithContent('vector.svg', '<svg><script>alert(1)</script></svg>'),
            UploadedFile::fake()->createWithContent('shell.php', '<?php echo "unsafe";'),
            UploadedFile::fake()->createWithContent('spoofed.jpg', '<?php echo "unsafe";'),
        ] as $file) {
            $this->post('/api/admin/v1/media', ['file' => $file])->assertUnprocessable();
        }

        $this->assertDatabaseCount('hongvan_media', 0);
        Storage::disk('public')->assertDirectoryEmpty('media');
    }

    public function test_variant_job_generates_webp_and_avif_and_records_completion(): void
    {
        Queue::fake();
        $actor = $this->superAdmin();
        $request = Request::create('/api/admin/v1/media', 'POST');
        $media = app(MediaUploadService::class)->upload(UploadedFile::fake()->image('variant.png', 800, 600), [], $actor, $request);
        $operation = MediaOperation::query()->where('media_id', $media->getKey())->firstOrFail();
        $job = new GenerateMediaVariants($media->getKey(), $operation->getKey());

        $job->handle(app(ImageVariantGenerator::class), app(AuditTrail::class));

        $this->assertSame('ready', $media->fresh()->status);
        $this->assertSame('completed', $operation->fresh()->status);
        $variantCount = $media->variants()->where('status', 'ready')->count();
        $this->assertGreaterThanOrEqual(2, $variantCount);
        foreach ($media->variants as $variant) {
            Storage::disk($variant->disk)->assertExists($variant->path);
        }
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'media.variants.generated', 'subject_public_id' => $media->public_id]);

        $job->handle(app(ImageVariantGenerator::class), app(AuditTrail::class));

        $this->assertSame($variantCount, $media->variants()->where('status', 'ready')->count());
        $this->assertSame(1, $operation->fresh()->attempts);
        $this->assertDatabaseCount('hongvan_audit_logs', 2);
    }

    public function test_api_permissions_allowlisted_filters_and_authenticated_content_are_enforced(): void
    {
        Queue::fake();
        $actor = $this->superAdmin();
        $media = app(MediaUploadService::class)->upload(UploadedFile::fake()->image('view.png'), [], $actor, Request::create('/fixture', 'POST'));
        $viewer = User::factory()->create();
        $this->actingAs($viewer);

        $this->getJson('/api/admin/v1/media')->assertForbidden();
        $permission = Permission::query()->where('key', 'media.view')->firstOrFail();
        $viewer->permissionOverrides()->attach($permission, ['is_allowed' => true]);

        $this->getJson('/api/admin/v1/media?filter[mime_type]=image&sort=-created_at')
            ->assertOk()
            ->assertJsonPath('data.0.public_id', $media->public_id)
            ->assertJsonPath('meta.pagination.total', 1);
        $this->getJson('/api/admin/v1/media?filter[unsafe]=value')->assertUnprocessable();
        $this->post('/api/admin/v1/media', ['file' => UploadedFile::fake()->image('denied.png')])->assertForbidden();
        $this->get('/api/admin/v1/media/'.$media->public_id.'/content')->assertOk()->assertHeader('Content-Type', 'image/png');
    }

    public function test_usage_blocks_trash_then_restore_and_permanent_delete_follow_policy(): void
    {
        Queue::fake();
        $actor = $this->superAdmin();
        $this->actingAs($actor);
        $media = app(MediaUploadService::class)->upload(UploadedFile::fake()->image('usage.png'), [], $actor, Request::create('/fixture', 'POST'));
        $usage = app(MediaUsageTracker::class)->track($media, 'settings', 'company', 'logo_media_id');

        $this->postJson('/api/admin/v1/media/'.$media->public_id.'/trash')->assertConflict()->assertJsonPath('message', __('media.in_use'));
        $this->getJson('/api/admin/v1/media/'.$media->public_id)->assertOk()->assertJsonPath('data.usage_count', 1)->assertJsonPath('data.can_delete', false);

        $usage->delete();
        $this->postJson('/api/admin/v1/media/'.$media->public_id.'/trash')->assertOk()->assertJsonPath('data.status', 'trashed');
        $this->postJson('/api/admin/v1/media/'.$media->public_id.'/restore')->assertOk();
        $this->postJson('/api/admin/v1/media/'.$media->public_id.'/trash')->assertOk();
        $this->deleteJson('/api/admin/v1/media/'.$media->public_id)->assertOk();

        $this->assertDatabaseMissing('hongvan_media', ['public_id' => $media->public_id]);
        Storage::disk('public')->assertMissing($media->path);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'media.deleted', 'subject_public_id' => $media->public_id]);
    }

    public function test_failure_is_recorded_and_retry_is_queued(): void
    {
        Queue::fake();
        $actor = $this->superAdmin();
        $request = Request::create('/fixture', 'POST');
        $media = app(MediaUploadService::class)->upload(UploadedFile::fake()->image('retry.png'), [], $actor, $request);
        $operation = MediaOperation::query()->where('media_id', $media->getKey())->firstOrFail();
        config()->set('media.variant_formats', []);
        $job = new GenerateMediaVariants($media->getKey(), $operation->getKey());

        try {
            $job->handle(app(ImageVariantGenerator::class), app(AuditTrail::class));
            $this->fail('Variant generation should fail without an available configured encoder.');
        } catch (Throwable) {
            $this->assertSame('failed', $media->fresh()->status);
            $this->assertSame('failed', $operation->fresh()->status);
        }

        config()->set('media.variant_formats', ['webp']);
        app(MediaLibraryService::class)->retry($media->fresh(), $actor, $request);
        Queue::assertPushed(GenerateMediaVariants::class, 2);
        $this->assertSame('processing', $media->fresh()->status);
        $this->assertDatabaseHas('hongvan_media_operations', ['media_id' => $media->getKey(), 'status' => 'queued']);
    }

    public function test_clone_folder_lock_visibility_and_navigation_contracts_are_enforced(): void
    {
        Queue::fake();
        $actor = $this->superAdmin();
        $this->actingAs($actor);

        $folderId = $this->postJson('/api/admin/v1/media/folders', ['name' => 'Campaigns'])
            ->assertCreated()
            ->assertJsonPath('data.is_locked', false)
            ->json('data.public_id');
        $this->patchJson('/api/admin/v1/media/folders/'.$folderId, ['name' => 'Campaign 2026'])
            ->assertOk()
            ->assertJsonPath('data.slug', 'campaign-2026');
        $childFolderId = $this->postJson('/api/admin/v1/media/folders', [
            'name' => 'Assets',
            'parent_id' => $folderId,
        ])->assertCreated()->json('data.public_id');
        $this->patchJson('/api/admin/v1/media/folders/'.$folderId.'/lock', ['locked' => true])
            ->assertOk()
            ->assertJsonPath('data.is_locked', true);
        $this->postJson('/api/admin/v1/media/folders', [
            'name' => 'Blocked child',
            'parent_id' => $childFolderId,
        ])->assertConflict()->assertJsonPath('message', __('media.folder_locked'));
        $this->patchJson('/api/admin/v1/media/folders/'.$childFolderId, ['name' => 'Blocked rename'])
            ->assertConflict()
            ->assertJsonPath('message', __('media.folder_locked'));
        $this->post('/api/admin/v1/media', [
            'folder_id' => $childFolderId,
            'file' => UploadedFile::fake()->image('blocked.png'),
        ])->assertConflict()->assertJsonPath('message', __('media.folder_locked'));

        $this->patchJson('/api/admin/v1/media/folders/'.$folderId.'/lock', ['locked' => false])->assertOk();
        $mediaId = $this->post('/api/admin/v1/media', [
            'folder_id' => $childFolderId,
            'file' => UploadedFile::fake()->image('workflow.png'),
        ])->assertCreated()->json('data.public_id');

        $this->getJson('/api/admin/v1/media?filter[folder_id]='.$childFolderId)
            ->assertOk()
            ->assertJsonPath('meta.pagination.total', 1)
            ->assertJsonPath('data.0.public_id', $mediaId);
        $this->getJson('/api/admin/v1/media?filter[folder_id]=root')
            ->assertOk()
            ->assertJsonPath('meta.pagination.total', 0);
        $this->patchJson('/api/admin/v1/media/'.$mediaId.'/lock', ['locked' => true])
            ->assertOk()
            ->assertJsonPath('data.is_locked', true);
        $this->patchJson('/api/admin/v1/media/'.$mediaId.'/visibility', ['visibility' => 'private'])
            ->assertConflict()
            ->assertJsonPath('message', __('media.locked'));
        $this->patchJson('/api/admin/v1/media/'.$mediaId.'/lock', ['locked' => false])->assertOk();
        $this->patchJson('/api/admin/v1/media/'.$mediaId.'/visibility', ['visibility' => 'private'])
            ->assertOk()
            ->assertJsonPath('data.visibility', 'private');
        $this->patchJson('/api/admin/v1/media/'.$mediaId.'/move', ['folder_id' => null])
            ->assertOk()
            ->assertJsonPath('data.folder', null);

        $viewer = User::factory()->create();
        $viewer->permissionOverrides()->attach(Permission::query()->where('key', 'media.view')->firstOrFail(), ['is_allowed' => true]);
        $this->actingAs($viewer);
        $this->patchJson('/api/admin/v1/media/'.$mediaId.'/lock', ['locked' => true])->assertForbidden();
        $this->patchJson('/api/admin/v1/media/folders/'.$folderId, ['name' => 'Denied'])->assertForbidden();
        $this->assertFalse(MediaFolder::query()->where('public_id', $folderId)->firstOrFail()->is_locked);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now('UTC')]);

        return $user;
    }
}
