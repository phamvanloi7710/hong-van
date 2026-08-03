<?php

namespace Tests\Feature\Showcase;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\Showcase\ShowcaseDataSource;
use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ShowcaseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_routes_require_permission_and_reject_unknown_filters(): void
    {
        $this->actingAs(User::factory()->create());
        $this->getJson('/api/admin/v1/showcase/galleries')->assertForbidden();
        $this->actingAs($this->superAdmin());
        $this->getJson('/api/admin/v1/showcase/galleries?filter[unsafe]=1')->assertUnprocessable();
        $this->getJson('/api/admin/v1/showcase/galleries?sort=unsafe')->assertUnprocessable();
    }

    public function test_admin_manages_all_showcase_types_without_seeded_business_data(): void
    {
        $this->actingAs($this->superAdmin());
        $galleryId = $this->postJson('/api/admin/v1/showcase/galleries', $this->payload('galleries', 'GAL-001'))->assertCreated()->assertJsonCount(3, 'data.translations')->json('data.public_id');
        $duplicate = $this->payload('galleries', 'GAL-002');
        $duplicate['translations'] = $this->payload('galleries', 'GAL-001')['translations'];
        $this->postJson('/api/admin/v1/showcase/galleries', $duplicate)->assertUnprocessable()->assertJsonValidationErrors('translations.0.slug');
        $this->postJson('/api/admin/v1/showcase/partners', $this->payload('partners', 'PARTNER-001'))->assertCreated();
        $this->postJson('/api/admin/v1/showcase/certifications', $this->payload('certifications', 'CERT-001'))->assertCreated();
        $projectId = $this->postJson('/api/admin/v1/showcase/projects', $this->payload('projects', 'PROJECT-001'))->assertCreated()->json('data.public_id');
        $media = $this->media('gallery.jpg', 'image/jpeg');
        $itemPayload = $this->payload('gallery-items', '');
        $itemPayload['gallery_id'] = $galleryId;
        $itemPayload['media_id'] = $media->public_id;
        $itemId = $this->postJson('/api/admin/v1/showcase/gallery-items', $itemPayload)->assertCreated()->assertJsonPath('data.media.public_id', $media->public_id)->json('data.public_id');

        $this->postJson('/api/admin/v1/showcase/projects/'.$projectId.'/publish')->assertOk()->assertJsonPath('data.status', 'published');
        $this->postJson('/api/admin/v1/showcase/projects/'.$projectId.'/archive')->assertOk()->assertJsonPath('data.status', 'archived');
        $this->deleteJson('/api/admin/v1/showcase/gallery-items/'.$itemId)->assertOk();
        $this->postJson('/api/admin/v1/showcase/gallery-items/'.$itemId.'/restore')->assertOk();
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'projects.published', 'subject_public_id' => $projectId]);
        $this->assertDatabaseCount('hongvan_partners', 1);
        $this->assertDatabaseCount('hongvan_certifications', 1);
    }

    public function test_media_usage_blocks_deletion_and_private_certificate_document_is_not_public(): void
    {
        $this->actingAs($this->superAdmin());
        $logo = $this->media('logo.png', 'image/png');
        $document = $this->media('certificate.pdf', 'application/pdf');
        $partner = $this->payload('partners', 'PARTNER-MEDIA');
        $partner['logo_media_id'] = $logo->public_id;
        $this->postJson('/api/admin/v1/showcase/partners', $partner)->assertCreated();
        $this->postJson('/api/admin/v1/media/'.$logo->public_id.'/trash')->assertConflict();

        $certificate = $this->payload('certifications', 'CERT-PRIVATE');
        $certificate['status'] = 'published';
        $certificate['document_media_id'] = $document->public_id;
        $certificate['document_visibility'] = 'private';
        $certificateId = $this->postJson('/api/admin/v1/showcase/certifications', $certificate)->assertCreated()->json('data.public_id');
        $published = app(ShowcaseDataSource::class)->published('en');
        $this->assertNull($published['certifications'][0]['document_media_public_id']);
        $certificate['document_visibility'] = 'public';
        $this->putJson('/api/admin/v1/showcase/certifications/'.$certificateId, $certificate)->assertOk();
        $this->assertSame($document->public_id, app(ShowcaseDataSource::class)->published('en')['certifications'][0]['document_media_public_id']);
    }

    public function test_public_data_source_returns_only_published_records_with_locale_fallback_and_eager_media(): void
    {
        $this->actingAs($this->superAdmin());
        $draft = $this->payload('projects', 'PROJECT-DRAFT');
        $this->postJson('/api/admin/v1/showcase/projects', $draft)->assertCreated();
        $published = $this->payload('projects', 'PROJECT-PUBLISHED');
        $published['status'] = 'published';
        $published['translations'][1]['title'] = 'Published case study';
        $this->postJson('/api/admin/v1/showcase/projects', $published)->assertCreated();
        $source = app(ShowcaseDataSource::class)->published('fr');
        $this->assertCount(1, $source['projects']);
        $this->assertSame('Tiêu đề PROJECT-PUBLISHED', $source['projects'][0]['title']);
        $this->assertSame([], $source['galleries']);
        $this->assertSame([], $source['partners']);
    }

    /** @return array<string, mixed> */
    private function payload(string $kind, string $code): array
    {
        $base = ['code' => $code, 'status' => 'draft', 'is_featured' => true, 'sort_order' => 1];
        $translations = [];
        foreach (['vi', 'en', 'zh'] as $locale) {
            $slug = strtolower($locale.'-'.preg_replace('/[^a-z0-9]+/i', '-', $code ?: 'item'));
            $translations[] = match ($kind) {
                'gallery-items' => ['locale' => $locale, 'title' => 'Media '.$locale, 'caption' => 'Caption '.$locale, 'alt_text' => 'Alternative '.$locale],
                'partners' => ['locale' => $locale, 'name' => 'Partner '.$locale, 'description' => null, 'logo_alt' => 'Logo '.$locale],
                'certifications' => ['locale' => $locale, 'name' => 'Certificate '.$locale, 'slug' => $slug, 'issuer' => 'Issuer', 'description' => null, 'image_alt' => null, 'document_label' => 'PDF'],
                'projects' => ['locale' => $locale, 'title' => $locale === 'vi' ? 'Tiêu đề '.$code : 'Title '.$code, 'slug' => $slug, 'summary' => 'Summary', 'content' => 'Content', 'location' => null, 'meta_title' => null, 'meta_description' => null],
                default => ['locale' => $locale, 'name' => 'Gallery '.$locale, 'slug' => $slug, 'description' => null, 'meta_title' => null, 'meta_description' => null],
            };
        }
        $base['translations'] = $translations;
        if ($kind === 'certifications') {
            $base = [...$base, 'document_visibility' => 'private', 'issued_on' => null, 'expires_on' => null];
        }
        if ($kind === 'projects') {
            $base = [...$base, 'started_on' => null, 'completed_on' => null, 'media_items' => []];
        }

        return $base;
    }

    private function media(string $name, string $mime): Media
    {
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        return Media::query()->create(['disk' => 'public', 'path' => 'tests/'.Str::uuid().'.'.$extension, 'original_filename' => $name, 'normalized_filename' => $name, 'extension' => $extension, 'mime_type' => $mime, 'size_bytes' => 100, 'checksum_sha256' => hash('sha256', Str::uuid()->toString()), 'status' => 'ready', 'visibility' => 'public']);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now('UTC')]);

        return $user;
    }
}
