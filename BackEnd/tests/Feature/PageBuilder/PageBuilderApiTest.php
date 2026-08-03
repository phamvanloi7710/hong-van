<?php

namespace Tests\Feature\PageBuilder;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\PageBuilder\PageDocumentSchema;
use App\Models\Page;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PageBuilderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_registry_metadata_is_typed_and_does_not_leak_internal_renderer_or_sanitizer(): void
    {
        $this->actingAs($this->superAdmin());

        $response = $this->getJson('/api/admin/v1/page-builder/registry')
            ->assertOk()
            ->assertJsonPath('data.document.schemaVersion', 1)
            ->assertJsonPath('data.blocks.0.type', 'foundation.placeholder')
            ->assertJsonPath('data.blocks.0.version', 1)
            ->assertJsonMissingPath('data.blocks.0.renderer')
            ->assertJsonMissingPath('data.blocks.0.sanitizer')
            ->assertJsonMissingPath('data.blocks.0.testFixture');

        $this->assertStringNotContainsString('App\\', $response->getContent());
        $this->assertStringNotContainsString('.blade.php', $response->getContent());
    }

    public function test_page_metadata_and_draft_shell_create_and_update_validated_document(): void
    {
        $this->actingAs($this->superAdmin());
        $payload = $this->metadataPayload();

        $pageId = $this->postJson('/api/admin/v1/page-builder/pages', $payload)
            ->assertCreated()
            ->assertJsonPath('data.code', 'about-company')
            ->assertJsonPath('data.draft.document.schemaVersion', 1)
            ->json('data.public_id');

        $document = PageDocumentSchema::emptyDocument();
        $document['blocks'][] = $this->placeholder('block-home-0001', 'Giới thiệu doanh nghiệp');
        $this->putJson("/api/admin/v1/page-builder/pages/{$pageId}/draft", ['document' => $document])
            ->assertOk()
            ->assertJsonPath('data.draft.document.blocks.0.props.label', 'Giới thiệu doanh nghiệp');

        $this->assertDatabaseHas('hongvan_pages', ['public_id' => $pageId, 'code' => 'about-company']);
        $this->assertDatabaseHas('hongvan_page_translations', ['locale' => 'vi', 'slug' => 'gioi-thieu']);
        $this->assertDatabaseHas('hongvan_page_versions', ['page_id' => Page::query()->where('public_id', $pageId)->value('id'), 'status' => 'draft', 'schema_version' => 1]);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'page.created', 'subject_type' => 'page']);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'page.draft.updated', 'subject_type' => 'page']);
    }

    public function test_page_builder_api_rejects_unknown_block_arbitrary_view_and_script_payload_with_paths(): void
    {
        $this->actingAs($this->superAdmin());
        $pageId = $this->postJson('/api/admin/v1/page-builder/pages', $this->metadataPayload())->assertCreated()->json('data.public_id');

        $unknown = PageDocumentSchema::emptyDocument();
        $unknown['blocks'][] = array_replace($this->placeholder('block-unknown-01', 'Unknown'), ['type' => 'evil.shell']);
        $this->putJson("/api/admin/v1/page-builder/pages/{$pageId}/draft", ['document' => $unknown])
            ->assertUnprocessable()->assertJsonValidationErrors('document.blocks.0.type');

        $view = PageDocumentSchema::emptyDocument();
        $view['blocks'][] = $this->placeholder('block-view-00001', 'View') + ['view' => '../../secrets'];
        $this->putJson("/api/admin/v1/page-builder/pages/{$pageId}/draft", ['document' => $view])
            ->assertUnprocessable()->assertJsonValidationErrors('document.blocks.0.view');

        $script = PageDocumentSchema::emptyDocument();
        $script['blocks'][] = $this->placeholder('block-script-001', '<script>alert(1)</script>');
        $this->putJson("/api/admin/v1/page-builder/pages/{$pageId}/draft", ['document' => $script])
            ->assertUnprocessable()->assertJsonValidationErrors('document.blocks.0.props.label');
    }

    public function test_page_builder_document_preserves_valid_empty_string_defaults(): void
    {
        $this->actingAs($this->superAdmin());
        $pageId = $this->postJson('/api/admin/v1/page-builder/pages', $this->metadataPayload())
            ->assertCreated()
            ->json('data.public_id');

        $document = PageDocumentSchema::emptyDocument();
        $document['blocks'][] = $this->placeholder('block-empty-label-01', '');

        $this->putJson("/api/admin/v1/page-builder/pages/{$pageId}/draft", ['document' => $document])
            ->assertOk()
            ->assertJsonPath('data.draft.document.blocks.0.props.label', '');
    }

    public function test_page_builder_permissions_are_enforced(): void
    {
        $this->actingAs(User::factory()->create());

        $this->getJson('/api/admin/v1/page-builder/registry')->assertForbidden();
        $this->postJson('/api/admin/v1/page-builder/pages', $this->metadataPayload())->assertForbidden();
    }

    /** @return array<string, mixed> */
    private function metadataPayload(): array
    {
        return [
            'code' => 'about-company', 'type' => 'standard', 'is_home' => false,
            'translations' => [
                ['locale' => 'vi', 'title' => 'Giới thiệu', 'navigation_label' => 'Giới thiệu', 'slug' => 'gioi-thieu'],
                ['locale' => 'en', 'title' => 'About us', 'navigation_label' => 'About', 'slug' => 'about-us'],
                ['locale' => 'zh', 'title' => '关于我们', 'navigation_label' => '关于', 'slug' => 'about-company'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function placeholder(string $id, string $label): array
    {
        return [
            'id' => $id, 'type' => 'foundation.placeholder', 'version' => 1,
            'props' => ['label' => $label],
            'style' => ['desktop' => [], 'tablet' => [], 'mobile' => []],
            'visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
            'bindings' => [], 'children' => [],
        ];
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now('UTC')]);

        return $user;
    }
}
