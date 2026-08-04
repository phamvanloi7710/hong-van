<?php

namespace Tests\Feature\PageBuilder;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\PageBuilder\PageDocumentValidator;
use App\Models\Page;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PageImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_export_round_trips_to_a_new_draft_page_without_urls_or_executable_payload(): void
    {
        $this->actingAs($this->superAdmin());
        $page = $this->page('export-source');
        $payload = $this->getJson("/api/admin/v1/page-builder/pages/{$page->public_id}/export")->assertOk()->json();
        $this->assertSame('hongvan.page-builder.export', $payload['manifest']['format']);
        $this->assertArrayNotHasKey('url', $payload['manifest']);

        $this->postJson('/api/admin/v1/page-builder/imports/validate', ['payload' => $payload])->assertOk()->assertJsonPath('data.valid', true);
        $this->postJson('/api/admin/v1/page-builder/imports', [...$this->metadata('import-copy'), 'payload' => $payload])
            ->assertCreated()->assertJsonPath('data.status', 'draft');
    }

    public function test_import_rejects_unknown_fields_and_executable_content(): void
    {
        $this->actingAs($this->superAdmin());
        $page = $this->page('malicious-source');
        $payload = $this->getJson("/api/admin/v1/page-builder/pages/{$page->public_id}/export")->json();
        $payload['document']['dangerous'] = '<script>alert(1)</script>';

        $this->postJson('/api/admin/v1/page-builder/imports/validate', ['payload' => $payload])
            ->assertUnprocessable()->assertJsonPath('success', false);
    }

    private function page(string $code): Page
    {
        $actor = User::factory()->create();
        $page = Page::query()->create(['code' => $code, 'type' => 'standard', 'status' => 'draft', 'is_home' => false, 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey()]);
        foreach (['vi', 'en', 'zh'] as $locale) {
            $page->translations()->create(['locale' => $locale, 'title' => "Title {$locale}", 'slug' => "{$code}-{$locale}"]);
        }
        $document = ['schemaVersion' => 1, 'themeVersionId' => null, 'pageSettings' => ['container' => 'default', 'background' => 'surface', 'hideHeader' => false, 'hideFooter' => false], 'blocks' => []];
        $version = $page->versions()->create(['version_number' => 1, 'status' => 'draft', 'schema_version' => 1, 'document_json' => $document, 'checksum' => app(PageDocumentValidator::class)->checksum($document), 'created_by' => $actor->getKey()]);
        $page->update(['draft_version_id' => $version->getKey()]);

        return $page->refresh()->load('draftVersion');
    }

    /** @return array<string, mixed> */
    private function metadata(string $code): array
    {
        return ['code' => $code, 'type' => 'standard', 'is_home' => false, 'translations' => array_map(fn (string $locale): array => ['locale' => $locale, 'title' => "Copy {$locale}", 'navigation_label' => null, 'slug' => "{$code}-{$locale}"], ['vi', 'en', 'zh'])];
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail(), ['created_at' => now('UTC')]);

        return $user;
    }
}
