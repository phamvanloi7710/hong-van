<?php

namespace Tests\Feature\Themes;

use App\Domain\Identity\PermissionRegistry;
use App\Models\Role;
use App\Models\Theme;
use App\Models\User;
use Database\Seeders\CompanySettingsSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class PublicThemeStudioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PermissionSeeder::class, CompanySettingsSeeder::class, ThemeSeeder::class]);
    }

    public function test_theme_api_rejects_unknown_or_injectable_tokens(): void
    {
        $this->actingAs($this->superAdmin());
        $theme = Theme::query()->firstOrFail();
        $tokens = $theme->versions()->where('status', 'draft')->firstOrFail()->tokens;
        $tokens['custom_css'] = '</style><script>alert(1)</script>';

        $this->putJson('/api/admin/v1/themes/'.$theme->public_id.'/draft', ['tokens' => $tokens])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tokens.custom_css');

        $tokens = $theme->versions()->where('status', 'draft')->firstOrFail()->tokens;
        $tokens['colors']['brand'] = 'url(javascript:alert(1))';
        $this->putJson('/api/admin/v1/themes/'.$theme->public_id.'/draft', ['tokens' => $tokens])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tokens.colors.brand');
    }

    public function test_publish_preview_and_rollback_use_versioned_compiler_and_invalidate_cache(): void
    {
        $this->actingAs($this->superAdmin());
        $theme = Theme::query()->firstOrFail();
        $original = $theme->publishedVersion()->firstOrFail();
        $tokens = $theme->versions()->where('status', 'draft')->firstOrFail()->tokens;
        $tokens['colors']['brand'] = '#123456';

        $this->putJson('/api/admin/v1/themes/'.$theme->public_id.'/draft', ['tokens' => $tokens])
            ->assertOk()
            ->assertJsonPath('data.draft.tokens.colors.brand', '#123456');

        $previewUrl = $this->postJson('/api/admin/v1/themes/'.$theme->public_id.'/preview')
            ->assertOk()
            ->json('data.url');
        $this->get($previewUrl)
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('--public-brand:#123456', false);

        Cache::put((string) config('public_theme.cache_key'), ['version' => 'stale', 'css' => 'stale']);
        $this->postJson('/api/admin/v1/themes/'.$theme->public_id.'/publish')
            ->assertOk()
            ->assertJsonPath('data.published.tokens.colors.brand', '#123456');
        $this->assertFalse(Cache::has((string) config('public_theme.cache_key')));
        $this->get('/')->assertOk()->assertSee('--public-brand:#123456', false);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'theme.published', 'subject_type' => 'theme']);

        $this->postJson('/api/admin/v1/themes/'.$theme->public_id.'/rollback/'.$original->public_id)
            ->assertOk()
            ->assertJsonPath('data.published.public_id', $original->public_id);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'theme.rolled_back', 'subject_type' => 'theme']);
        $this->assertDatabaseHas('hongvan_theme_versions', ['theme_id' => $theme->getKey(), 'status' => 'draft', 'parent_version_id' => $original->getKey()]);
    }

    public function test_theme_permissions_are_enforced(): void
    {
        $theme = Theme::query()->firstOrFail();
        $this->actingAs(User::factory()->create());

        $this->getJson('/api/admin/v1/themes/'.$theme->public_id)->assertForbidden();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now('UTC')]);

        return $user;
    }
}
