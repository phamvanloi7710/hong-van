<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use App\Models\UserPreference;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_and_supported_languages_are_available(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->getJson('/api/admin/v1/preferences')
            ->assertOk()
            ->assertJsonPath('data.locale', 'vi')
            ->assertJsonPath('data.theme.skin', 'indigo-light')
            ->assertJsonPath('data.favorite_menu_ids', []);

        $this->seed(LanguageSeeder::class);
        $this->seed(LanguageSeeder::class);

        $this->assertDatabaseCount('hongvan_languages', 3);
        $this->assertDatabaseHas('hongvan_languages', ['locale' => 'vi', 'is_default' => true, 'is_active' => true]);
        $this->assertDatabaseHas('hongvan_languages', ['locale' => 'en', 'is_default' => false, 'is_active' => true]);
        $this->assertDatabaseHas('hongvan_languages', ['locale' => 'zh', 'is_default' => false, 'is_active' => true]);
    }

    public function test_two_users_keep_isolated_theme_locale_and_favorite_menus(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $payload = [
            'theme' => [
                'fixed_header' => true,
                'fixed_sidenav' => false,
                'fixed_footer' => true,
                'sidenav_opened' => false,
                'sidenav_pinned' => false,
                'menu_orientation' => 'horizontal',
                'menu_density' => 'compact',
                'skin' => 'green-dark',
                'rtl' => false,
            ],
            'locale' => 'zh',
            'favorite_menu_ids' => ['dashboard', 'identity'],
        ];

        $this->actingAs($userA)
            ->putJson('/api/admin/v1/preferences', $payload)
            ->assertOk()
            ->assertJsonPath('data.locale', 'zh')
            ->assertJsonPath('data.theme.skin', 'green-dark')
            ->assertJsonPath('data.favorite_menu_ids.0', 'dashboard');

        $this->actingAs($userB)
            ->getJson('/api/admin/v1/preferences')
            ->assertOk()
            ->assertJsonPath('data.locale', 'vi')
            ->assertJsonPath('data.theme.skin', 'indigo-light')
            ->assertJsonPath('data.favorite_menu_ids', []);

        $this->actingAs($userA)
            ->getJson('/api/admin/v1/preferences')
            ->assertOk()
            ->assertJsonPath('data.locale', 'zh')
            ->assertJsonPath('data.favorite_menu_ids.1', 'identity');

        $this->assertSame(3, UserPreference::query()->where('user_id', $userA->getKey())->count());
        $this->assertSame(0, UserPreference::query()->where('user_id', $userB->getKey())->count());
    }

    public function test_invalid_tokens_are_rejected_and_old_invalid_values_fall_back(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->putJson('/api/admin/v1/preferences', [
            'locale' => 'fr',
            'favorite_menu_ids' => ['unknown-menu'],
        ])->assertUnprocessable();

        UserPreference::query()->create([
            'user_id' => $user->getKey(),
            'namespace' => 'admin',
            'key' => 'theme',
            'value' => ['skin' => 'removed-skin', 'menu_density' => 'invalid'],
        ]);

        $this->getJson('/api/admin/v1/preferences')
            ->assertOk()
            ->assertJsonPath('data.theme.skin', 'indigo-light')
            ->assertJsonPath('data.theme.menu_density', 'default');
    }

    public function test_user_can_clear_all_favorite_menus(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->putJson('/api/admin/v1/preferences', [
            'favorite_menu_ids' => ['dashboard', 'identity'],
        ])->assertOk();

        $this->putJson('/api/admin/v1/preferences', [
            'favorite_menu_ids' => [],
        ])
            ->assertOk()
            ->assertJsonPath('data.favorite_menu_ids', []);

        $this->getJson('/api/admin/v1/preferences')
            ->assertOk()
            ->assertJsonPath('data.favorite_menu_ids', []);
    }

    public function test_all_current_navigable_menu_ids_are_allowlisted_and_ordered(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $favoriteMenuIds = [
            'products',
            'page-builder',
            'theme-studio',
            'localization',
            'audit',
        ];

        $this->putJson('/api/admin/v1/preferences', [
            'favorite_menu_ids' => $favoriteMenuIds,
        ])
            ->assertOk()
            ->assertJsonPath('data.favorite_menu_ids', $favoriteMenuIds);

        $this->getJson('/api/admin/v1/preferences')
            ->assertOk()
            ->assertJsonPath('data.favorite_menu_ids', $favoriteMenuIds);
    }

    public function test_user_can_reset_only_their_own_preferences(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        foreach ([$userA, $userB] as $user) {
            UserPreference::query()->create([
                'user_id' => $user->getKey(),
                'namespace' => 'admin',
                'key' => 'locale',
                'value' => 'en',
            ]);
        }

        UserPreference::query()->create([
            'user_id' => $userA->getKey(),
            'namespace' => 'public',
            'key' => 'locale',
            'value' => 'zh',
        ]);

        $this->actingAs($userA)
            ->deleteJson('/api/admin/v1/preferences')
            ->assertOk()
            ->assertJsonPath('data.locale', 'vi')
            ->assertJsonPath('data.theme.skin', 'indigo-light')
            ->assertJsonPath('data.favorite_menu_ids', []);

        $this->assertDatabaseMissing('hongvan_user_preferences', [
            'user_id' => $userA->getKey(),
            'namespace' => 'admin',
        ]);
        $this->assertDatabaseHas('hongvan_user_preferences', [
            'user_id' => $userA->getKey(),
            'namespace' => 'public',
            'key' => 'locale',
        ]);
        $this->assertDatabaseHas('hongvan_user_preferences', ['user_id' => $userB->getKey(), 'key' => 'locale']);
    }
}
