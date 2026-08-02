<?php

namespace Tests\Feature\Localization;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\Localization\Concerns\HasTranslations;
use App\Domain\Localization\Contracts\TranslatableEntity;
use App\Domain\Localization\DateTimePresenter;
use App\Domain\Localization\LocalizedSlugRegistry;
use App\Domain\Localization\MissingTranslationReport;
use App\Domain\Localization\TranslationResolver;
use App\Models\Language;
use App\Models\Role;
use App\Models\TranslationKey;
use App\Models\TranslationValue;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LocaleFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, PermissionSeeder::class]);
    }

    public function test_vietnamese_is_default_and_other_languages_fall_back_to_it(): void
    {
        $vietnamese = Language::query()->where('locale', 'vi')->firstOrFail();

        $this->assertTrue($vietnamese->is_default);
        $this->assertTrue($vietnamese->is_active);
        $this->assertNull($vietnamese->fallback_language_id);
        $this->assertDatabaseHas('hongvan_languages', ['locale' => 'en', 'fallback_language_id' => $vietnamese->getKey()]);
        $this->assertDatabaseHas('hongvan_languages', ['locale' => 'zh', 'fallback_language_id' => $vietnamese->getKey()]);
    }

    public function test_localization_api_is_permission_protected_and_returns_iso_utc_time(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->getJson('/api/admin/v1/localization')->assertForbidden();

        $this->actingAs($this->superAdmin())
            ->getJson('/api/admin/v1/localization')
            ->assertOk()
            ->assertJsonCount(3, 'data.languages')
            ->assertJsonPath('data.storage_timezone', 'UTC')
            ->assertJsonPath('data.display_timezone', 'Asia/Ho_Chi_Minh')
            ->assertJsonPath('data.languages.0.locale', 'vi')
            ->assertJsonPath('data.languages.0.is_default', true)
            ->assertJson(fn ($json) => $json->whereType('data.generated_at', 'string')->etc());

        $generatedAt = (string) $this->getLocalizationPayload()['generated_at'];
        $this->assertStringEndsWith('Z', $generatedAt);
    }

    public function test_disabled_english_redirects_to_default_public_route_without_breaking_admin_locale_support(): void
    {
        $english = Language::query()->where('locale', 'en')->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->withHeader('X-Locale', 'en')
            ->putJson('/api/admin/v1/localization/languages/'.$english->public_id, ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('message', 'Language settings updated.')
            ->assertJsonPath('data.languages.1.is_active', false);

        $this->get('/en')->assertRedirect(route('public.home'));
        $this->get('/')->assertOk()->assertHeader('Content-Language', 'vi');
    }

    public function test_default_locale_prefix_redirects_to_the_canonical_unprefixed_route(): void
    {
        $this->get('/vi')->assertRedirect(route('public.home'));
        $this->get('/')->assertOk()->assertHeader('Content-Language', 'vi');
    }

    public function test_default_language_cannot_be_disabled_and_fallback_locale_is_validated(): void
    {
        $vietnamese = Language::query()->where('locale', 'vi')->firstOrFail();
        $english = Language::query()->where('locale', 'en')->firstOrFail();
        $this->actingAs($this->superAdmin());

        $this->putJson('/api/admin/v1/localization/languages/'.$vietnamese->public_id, ['is_active' => false])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['is_default']]);

        $this->putJson('/api/admin/v1/localization/languages/'.$english->public_id, ['fallback_locale' => 'fr'])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['fallback_locale']]);

        $this->putJson('/api/admin/v1/localization/languages/'.$english->public_id, ['fallback_locale' => 'en'])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['fallback_locale']]);

        $chinese = Language::query()->where('locale', 'zh')->firstOrFail();
        $this->putJson('/api/admin/v1/localization/languages/'.$chinese->public_id, ['fallback_locale' => 'en'])
            ->assertOk();

        $this->putJson('/api/admin/v1/localization/languages/'.$english->public_id, ['fallback_locale' => 'zh'])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['fallback_locale']]);
    }

    public function test_missing_translation_falls_back_without_writing_during_resolution(): void
    {
        $key = TranslationKey::query()->create([
            'namespace' => 'public',
            'key' => 'welcome',
            'description' => 'Tiêu đề chào mừng public.',
            'is_system' => true,
        ]);
        $vietnamese = Language::query()->where('locale', 'vi')->firstOrFail();
        TranslationValue::query()->create([
            'translation_key_id' => $key->getKey(),
            'language_id' => $vietnamese->getKey(),
            'value' => 'Chào mừng {name}',
            'is_reviewed' => true,
        ]);

        $before = TranslationValue::query()->count();
        $resolved = app(TranslationResolver::class)->translate('public', 'welcome', 'en', ['name' => 'Hồng Vân']);
        $missing = app(TranslationResolver::class)->translate('public', 'unknown', 'en');
        $report = app(MissingTranslationReport::class)->generate();

        $this->assertSame('Chào mừng Hồng Vân', $resolved);
        $this->assertSame('public.unknown', $missing);
        $this->assertSame($before, TranslationValue::query()->count());
        $this->assertSame(1, collect($report['languages'])->firstWhere('locale', 'en')['missing_count']);
    }

    public function test_slug_is_unique_only_inside_the_same_locale_and_namespace(): void
    {
        $registry = app(LocalizedSlugRegistry::class);
        $registry->reserve('pages', 'gioi-thieu', 'vi', 'page', '01PAGEA');
        $registry->reserve('pages', 'gioi-thieu', 'en', 'page', '01PAGEB');
        $registry->reserve('products', 'gioi-thieu', 'vi', 'product', '01PRODUCT');

        $this->expectException(ValidationException::class);
        $registry->reserve('pages', 'gioi-thieu', 'vi', 'page', '01PAGEC');
    }

    public function test_timezone_conversion_crosses_the_vietnam_day_boundary(): void
    {
        $presenter = app(DateTimePresenter::class);
        $display = $presenter->display('2026-01-01T17:30:00Z');

        $this->assertSame('Asia/Ho_Chi_Minh', $display->timezoneName);
        $this->assertSame('2026-01-02 00:30:00', $display->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-01T17:30:00.000000Z', $presenter->api('2026-01-01T17:30:00Z'));
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('+00:00', config('database.connections.mysql.timezone'));
    }

    public function test_translation_table_contract_is_available_for_future_content_entities(): void
    {
        $this->assertTrue(interface_exists(TranslatableEntity::class));
        $this->assertTrue(trait_exists(HasTranslations::class));
    }

    /** @return array<string, mixed> */
    private function getLocalizationPayload(): array
    {
        return $this->actingAs($this->superAdmin())
            ->getJson('/api/admin/v1/localization')
            ->json('data');
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role);

        return $user;
    }
}
