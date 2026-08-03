<?php

namespace Tests\Feature\PublicFrontend;

use App\Domain\Settings\CompanySettingsService;
use App\Models\Setting;
use Database\Seeders\CompanySettingsSeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class PublicFrontendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, CompanySettingsSeeder::class]);
    }

    public function test_home_is_server_rendered_from_company_settings_without_javascript_dependency(): void
    {
        Setting::query()->where('key', 'company_name')->update(['value' => 'HỒNG VÂN TEST']);
        app(CompanySettingsService::class)->invalidate();

        $response = $this->get('/');

        $response->assertOk()
            ->assertHeader('Content-Language', 'vi')
            ->assertSee('<html lang="vi">', false)
            ->assertSee('<main id="main-content"', false)
            ->assertSee('<h1', false)
            ->assertSee('HỒNG VÂN TEST')
            ->assertSee('Nội dung đang được chuẩn bị')
            ->assertDontSee('ng-version', false)
            ->assertDontSee('id="app"', false);
    }

    public function test_localized_home_and_legal_pages_render_complete_language_catalogs(): void
    {
        $this->get('/en')->assertOk()->assertHeader('Content-Language', 'en')->assertSee('Content is being prepared');
        $this->get('/zh')->assertOk()->assertHeader('Content-Language', 'zh')->assertSee('内容正在准备中');
        $this->get('/privacy')->assertOk()->assertSee('Chính sách bảo mật');
        $this->get('/en/terms')->assertOk()->assertSee('Terms of use');
        $this->get('/zh/privacy')->assertOk()->assertSee('隐私政策');
    }

    public function test_default_locale_prefix_redirects_to_the_matching_canonical_page(): void
    {
        $this->get('/vi')->assertRedirect(route('public.home'));
        $this->get('/vi/privacy')->assertRedirect(route('public.privacy'));
        $this->get('/vi/terms')->assertRedirect(route('public.terms'));
    }

    public function test_minimal_error_pages_are_available(): void
    {
        $this->get('/missing-public-page')->assertNotFound()->assertSee('Không tìm thấy trang');

        $serverError = view('errors.500')->render();
        $this->assertStringContainsString('Hệ thống đang bận', $serverError);
        $this->assertStringContainsString('Về trang chủ', $serverError);
    }

    public function test_public_translation_catalogs_have_identical_keys(): void
    {
        $vietnameseKeys = array_keys(Arr::dot(require lang_path('vi/public.php')));
        sort($vietnameseKeys);

        foreach (['en', 'zh'] as $locale) {
            $localizedKeys = array_keys(Arr::dot(require lang_path("{$locale}/public.php")));
            sort($localizedKeys);
            $this->assertSame($vietnameseKeys, $localizedKeys, "Public translation keys differ for {$locale}.");
        }
    }

    public function test_blade_views_do_not_query_the_database_directly(): void
    {
        $viewFiles = collect(glob(resource_path('views/**/*.blade.php')) ?: [])
            ->merge(glob(resource_path('views/**/**/*.blade.php')) ?: [])
            ->unique();

        foreach ($viewFiles as $viewFile) {
            $contents = file_get_contents($viewFile);
            $this->assertIsString($contents);
            $this->assertDoesNotMatchRegularExpression('/(?:DB::|::query\s*\(|->where\s*\()/i', $contents, "Database query found in {$viewFile}.");
        }
    }
}
