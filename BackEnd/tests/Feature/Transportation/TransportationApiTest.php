<?php

namespace Tests\Feature\Transportation;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\Transportation\TransportationDataSource;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class TransportationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_transportation_admin_requires_permission_and_manages_published_capabilities(): void
    {
        $this->actingAs(User::factory()->create());
        $this->getJson('/api/admin/v1/transportation/vehicles')->assertForbidden();

        $this->actingAs($this->superAdmin());
        $typeId = $this->postJson('/api/admin/v1/transportation/types', $this->typePayload())->assertCreated()->json('data.public_id');
        $vehicleId = $this->postJson('/api/admin/v1/transportation/vehicles', $this->vehiclePayload($typeId))->assertCreated()->json('data.public_id');
        $this->deleteJson('/api/admin/v1/transportation/types/'.$typeId)->assertConflict();
        $routeId = $this->postJson('/api/admin/v1/transportation/routes', $this->routePayload())->assertCreated()->json('data.public_id');
        $areaId = $this->postJson('/api/admin/v1/transportation/areas', $this->areaPayload())->assertCreated()->json('data.public_id');
        $this->postJson('/api/admin/v1/transportation/vehicles/'.$vehicleId.'/publish')->assertOk();
        $this->postJson('/api/admin/v1/transportation/routes/'.$routeId.'/publish')->assertOk();
        $this->postJson('/api/admin/v1/transportation/areas/'.$areaId.'/publish')->assertOk();

        $data = app(TransportationDataSource::class)->resolve('en');
        $this->assertSame('Truck capability', $data['vehicles'][0]['name']);
        $this->assertSame('Route capability', $data['routes'][0]['name']);
        $this->assertSame('Service area', $data['areas'][0]['name']);
        $this->assertDatabaseHas('hongvan_audit_logs', ['subject_public_id' => $vehicleId]);
    }

    public function test_public_transport_request_contract_validates_and_stores_no_price_or_dispatch_data(): void
    {
        $this->postJson('/api/public/v1/transport-requests', [])->assertUnprocessable()->assertJsonValidationErrors(['pickup_location', 'delivery_location', 'cargo_description', 'contact_name', 'contact_phone']);
        $this->postJson('/api/public/v1/transport-requests', [
            'pickup_location' => 'Pickup', 'delivery_location' => 'Delivery', 'cargo_description' => 'Packaged cargo',
            'cargo_weight' => 1200, 'weight_unit' => 'kg', 'requested_date' => now()->addDay()->toDateString(),
            'contact_name' => 'Requester', 'contact_phone' => '0900000000', 'contact_email' => 'requester@example.test',
        ])->assertCreated()->assertJsonPath('data.status', 'new');
        $this->assertDatabaseCount('hongvan_transport_requests', 1);
        $this->assertDatabaseCount('hongvan_transport_request_status_histories', 1);
        $this->assertFalse(
            collect(
                Schema::getColumnListing('hongvan_transport_requests')
            )->contains(fn (string $column): bool => in_array($column, ['price', 'fare', 'gps', 'driver_id', 'dispatch_id'], true))
        );
    }

    /** @return array<string,mixed> */
    private function typePayload(): array
    {
        return ['code' => 'TRUCK', 'is_active' => true, 'sort_order' => 1, 'translations' => $this->translations('Loại xe', 'Vehicle type', '车辆类型', false)];
    }

    /** @return array<string,mixed> */
    private function vehiclePayload(string $typeId): array
    {
        return ['vehicle_type_id' => $typeId, 'code' => 'TRUCK-CAP', 'payload_capacity' => 5, 'payload_unit' => 'ton', 'availability_display' => 'contact', 'status' => 'draft', 'is_featured' => true, 'sort_order' => 1, 'published_at' => null, 'unpublished_at' => null, 'translations' => $this->translations('Năng lực xe', 'Truck capability', '车辆能力', true), 'media' => []];
    }

    /** @return array<string,mixed> */
    private function routePayload(): array
    {
        return ['code' => 'ROUTE-01', 'origin_code' => 'ORIGIN', 'destination_code' => 'DESTINATION', 'status' => 'draft', 'is_featured' => true, 'sort_order' => 1, 'published_at' => null, 'unpublished_at' => null, 'translations' => $this->simpleTranslations('Tuyến vận chuyển', 'Route capability', '运输路线', 'route-capability')];
    }

    /** @return array<string,mixed> */
    private function areaPayload(): array
    {
        return ['code' => 'AREA-01', 'status' => 'draft', 'is_featured' => true, 'sort_order' => 1, 'published_at' => null, 'unpublished_at' => null, 'translations' => $this->simpleTranslations('Khu vực phục vụ', 'Service area', '服务区域', 'service-area')];
    }

    /** @return list<array<string,mixed>> */
    private function translations(string $vi, string $en, string $zh, bool $vehicle): array
    {
        return $vehicle ? [['locale' => 'vi', 'name' => $vi, 'slug' => 'nang-luc-xe', 'summary' => null, 'description' => null, 'body_dimensions' => 'Mô tả kích thước', 'meta_title' => null, 'meta_description' => null], ['locale' => 'en', 'name' => $en, 'slug' => 'truck-capability', 'summary' => null, 'description' => null, 'body_dimensions' => 'Dimension description', 'meta_title' => null, 'meta_description' => null], ['locale' => 'zh', 'name' => $zh, 'slug' => 'truck-capability-zh', 'summary' => null, 'description' => null, 'body_dimensions' => '尺寸说明', 'meta_title' => null, 'meta_description' => null]] : [['locale' => 'vi', 'name' => $vi, 'description' => null], ['locale' => 'en', 'name' => $en, 'description' => null], ['locale' => 'zh', 'name' => $zh, 'description' => null]];
    }

    /** @return list<array<string,mixed>> */
    private function simpleTranslations(string $vi, string $en, string $zh, string $slug): array
    {
        return [['locale' => 'vi', 'name' => $vi, 'slug' => $slug, 'summary' => null], ['locale' => 'en', 'name' => $en, 'slug' => $slug.'-en', 'summary' => null], ['locale' => 'zh', 'name' => $zh, 'slug' => $slug.'-zh', 'summary' => null]];
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now('UTC')]);

        return $user;
    }
}
