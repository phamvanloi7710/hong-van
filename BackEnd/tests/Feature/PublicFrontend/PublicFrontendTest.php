<?php

namespace Tests\Feature\PublicFrontend;

use App\Domain\PublicSite\PublicSiteViewData;
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
            ->assertSee('Kết nối sản phẩm nông nghiệp với dịch vụ logistics')
            ->assertSee('Danh mục chính')
            ->assertSee('Dịch vụ vận chuyển')
            ->assertSee('Dịch vụ kho bãi')
            ->assertDontSee('ng-version', false)
            ->assertDontSee('id="app"', false);
    }

    public function test_home_contains_no_ecommerce_or_wordpress_runtime_contracts(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertDontSee('woocommerce', false)
            ->assertDontSee('wp-content', false)
            ->assertDontSee('add-to-cart', false)
            ->assertDontSee('Giỏ hàng')
            ->assertDontSee('Thanh toán')
            ->assertDontSee('Wishlist')
            ->assertSee('Không bán hàng online');
    }

    public function test_localized_home_and_legal_pages_render_complete_language_catalogs(): void
    {
        $this->get('/en')->assertOk()->assertHeader('Content-Language', 'en')->assertSee('Connecting agricultural products');
        $this->get('/zh')->assertOk()->assertHeader('Content-Language', 'zh')->assertSee('连接农业产品与物流服务');
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

    public function test_public_page_template_variants_render_with_safe_empty_contracts(): void
    {
        $viewData = app(PublicSiteViewData::class)->forPage('home');

        $listing = view('pages.listing', array_merge($viewData, [
            'pageTitle' => 'Danh mục thử nghiệm',
            'items' => [],
        ]))->render();
        $detail = view('pages.detail', array_merge($viewData, [
            'pageTitle' => 'Chi tiết thử nghiệm',
            'contentHtml' => '<script>alert(1)</script>',
        ]))->render();
        $contact = view('pages.contact', array_merge($viewData, [
            'pageTitle' => 'Liên hệ',
        ]))->render();
        $content = view('pages.content', array_merge($viewData, [
            'pageTitle' => 'Nội dung thử nghiệm',
            'contentHtml' => '<strong>Nội dung</strong>',
        ]))->render();

        $this->assertStringContainsString('Chưa có nội dung', $listing);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $detail);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $detail);
        $this->assertStringContainsString('Thông tin liên hệ', $contact);
        $this->assertStringContainsString('&lt;strong&gt;Nội dung&lt;/strong&gt;', $content);
    }

    public function test_bootstrap_jquery_and_font_awesome_are_pinned_in_the_vite_application(): void
    {
        $package = json_decode((string) file_get_contents(base_path('package.json')), true, 512, JSON_THROW_ON_ERROR);
        $dependencies = $package['dependencies'] ?? [];

        $this->assertSame('5.3.8', ltrim((string) ($dependencies['bootstrap'] ?? ''), '^~'));
        $this->assertSame('4.0.0', ltrim((string) ($dependencies['jquery'] ?? ''), '^~'));
        $this->assertSame('7.3.1', ltrim((string) ($dependencies['@fortawesome/fontawesome-free'] ?? ''), '^~'));

        $css = (string) file_get_contents(resource_path('css/public/app.css'));
        $javascript = (string) file_get_contents(resource_path('js/public/app.js'));
        $this->assertStringContainsString('bootstrap/dist/css/bootstrap.min.css', $css);
        $this->assertStringContainsString('@fortawesome/fontawesome-free/css/fontawesome.min.css', $css);
        $this->assertStringContainsString('@fortawesome/fontawesome-free/css/solid.min.css', $css);
        $this->assertStringContainsString('@fortawesome/fontawesome-free/css/regular.min.css', $css);
        $this->assertStringContainsString("import $ from 'jquery'", $javascript);
        $this->assertStringContainsString("import * as bootstrap from 'bootstrap'", $javascript);
        $this->assertStringContainsString('window.bootstrap = bootstrap', $javascript);
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
