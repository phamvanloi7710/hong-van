<?php

namespace Tests\Feature\Seo;

use App\Domain\Identity\PermissionRegistry;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs($this->superAdmin());
    }

    public function test_redirect_manager_enforces_exact_paths_and_gone_responses(): void
    {
        $this->postJson('/api/admin/v1/seo-tools/redirects', $this->payload('/old-page', '/new-page', 301))
            ->assertCreated()->assertJsonPath('data.source_path', '/old-page');

        $this->get('/old-page?campaign=summer')->assertRedirect(url('/new-page'))->assertStatus(301);
        $this->get('/old-page/child')->assertNotFound();

        $this->postJson('/api/admin/v1/seo-tools/redirects', $this->payload('/removed-page', null, 410))
            ->assertCreated();
        $this->get('/removed-page')->assertStatus(410)->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertDatabaseHas('hongvan_redirects', ['source_path' => '/old-page', 'hit_count' => 1]);
    }

    public function test_redirect_manager_blocks_reserved_colliding_and_looping_rules(): void
    {
        $this->postJson('/api/admin/v1/seo-tools/redirects', $this->payload('/admin/users', '/safe', 302))
            ->assertUnprocessable()->assertJsonValidationErrors('source_path');

        $this->postJson('/api/admin/v1/seo-tools/redirects', $this->payload('/a', '/b', 301))->assertCreated();
        $this->postJson('/api/admin/v1/seo-tools/redirects', $this->payload('/a', '/c', 302))
            ->assertUnprocessable()->assertJsonValidationErrors('source_path');
        $this->postJson('/api/admin/v1/seo-tools/redirects', $this->payload('/b', '/a', 301))
            ->assertUnprocessable()->assertJsonValidationErrors('target_path');
    }

    /** @return array<string, mixed> */
    private function payload(string $source, ?string $target, int $status): array
    {
        return ['source_path' => $source, 'target_path' => $target, 'status_code' => $status, 'locale' => '*', 'is_active' => true, 'note' => null];
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail(), ['created_at' => now('UTC')]);

        return $user;
    }
}
