<?php

namespace Tests\Feature\PageBuilder;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\PageBuilder\PageDocumentRenderer;
use App\Domain\PageBuilder\PageDocumentSchema;
use App\Domain\PageBuilder\PageRenderOptions;
use App\Models\Page;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class PreviewSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('page_builder.preview.cache_store', 'array');
        config()->set('page_builder.preview.ttl_seconds', 60);
        $this->seed(PermissionSeeder::class);
    }

    public function test_preview_uses_the_public_renderer_and_redis_payload_without_creating_page_versions(): void
    {
        $actor = $this->superAdmin();
        $this->actingAs($actor);
        $pageId = $this->createPage();
        $versionCount = Page::query()->where('public_id', $pageId)->firstOrFail()->versions()->count();
        $document = $this->document('Nội dung xem trước');

        $created = $this->postJson("/api/admin/v1/page-builder/pages/{$pageId}/preview-sessions", ['document' => $document, 'locale' => 'vi'])
            ->assertCreated()
            ->assertJsonPath('data.revision', 1)
            ->assertJsonPath('data.message_schema_version', 1);
        $sessionId = (string) $created->json('data.public_id');
        $token = (string) $created->json('data.token');
        $url = (string) $created->json('data.url');

        $updatedDocument = $this->document('<em>Nội dung an toàn</em>');
        $this->withHeader('X-Preview-Token', $token)
            ->putJson("/api/admin/v1/page-builder/preview-sessions/{$sessionId}", ['document' => $updatedDocument])
            ->assertOk()
            ->assertJsonPath('data.revision', 2);

        $this->assertSame($versionCount, Page::query()->where('public_id', $pageId)->firstOrFail()->versions()->count());
        $this->assertDatabaseCount('hongvan_page_preview_sessions', 1);
        $expected = app(PageDocumentRenderer::class)->render($updatedDocument, new PageRenderOptions('vi', true, true));
        $response = $this->get($url)
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee($expected, false);
        $this->assertStringContainsString("frame-ancestors 'self'", (string) $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('&lt;em&gt;Nội dung an toàn&lt;/em&gt;', $response->getContent());
        $this->assertStringNotContainsString('<em>Nội dung an toàn</em>', $response->getContent());
    }

    public function test_preview_token_is_owner_scoped_and_close_invalidates_the_url(): void
    {
        $owner = $this->superAdmin();
        $other = $this->superAdmin();
        $this->actingAs($owner);
        $pageId = $this->createPage();
        $created = $this->postJson("/api/admin/v1/page-builder/pages/{$pageId}/preview-sessions", ['document' => $this->document('Owner'), 'locale' => 'en'])->assertCreated();
        $sessionId = (string) $created->json('data.public_id');
        $token = (string) $created->json('data.token');
        $url = (string) $created->json('data.url');

        $this->actingAs($other)->get($url)->assertNotFound();
        $this->withHeader('X-Preview-Token', $token)
            ->putJson("/api/admin/v1/page-builder/preview-sessions/{$sessionId}", ['document' => $this->document('Other')])
            ->assertNotFound();

        $this->actingAs($owner)
            ->withHeader('X-Preview-Token', str_repeat('x', 64))
            ->postJson("/api/admin/v1/page-builder/preview-sessions/{$sessionId}/refresh")
            ->assertNotFound();
        $this->withHeader('X-Preview-Token', $token)
            ->deleteJson("/api/admin/v1/page-builder/preview-sessions/{$sessionId}")
            ->assertOk();

        $this->assertDatabaseCount('hongvan_page_preview_sessions', 0);
        $this->get($url)->assertNotFound();
    }

    public function test_expired_session_cannot_be_refreshed_or_rendered(): void
    {
        Carbon::setTestNow('2026-08-03 08:00:00 UTC');
        $this->actingAs($this->superAdmin());
        $pageId = $this->createPage();
        $created = $this->postJson("/api/admin/v1/page-builder/pages/{$pageId}/preview-sessions", ['document' => $this->document('Expiry'), 'locale' => 'zh'])->assertCreated();
        $sessionId = (string) $created->json('data.public_id');
        $token = (string) $created->json('data.token');
        $url = (string) $created->json('data.url');
        Carbon::setTestNow('2026-08-03 08:01:01 UTC');

        $this->withHeader('X-Preview-Token', $token)
            ->postJson("/api/admin/v1/page-builder/preview-sessions/{$sessionId}/refresh")
            ->assertStatus(410);
        $this->get($url)->assertForbidden();
        Carbon::setTestNow();
    }

    public function test_invalid_xss_update_returns_field_path_and_keeps_the_last_valid_preview(): void
    {
        $this->actingAs($this->superAdmin());
        $pageId = $this->createPage();
        $created = $this->postJson("/api/admin/v1/page-builder/pages/{$pageId}/preview-sessions", ['document' => $this->document('Bản hợp lệ'), 'locale' => 'vi'])->assertCreated();
        $sessionId = (string) $created->json('data.public_id');
        $token = (string) $created->json('data.token');
        $url = (string) $created->json('data.url');
        $invalid = $this->document('<script>alert(1)</script>');

        $this->withHeader('X-Preview-Token', $token)
            ->putJson("/api/admin/v1/page-builder/preview-sessions/{$sessionId}", ['document' => $invalid])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('document.blocks.0.props.label');

        $content = $this->get($url)->assertOk()->getContent();
        $this->assertStringContainsString('Bản hợp lệ', $content);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $content);
    }

    private function createPage(): string
    {
        return (string) $this->postJson('/api/admin/v1/page-builder/pages', [
            'code' => 'preview-page', 'type' => 'standard', 'is_home' => false,
            'translations' => [
                ['locale' => 'vi', 'title' => 'Xem trước', 'navigation_label' => 'Xem trước', 'slug' => 'xem-truoc'],
                ['locale' => 'en', 'title' => 'Preview', 'navigation_label' => 'Preview', 'slug' => 'preview'],
                ['locale' => 'zh', 'title' => '预览', 'navigation_label' => '预览', 'slug' => 'preview-zh'],
            ],
        ])->assertCreated()->json('data.public_id');
    }

    /** @return array<string, mixed> */
    private function document(string $label): array
    {
        $document = PageDocumentSchema::emptyDocument();
        $document['blocks'][] = [
            'id' => 'block-preview-0001', 'type' => 'foundation.placeholder', 'version' => 1,
            'props' => ['label' => $label],
            'style' => ['desktop' => [], 'tablet' => [], 'mobile' => []],
            'visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
            'bindings' => [], 'children' => [],
        ];

        return $document;
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now('UTC')]);

        return $user;
    }
}
