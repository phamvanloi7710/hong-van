<?php

namespace Tests\Feature\Api;

use App\Domain\Identity\PermissionRegistry;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class QueryAllowlistHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->actingAs($this->superAdmin());
    }

    public function test_query_keys_sort_injection_and_page_size_are_rejected_with_the_api_422_contract(): void
    {
        $this->getJson('/api/admin/v1/products?filter[status]=published&filter[operator]=or%201%3D1')
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('filter');

        $this->getJson('/api/admin/v1/products?sort=sku%20desc')
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('sort');

        $this->getJson('/api/admin/v1/products?per_page=101')
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('per_page');
    }

    public function test_bulk_updates_are_limited_to_one_hundred_product_ids(): void
    {
        $productIds = array_map(
            static fn (): string => (string) Str::ulid(),
            range(1, 101),
        );

        $this->postJson('/api/admin/v1/products/bulk', [
            'action' => 'archive',
            'product_ids' => $productIds,
        ])->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('product_ids');
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now('UTC')]);

        return $user;
    }
}
