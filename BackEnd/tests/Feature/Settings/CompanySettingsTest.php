<?php

namespace Tests\Feature\Settings;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\Settings\CompanySettingsService;
use App\Domain\Settings\CompanySettingsViewModel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SettingGroup;
use App\Models\User;
use Database\Seeders\CompanySettingsSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PermissionSeeder::class, CompanySettingsSeeder::class]);
    }

    public function test_defaults_only_contain_confirmed_company_information(): void
    {
        $this->assertDatabaseHas('hongvan_settings', ['key' => 'company_name', 'value' => 'CÔNG TY TNHH DV VT HỒNG VÂN']);
        $this->assertDatabaseHas('hongvan_settings', ['key' => 'timezone', 'value' => 'Asia/Ho_Chi_Minh']);
        $this->assertDatabaseHas('hongvan_settings', ['key' => 'tax_code', 'value' => null]);
        $this->assertDatabaseHas('hongvan_settings', ['key' => 'primary_phone', 'value' => null]);
        $this->assertDatabaseHas('hongvan_settings', ['key' => 'primary_address', 'value' => null]);
        $this->assertSame(11, SettingGroup::query()->count());
    }

    public function test_settings_api_requires_the_matching_permission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->getJson('/api/admin/v1/settings')->assertForbidden();

        $permission = Permission::query()->where('key', 'settings.view')->firstOrFail();
        $user->permissionOverrides()->attach($permission, ['is_allowed' => true]);
        $this->getJson('/api/admin/v1/settings')->assertOk()->assertJsonCount(11, 'data.groups');
    }

    public function test_settings_api_returns_json_unauthorized_without_an_accept_header(): void
    {
        $this->get('/api/admin/v1/settings')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_updating_a_group_invalidates_cached_admin_and_public_payloads(): void
    {
        $this->actingAs($this->superAdmin());
        $service = app(CompanySettingsService::class);
        $service->adminPayload();
        $service->publicPayload();
        $this->assertTrue(Cache::has(config('company_settings.cache.admin')));
        $this->assertTrue(Cache::has(config('company_settings.cache.public')));

        $this->putJson('/api/admin/v1/settings/groups/company', [
            'values' => ['short_name' => 'Hồng Vân Logistics'],
        ])->assertOk()->assertJsonPath('data.settings.1.value', 'Hồng Vân Logistics');

        $this->assertFalse(Cache::has(config('company_settings.cache.admin')));
        $this->assertFalse(Cache::has(config('company_settings.cache.public')));
        $this->assertSame('Hồng Vân Logistics', data_get($service->publicPayload(), 'settings.company.short_name'));
    }

    public function test_secret_is_encrypted_masked_and_never_written_to_the_settings_audit_context(): void
    {
        $this->actingAs($this->superAdmin());
        Log::spy();
        $secret = 'smtp-secret-value';

        $response = $this->putJson('/api/admin/v1/settings/groups/email', [
            'values' => ['smtp_password' => $secret],
        ])->assertOk();

        $stored = Setting::query()->where('key', 'smtp_password')->firstOrFail()->value;
        $this->assertIsString($stored);
        $this->assertStringStartsWith('enc:', $stored);
        $this->assertStringNotContainsString($secret, $stored);
        $this->assertSame($secret, app(CompanySettingsService::class)->secret('email', 'smtp_password'));
        $response->assertJsonPath('data.settings.2.value', null)->assertJsonPath('data.settings.2.has_value', true);
        $this->assertStringNotContainsString($secret, $response->getContent());

        Log::shouldHaveReceived('notice')->withArgs(
            static fn (string $message, array $context): bool => $message === 'Admin company settings event.'
                && $context['event'] === 'settings.group.updated'
                && ! str_contains(json_encode($context, JSON_THROW_ON_ERROR), $secret),
        )->once();
    }

    public function test_environment_secret_reference_is_preserved_but_masked(): void
    {
        $this->actingAs($this->superAdmin());

        $this->putJson('/api/admin/v1/settings/groups/email', [
            'values' => ['smtp_password' => 'env:MAIL_PASSWORD'],
        ])->assertOk()->assertJsonPath('data.settings.2.value', null);

        $this->assertDatabaseHas('hongvan_settings', ['key' => 'smtp_password', 'value' => 'env:MAIL_PASSWORD']);
    }

    public function test_directories_are_ordered_filtered_and_available_through_the_public_view_model(): void
    {
        $this->actingAs($this->superAdmin());

        $this->postJson('/api/admin/v1/settings/branches', $this->branchPayload('Hidden', 1, false))->assertCreated();
        $this->postJson('/api/admin/v1/settings/branches', $this->branchPayload('Visible', 2, true))->assertCreated();
        $this->postJson('/api/admin/v1/settings/social-links', [
            'platform' => 'facebook', 'label' => 'Facebook', 'url' => 'https://facebook.com/hongvan',
            'icon' => 'facebook', 'is_active' => true, 'sort_order' => 3,
        ])->assertCreated();
        $this->postJson('/api/admin/v1/settings/contact-channels', [
            'type' => 'email', 'label' => 'Email', 'value' => 'contact@example.test', 'href' => 'mailto:contact@example.test',
            'availability_note' => null, 'is_primary' => true, 'is_active' => true, 'sort_order' => 4,
        ])->assertCreated();

        $public = app(CompanySettingsViewModel::class)->toArray();
        $this->assertSame(['Visible'], array_column($public['branches'], 'name'));
        $this->assertSame('Facebook', $public['social_links'][0]['label']);
        $this->assertSame('Email', $public['contact_channels'][0]['label']);
        $this->assertArrayNotHasKey('smtp_password', $public['settings']['email']);
    }

    /** @return array<string, mixed> */
    private function branchPayload(string $name, int $sortOrder, bool $active): array
    {
        return [
            'name' => $name, 'code' => null, 'address' => null, 'province' => null, 'district' => null,
            'ward' => null, 'postal_code' => null, 'latitude' => null, 'longitude' => null,
            'phone' => null, 'email' => null, 'is_head_office' => false, 'is_active' => $active, 'sort_order' => $sortOrder,
        ];
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now()]);

        return $user;
    }
}
