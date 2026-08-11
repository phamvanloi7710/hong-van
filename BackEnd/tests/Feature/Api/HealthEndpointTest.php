<?php

namespace Tests\Feature\Api;

use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

final class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_health_surfaces_expose_only_application_status(): void
    {
        $this->withHeader('Accept', 'text/html')->get('/health')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson(['status' => 'up']);

        $requestId = (string) Str::ulid();
        $this->withHeader('X-Request-ID', $requestId)
            ->getJson('/api/public/v1/system/ping')
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'data' => ['status' => 'up'],
                'meta' => [
                    'request_id' => $requestId,
                    'pagination' => null,
                ],
                'message' => null,
            ]);
    }

    public function test_public_health_does_not_leak_diagnostic_failures(): void
    {
        $sensitiveMarker = 'mysql.internal:3306';
        Event::listen(
            DiagnosingHealth::class,
            static fn () => throw new RuntimeException($sensitiveMarker),
        );

        try {
            $this->withHeader('Accept', 'text/html')->get('/health')
                ->assertInternalServerError()
                ->assertHeader('Content-Type', 'application/json')
                ->assertExactJson(['status' => 'down'])
                ->assertDontSee($sensitiveMarker);
        } finally {
            Event::forget(DiagnosingHealth::class);
        }
    }

    public function test_admin_health_requires_authentication_and_system_health_permission(): void
    {
        $this->seed(PermissionSeeder::class);

        $this->getJson('/api/admin/v1/system/ping')->assertUnauthorized();

        $user = User::factory()->create();
        $this->actingAs($user)
            ->getJson('/api/admin/v1/system/ping')
            ->assertForbidden();

        $permission = Permission::query()->where('key', 'system.health')->firstOrFail();
        $user->permissionOverrides()->attach($permission, ['is_allowed' => true]);
        $requestId = (string) Str::ulid();

        $this->withHeader('X-Request-ID', $requestId)
            ->getJson('/api/admin/v1/system/ping')
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'data' => ['status' => 'up'],
                'meta' => [
                    'request_id' => $requestId,
                    'pagination' => null,
                ],
                'message' => null,
            ]);
    }
}
