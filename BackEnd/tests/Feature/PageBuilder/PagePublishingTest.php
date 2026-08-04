<?php

namespace Tests\Feature\PageBuilder;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\PageBuilder\PageDocumentValidator;
use App\Domain\PageBuilder\PagePublishingManager;
use App\Models\Page;
use App\Models\PagePublishSchedule;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PagePublishingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_publish_creates_immutable_version_and_rejects_stale_checksum(): void
    {
        $this->actingAs($this->superAdmin());
        $page = $this->createPage();
        $checksum = $page->draftVersion->checksum;
        $versionId = $page->draftVersion->public_id;

        $this->postJson("/api/admin/v1/page-builder/pages/{$page->public_id}/publish", ['expected_checksum' => $checksum, 'expected_version_id' => $versionId, 'note' => 'First release'])
            ->assertOk()->assertJsonPath('data.status', 'published');
        $this->postJson("/api/admin/v1/page-builder/pages/{$page->public_id}/publish", ['expected_checksum' => $checksum, 'expected_version_id' => $versionId])
            ->assertConflict();

        $published = $page->refresh()->publishedVersion;
        $this->assertSame('published', $published->status);
        $this->assertSame('First release', $published->note);
        $this->assertNotSame($published->getKey(), $page->draft_version_id);
        $this->expectException(\LogicException::class);
        $published->update(['checksum' => str_repeat('0', 64)]);
    }

    public function test_due_schedule_is_idempotent_and_rollback_clones_source(): void
    {
        $actor = $this->superAdmin();
        $page = $this->createPage();
        $manager = app(PagePublishingManager::class);
        $schedule = $manager->schedule($actor, $page, $page->draftVersion->checksum, $page->draftVersion->public_id, now()->subMinute()->toImmutable(), 'Scheduled');

        $this->assertTrue($manager->processSchedule($schedule));
        $this->assertFalse($manager->processSchedule($schedule));
        $source = $page->refresh()->publishedVersion;
        $rolledBack = $manager->rollback($actor, $page, $source, 'Rollback check');

        $this->assertNotSame($source->getKey(), $rolledBack->getKey());
        $this->assertSame('published', $rolledBack->status);
        $this->assertSame($source->checksum, $rolledBack->checksum);
        $this->assertSame(1, PagePublishSchedule::query()->where('status', 'completed')->count());
    }

    public function test_publish_permission_is_required(): void
    {
        $page = $this->createPage();
        $this->actingAs(User::factory()->create());
        $this->postJson("/api/admin/v1/page-builder/pages/{$page->public_id}/publish", ['expected_checksum' => $page->draftVersion->checksum, 'expected_version_id' => $page->draftVersion->public_id])->assertForbidden();
    }

    private function createPage(): Page
    {
        $actor = User::factory()->create();
        $page = Page::query()->create(['code' => 'publishing-test-'.str()->random(5), 'type' => 'standard', 'status' => 'draft', 'is_home' => false, 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey()]);
        foreach (['vi', 'en', 'zh'] as $locale) {
            $page->translations()->create(['locale' => $locale, 'title' => 'Title '.$locale, 'slug' => 'title-'.$locale.'-'.str()->random(5)]);
        }
        $document = ['schemaVersion' => 1, 'themeVersionId' => null, 'pageSettings' => ['container' => 'default', 'background' => 'surface', 'hideHeader' => false, 'hideFooter' => false], 'blocks' => []];
        $version = $page->versions()->create(['version_number' => 1, 'status' => 'draft', 'schema_version' => 1, 'document_json' => $document, 'checksum' => app(PageDocumentValidator::class)->checksum($document), 'created_by' => $actor->getKey()]);
        $page->update(['draft_version_id' => $version->getKey()]);

        return $page->refresh()->load('draftVersion');
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now('UTC')]);

        return $user;
    }
}
