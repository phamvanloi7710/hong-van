<?php

namespace Tests\Feature\Posts;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\Posts\PostDataSource;
use App\Domain\Posts\ScheduledPostPublisher;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostCmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_post_admin_routes_require_permission_and_allowlisted_filters(): void
    {
        $this->actingAs(User::factory()->create());
        $this->getJson('/api/admin/v1/posts')->assertForbidden();
        $this->getJson('/api/admin/v1/posts/categories')->assertForbidden();

        $this->actingAs($this->superAdmin());
        $this->getJson('/api/admin/v1/posts?filter[unsafe]=1')->assertUnprocessable();
        $this->getJson('/api/admin/v1/posts?sort=unsafe')->assertUnprocessable();
        $this->getJson('/api/admin/v1/posts/authors')->assertOk()->assertJsonStructure(['data' => [['public_id', 'name', 'email']]]);
    }

    public function test_admin_manages_localized_posts_and_backend_sanitizes_rich_text(): void
    {
        $this->actingAs($this->superAdmin());
        $categoryId = $this->postJson('/api/admin/v1/posts/categories', $this->categoryPayload())->assertCreated()->json('data.public_id');
        $tagId = $this->postJson('/api/admin/v1/posts/tags', $this->tagPayload())->assertCreated()->json('data.public_id');
        $payload = $this->postPayload($categoryId, [$tagId]);
        $payload['translations'][0]['content_html'] = '<p onclick="alert(1)">An toàn</p><script>alert(1)</script><a href="javascript:alert(1)">x</a>';
        $postId = $this->postJson('/api/admin/v1/posts', $payload)
            ->assertCreated()->assertJsonCount(3, 'data.translations')->assertJsonPath('data.tags.0.public_id', $tagId)->json('data.public_id');

        $html = (string) Post::query()->where('public_id', $postId)->firstOrFail()->translations()->where('locale', 'vi')->value('content_html');
        $this->assertStringContainsString('<p>An toàn</p>', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'post.created', 'subject_public_id' => $postId]);
    }

    public function test_scheduled_publication_is_idempotent_and_drafts_are_not_public(): void
    {
        $this->actingAs($this->superAdmin());
        $draftId = $this->postJson('/api/admin/v1/posts', $this->postPayload())->assertCreated()->json('data.public_id');
        $scheduled = $this->postPayload();
        $scheduled['code'] = 'POST-SCHEDULED';
        $scheduled['status'] = 'scheduled';
        $scheduled['scheduled_for'] = now('UTC')->subMinute()->toISOString();
        foreach ($scheduled['translations'] as $index => &$translation) {
            $translation['slug'] .= '-scheduled-'.$index;
        }
        unset($translation);
        $scheduledId = $this->postJson('/api/admin/v1/posts', $scheduled)->assertCreated()->json('data.public_id');

        $source = app(PostDataSource::class);
        $this->assertSame(0, $source->listing('vi')->total());
        $publisher = app(ScheduledPostPublisher::class);
        $this->assertSame(1, $publisher->publishDue());
        $this->assertSame(0, $publisher->publishDue());
        $this->assertSame(1, $source->listing('vi')->total());
        $this->assertSame($scheduledId, $source->listing('vi')->items()[0]->public_id);
        $this->assertNull($source->resolveSlug('vi', 'bai-viet-mau'));
        $this->assertDatabaseHas('hongvan_posts', ['public_id' => $draftId, 'status' => 'draft']);
    }

    public function test_locale_fallback_slug_history_redirect_and_eager_loading_contract(): void
    {
        $this->actingAs($this->superAdmin());
        $postId = $this->postJson('/api/admin/v1/posts', $this->postPayload())->assertCreated()->json('data.public_id');
        $this->postJson('/api/admin/v1/posts/'.$postId.'/publish')->assertOk();
        $post = Post::query()->where('public_id', $postId)->firstOrFail();
        $payload = $this->postPayload();
        $payload['status'] = 'published';
        $payload['published_at'] = now('UTC')->subMinute()->toISOString();
        $payload['translations'][0]['slug'] = 'bai-viet-moi';
        $this->putJson('/api/admin/v1/posts/'.$postId, $payload)->assertOk();

        $source = app(PostDataSource::class);
        $resolved = $source->resolveSlug('vi', 'bai-viet-mau');
        $this->assertSame('bai-viet-moi', $resolved['redirect_slug'] ?? null);
        $listed = $source->listing('vi')->items()[0];
        $this->assertTrue($listed->relationLoaded('translations'));
        $this->assertTrue($listed->relationLoaded('category'));
        $this->assertSame('Bài viết mẫu', $source->translation($listed, 'fr')?->title);
        $this->assertSame([], $source->related($post->fresh(), 4));
    }

    /** @return array<string, mixed> */
    private function categoryPayload(): array
    {
        return ['parent_id' => null, 'code' => 'NEWS', 'is_active' => true, 'sort_order' => 1, 'translations' => [
            ['locale' => 'vi', 'name' => 'Tin tức', 'slug' => 'tin-tuc', 'description' => null, 'meta_title' => null, 'meta_description' => null],
            ['locale' => 'en', 'name' => 'News', 'slug' => 'news', 'description' => null, 'meta_title' => null, 'meta_description' => null],
            ['locale' => 'zh', 'name' => '新闻', 'slug' => 'news-zh', 'description' => null, 'meta_title' => null, 'meta_description' => null],
        ]];
    }

    /** @return array<string, mixed> */
    private function tagPayload(): array
    {
        return ['code' => 'FERTILIZER', 'is_active' => true, 'sort_order' => 1, 'translations' => [
            ['locale' => 'vi', 'name' => 'Phân bón', 'slug' => 'phan-bon'],
            ['locale' => 'en', 'name' => 'Fertilizer', 'slug' => 'fertilizer'],
            ['locale' => 'zh', 'name' => '肥料', 'slug' => 'fertilizer-zh'],
        ]];
    }

    /** @param list<string> $tagIds @return array<string, mixed> */
    private function postPayload(?string $categoryId = null, array $tagIds = []): array
    {
        return [
            'category_id' => $categoryId, 'author_id' => null, 'featured_media_id' => null, 'tag_ids' => $tagIds,
            'code' => 'POST-001', 'status' => 'draft', 'is_featured' => true, 'scheduled_for' => null, 'published_at' => null, 'unpublished_at' => null,
            'translations' => [
                ['locale' => 'vi', 'title' => 'Bài viết mẫu', 'slug' => 'bai-viet-mau', 'excerpt' => 'Tóm tắt', 'content_html' => '<h2>Nội dung</h2><p>Chi tiết.</p>', 'meta_title' => null, 'meta_description' => null],
                ['locale' => 'en', 'title' => 'Sample post', 'slug' => 'sample-post', 'excerpt' => 'Summary', 'content_html' => '<h2>Content</h2><p>Details.</p>', 'meta_title' => null, 'meta_description' => null],
                ['locale' => 'zh', 'title' => '示例文章', 'slug' => 'sample-post-zh', 'excerpt' => '摘要', 'content_html' => '<h2>内容</h2><p>详情。</p>', 'meta_title' => null, 'meta_description' => null],
            ],
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
