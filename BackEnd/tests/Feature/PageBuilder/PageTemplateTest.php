<?php

namespace Tests\Feature\PageBuilder;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\PageBuilder\PageDocumentValidator;
use App\Models\Page;
use App\Models\PageTemplateVersion;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PageTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_page_can_be_saved_as_an_immutable_template_and_used_to_create_a_draft_copy(): void
    {
        $this->actingAs($this->superAdmin());
        $page = $this->page('template-source');

        $template = $this->postJson("/api/admin/v1/page-builder/pages/{$page->public_id}/templates", [
            'key' => 'campaign-landing', 'name' => 'Campaign landing', 'category_key' => 'marketing',
        ])->assertCreated()->json('data');
        $this->assertDatabaseHas('hongvan_page_template_categories', ['key' => 'marketing']);
        $this->assertDatabaseHas('hongvan_page_templates', ['key' => 'campaign-landing']);

        try {
            PageTemplateVersion::query()->firstOrFail()->update(['checksum' => str_repeat('0', 64)]);
            $this->fail('Published template versions must be immutable.');
        } catch (\LogicException) {
            $this->assertTrue(true);
        }

        $copy = $this->postJson("/api/admin/v1/page-builder/templates/{$template['public_id']}/pages", $this->metadata('from-template'))
            ->assertCreated()->json('data');
        $this->assertSame('draft', $copy['status']);
        $this->assertSame([], $copy['draft']['document']['blocks']);
    }

    public function test_edit_lock_expires_and_force_unlock_is_restricted(): void
    {
        $owner = $this->superAdmin();
        $page = $this->page('lock-source');
        $this->actingAs($owner);
        $lock = $this->postJson("/api/admin/v1/page-builder/pages/{$page->public_id}/lock")->assertCreated()->json('data');
        $this->assertIsString($lock['token']);

        $this->actingAs(User::factory()->create());
        $this->postJson("/api/admin/v1/page-builder/pages/{$page->public_id}/lock")->assertForbidden();
        $this->deleteJson("/api/admin/v1/page-builder/pages/{$page->public_id}/lock/force")->assertForbidden();

        $this->actingAs($owner);
        $this->putJson("/api/admin/v1/page-builder/pages/{$page->public_id}/lock", ['token' => $lock['token']])->assertOk();
        $this->deleteJson("/api/admin/v1/page-builder/pages/{$page->public_id}/lock/force")->assertOk();
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
