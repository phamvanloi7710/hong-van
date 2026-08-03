<?php

namespace Tests\Feature\Warehouses;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\Warehouses\WarehouseDataSource;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class WarehouseApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_warehouse_admin_manages_localized_capabilities_without_wms_scope(): void
    {
        $this->actingAs(User::factory()->create());
        $this->getJson('/api/admin/v1/warehouses')->assertForbidden();

        $this->actingAs($this->superAdmin());
        $facilityId = $this->postJson('/api/admin/v1/warehouses/facilities', $this->referencePayload('SECURITY'))->assertCreated()->json('data.public_id');
        $serviceId = $this->postJson('/api/admin/v1/warehouses/services', $this->referencePayload('HANDLING'))->assertCreated()->json('data.public_id');
        $warehouseResponse = $this->postJson('/api/admin/v1/warehouses', $this->warehousePayload($facilityId, $serviceId))->assertCreated()->assertJsonMissingPath('data.translations.0.id');
        $warehouseId = $warehouseResponse->json('data.public_id');
        $this->deleteJson('/api/admin/v1/warehouses/facilities/'.$facilityId)->assertConflict();
        $this->postJson('/api/admin/v1/warehouses/'.$warehouseId.'/publish')->assertOk();

        $data = app(WarehouseDataSource::class)->resolve('en');
        $this->assertSame('Warehouse capability', $data[0]['name']);
        $this->assertSame(10.76, $data[0]['coordinates']['latitude']);
        $this->assertSame(['Security'], $data[0]['facilities']);
        $this->assertSame(['Handling'], $data[0]['services']);
        $this->assertDatabaseHas('hongvan_audit_logs', ['subject_public_id' => $warehouseId]);

        $columns = Schema::getColumnListing('hongvan_warehouses');
        foreach (['stock', 'quantity', 'bin_id', 'inventory', 'inbound', 'outbound', 'map_api_key'] as $forbidden) {
            $this->assertNotContains($forbidden, $columns);
        }
    }

    public function test_public_warehouse_request_contract_is_validated_and_privacy_safe(): void
    {
        $this->postJson('/api/public/v1/warehouse-requests', [])->assertUnprocessable()->assertJsonValidationErrors(['goods_description', 'contact_name', 'contact_phone']);
        $this->postJson('/api/public/v1/warehouse-requests', [
            'goods_description' => 'Packaged fertilizer', 'required_area' => 150, 'area_unit' => 'm2',
            'required_volume' => 300, 'volume_unit' => 'm3', 'duration_description' => 'Six months',
            'start_date' => now()->addDay()->toDateString(), 'storage_requirements' => 'Dry storage',
            'preferred_location' => 'Near the city', 'contact_name' => 'Requester', 'contact_phone' => '0900000000',
            'contact_email' => 'requester@example.test',
            'consent' => true, 'privacy_policy_version' => config('leads.privacy_policy_version'),
        ])->assertCreated()->assertJsonPath('data.status', 'new')->assertJsonMissingPath('data.ip_hash');
        $this->assertDatabaseCount('hongvan_warehouse_requests', 1);
        $this->assertDatabaseCount('hongvan_warehouse_request_status_histories', 1);
        $this->assertDatabaseMissing('hongvan_warehouse_requests', ['ip_hash' => '127.0.0.1']);
    }

    /** @return array<string,mixed> */
    private function referencePayload(string $code): array
    {
        return ['code' => $code, 'icon' => 'verified_user', 'is_active' => true, 'sort_order' => 1, 'translations' => [['locale' => 'vi', 'name' => $code === 'SECURITY' ? 'An ninh' : 'Xử lý hàng', 'description' => null], ['locale' => 'en', 'name' => $code === 'SECURITY' ? 'Security' : 'Handling', 'description' => null], ['locale' => 'zh', 'name' => $code === 'SECURITY' ? '安保' : '货物处理', 'description' => null]]];
    }

    /** @return array<string,mixed> */
    private function warehousePayload(string $facilityId, string $serviceId): array
    {
        return ['code' => 'WAREHOUSE-CAP', 'area_value' => 1500, 'area_unit' => 'm2', 'latitude' => 10.75678, 'longitude' => 106.67891, 'map_display' => 'approximate', 'business_hours' => [['day' => 'mon', 'opens' => '08:00', 'closes' => '17:00', 'closed' => false]], 'status' => 'draft', 'is_featured' => true, 'sort_order' => 1, 'published_at' => null, 'unpublished_at' => null, 'facility_ids' => [$facilityId], 'service_ids' => [$serviceId], 'translations' => $this->translations(), 'media' => []];
    }

    /** @return list<array<string,mixed>> */
    private function translations(): array
    {
        return collect(['vi' => ['Năng lực kho', 'nang-luc-kho'], 'en' => ['Warehouse capability', 'warehouse-capability'], 'zh' => ['仓储能力', 'warehouse-capability-zh']])->map(fn (array $value, string $locale): array => ['locale' => $locale, 'name' => $value[0], 'slug' => $value[1], 'summary' => null, 'description' => null, 'address_display' => 'Public location', 'area_description' => null, 'capacity_description' => null, 'security_description' => null, 'fire_safety_description' => null, 'business_hours_description' => null, 'meta_title' => null, 'meta_description' => null])->values()->all();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now('UTC')]);

        return $user;
    }
}
