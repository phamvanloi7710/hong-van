<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class AdminSpaTest extends TestCase
{
    private string $spaDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->spaDirectory = storage_path('framework/testing/admin-spa-'.bin2hex(random_bytes(8)));
        File::ensureDirectoryExists($this->spaDirectory);
        File::put(
            $this->spaDirectory.'/index.html',
            '<!doctype html><html><head><title>Admin fixture</title><base href="/admin/"></head><body></body></html>',
        );
        File::put($this->spaDirectory.'/main-ABCDEF12.js', 'window.__ADMIN_FIXTURE__ = true;');
        File::put($this->spaDirectory.'/favicon.ico', 'fixture-icon');

        config()->set('admin.spa_path', $this->spaDirectory);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->spaDirectory);

        parent::tearDown();
    }

    public function test_admin_root_and_deep_links_return_the_spa_index_without_long_cache(): void
    {
        foreach (['/admin/', '/admin/index.html', '/admin/dashboard', '/admin/content/pages/example'] as $uri) {
            $response = $this->get($uri)
                ->assertOk()
                ->assertHeader('X-Content-Type-Options', 'nosniff');

            $this->assertBinaryFile($response, $this->spaDirectory.'/index.html');
            $this->assertCacheControlContains($response, [
                'max-age=0',
                'must-revalidate',
                'no-cache',
                'no-store',
                'private',
            ]);
        }
    }

    public function test_hashed_admin_assets_are_served_with_immutable_cache_headers(): void
    {
        $response = $this->get('/admin/main-ABCDEF12.js')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->assertBinaryFile($response, $this->spaDirectory.'/main-ABCDEF12.js');
        $this->assertCacheControlContains($response, ['immutable', 'max-age=31536000', 'public']);
    }

    public function test_non_hashed_admin_assets_use_a_short_cache(): void
    {
        $response = $this->get('/admin/favicon.ico')->assertOk();

        $this->assertCacheControlContains($response, ['max-age=3600', 'public']);
    }

    public function test_missing_asset_returns_404_instead_of_the_spa_index(): void
    {
        $this->get('/admin/main-MISSING1.js')
            ->assertNotFound()
            ->assertDontSee('Admin fixture');
    }

    public function test_admin_fallback_does_not_capture_public_api_or_preview_routes(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Admin fixture');

        $this->get('/api/admin/v1')
            ->assertNotFound()
            ->assertDontSee('Admin fixture');

        $this->get('/preview/example')
            ->assertNotFound()
            ->assertDontSee('Admin fixture');
    }

    /**
     * @param  list<string>  $directives
     */
    private function assertCacheControlContains(TestResponse $response, array $directives): void
    {
        $cacheControl = $response->headers->get('Cache-Control');

        $this->assertIsString($cacheControl);

        foreach ($directives as $directive) {
            $this->assertStringContainsString($directive, $cacheControl);
        }
    }

    private function assertBinaryFile(TestResponse $response, string $expectedPath): void
    {
        $binaryResponse = $response->baseResponse;

        $this->assertInstanceOf(BinaryFileResponse::class, $binaryResponse);
        $this->assertSame(realpath($expectedPath), $binaryResponse->getFile()->getRealPath());
    }
}
