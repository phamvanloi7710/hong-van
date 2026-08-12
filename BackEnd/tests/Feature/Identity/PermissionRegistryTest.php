<?php

namespace Tests\Feature\Identity;

use App\Domain\Identity\PermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

final class PermissionRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_registry_exactly_matches_protected_routes_and_has_complete_labels(): void
    {
        $definitions = PermissionRegistry::definitions();
        $registryKeys = PermissionRegistry::keys();

        $this->assertCount(84, $definitions);
        $this->assertCount(count($registryKeys), array_unique($registryKeys));

        foreach ($definitions as $definition) {
            $this->assertTrue(PermissionRegistry::isValidKey($definition['key']), $definition['key']);
            $this->assertSame(['vi', 'en', 'zh'], array_keys($definition['labels']));
            $this->assertSame($definition['labels']['vi'], $definition['name']);
            foreach ($definition['labels'] as $locale => $label) {
                $this->assertNotSame('', trim($label), $definition['key'].':'.$locale);
            }
        }

        $routeKeys = [];
        $publicRoutes = [
            'admin.api.v1.auth.login',
            'admin.api.v1.auth.forgot-password',
            'admin.api.v1.auth.reset-password',
        ];
        $seenPublicRoutes = [];
        $permissionOptionalRoutes = [
            'admin.api.v1.auth.me',
            'admin.api.v1.auth.logout',
            'admin.api.v1.preferences.show',
            'admin.api.v1.preferences.update',
            'admin.api.v1.preferences.destroy',
        ];

        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/admin/v1/')) {
                continue;
            }

            $middleware = $route->gatherMiddleware();
            $routeName = $route->getName();
            $this->assertIsString($routeName, 'Every admin API route must be named.');
            foreach ($middleware as $entry) {
                if (preg_match('/^permission:(?<key>[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*)$/', $entry, $matches) === 1) {
                    $routeKeys[] = $matches['key'];
                }
            }

            if (in_array($routeName, $publicRoutes, true)) {
                $seenPublicRoutes[] = $routeName;
                $this->assertNotContains('auth:sanctum', $middleware, 'Public auth route unexpectedly requires a session: '.$routeName);

                continue;
            }

            $this->assertContains('auth:sanctum', $middleware, 'Admin route lacks authentication: '.$routeName);

            if (! in_array($routeName, $permissionOptionalRoutes, true)) {
                $hasAuthorization = count(array_filter(
                    $middleware,
                    static fn (string $entry): bool => str_starts_with($entry, 'permission:')
                        || $entry === 'can:system_health',
                )) > 0;

                $this->assertTrue($hasAuthorization, 'Authenticated route lacks authorization middleware: '.$routeName);
            }
        }

        $this->assertEqualsCanonicalizing($publicRoutes, $seenPublicRoutes, 'Public admin auth route allowlist changed.');
        $routeKeys = array_values(array_unique($routeKeys));
        $this->assertSame([], array_values(array_diff($routeKeys, $registryKeys)), 'Route permission missing from registry.');
        $this->assertSame(
            $this->sorted($registryKeys),
            $this->sorted([...$routeKeys, 'leads.view_all', 'system.health']),
            'Registry contains an orphan permission.',
        );
    }

    public function test_admin_route_menu_and_identity_permissions_exist_in_backend_registry(): void
    {
        $adminRoot = dirname(base_path()).DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'app';
        $this->assertDirectoryExists($adminRoot);

        $uiKeys = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($adminRoot));
        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile() || ! in_array($file->getExtension(), ['ts', 'html'], true)) {
                continue;
            }

            $source = file_get_contents($file->getPathname());
            $this->assertIsString($source);
            foreach ([
                "/permissionGuard\('(?<key>[^']+)'\)/",
                "/hasPermission\('(?<key>[^']+)'\)/",
                "/hvHasPermission=\"'(?<key>[^']+)'\"/",
                "/\bpermission:\s*'(?<key>[^']+)'/",
            ] as $pattern) {
                preg_match_all($pattern, $source, $matches);
                $uiKeys = [...$uiKeys, ...($matches['key'] ?? [])];
            }
        }

        $uiKeys = array_values(array_unique($uiKeys));
        $this->assertNotEmpty($uiKeys);
        $this->assertSame(
            [],
            array_values(array_diff($uiKeys, PermissionRegistry::keys())),
            'Admin UI references an unknown permission.',
        );
    }

    /** @param list<string> $values
     * @return list<string>
     */
    private function sorted(array $values): array
    {
        $values = array_values(array_unique($values));
        sort($values);

        return $values;
    }
}
