<?php

namespace Tests\Feature\Database;

use App\Domain\Products\ProductPriceMode;
use App\Models\Media;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoMediaSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_safe_seed_is_idempotent_and_super_admin_comes_only_from_config(): void
    {
        config()->set('identity.super_admin.email');
        config()->set('identity.super_admin.password');

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('hongvan_languages', 3);
        $this->assertDatabaseCount('hongvan_roles', 1);
        $this->assertDatabaseMissing('hongvan_products', ['sku' => 'DEMO-PROD-CONTACT']);
        $this->assertDatabaseCount('hongvan_users', 0);

        config()->set('identity.super_admin.email', 'seed-admin@example.test');
        config()->set('identity.super_admin.name', 'Seed Super Admin');
        config()->set('identity.super_admin.password', 'Seeder-password-123');

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->where('email', 'seed-admin@example.test')->firstOrFail();
        $this->assertTrue($user->roles()->where('slug', 'super_admin')->exists());
        $this->assertSame(['favorite_menu_ids', 'locale', 'theme'], $user->preferences()->orderBy('key')->pluck('key')->all());
        $this->assertDatabaseCount('hongvan_users', 1);
    }

    public function test_explicit_demo_seed_is_labelled_local_and_idempotent(): void
    {
        config()->set('identity.super_admin.email');
        config()->set('identity.super_admin.password');
        config()->set('media.disk', 'public');
        Storage::fake('public');

        $this->seed(DemoSeeder::class);
        $firstCounts = $this->demoCounts();
        $this->seed(DemoSeeder::class);

        $this->assertSame($firstCounts, $this->demoCounts());
        $this->assertSame([
            'media' => 1,
            'product_categories' => 1,
            'products' => 3,
            'services' => 1,
            'crop_categories' => 1,
            'crops' => 1,
            'crop_stages' => 1,
            'crop_solutions' => 1,
            'vehicle_types' => 1,
            'vehicles' => 1,
            'warehouses' => 1,
        ], $firstCounts);
        Storage::disk('public')->assertExists(DemoMediaSeeder::PATH);
        $this->assertDatabaseHas('hongvan_media', [
            'path' => DemoMediaSeeder::PATH,
            'mime_type' => 'image/png',
            'status' => 'ready',
            'is_locked' => true,
        ]);
        $this->assertDatabaseHas('hongvan_product_translations', [
            'locale' => 'vi',
            'name' => '[DEMO] Sản phẩm mẫu contact',
        ]);
        $this->assertDatabaseCount('hongvan_partners', 0);
        $this->assertDatabaseCount('hongvan_product_translations', 9);
    }

    public function test_product_factory_exposes_required_lifecycle_and_price_states(): void
    {
        $draft = Product::factory()->draft()->contactPrice()->make();
        $published = Product::factory()->published()->fixedPrice()->make();
        $archived = Product::factory()->archived()->rangePrice()->make();

        $this->assertSame('draft', $draft->status);
        $this->assertSame(ProductPriceMode::Contact, $draft->price_mode);
        $this->assertSame('published', $published->status);
        $this->assertSame(ProductPriceMode::Fixed, $published->price_mode);
        $this->assertSame('archived', $archived->status);
        $this->assertSame(ProductPriceMode::Range, $archived->price_mode);
        $this->assertSame('100000.0000', $archived->price_min);
        $this->assertSame('200000.0000', $archived->price_max);
    }

    /** @return array<string, int> */
    private function demoCounts(): array
    {
        return [
            'media' => Media::query()->where('path', DemoMediaSeeder::PATH)->count(),
            'product_categories' => $this->countByCode('hongvan_product_categories', 'DEMO-%'),
            'products' => $this->countByCode('hongvan_products', 'DEMO-%', 'sku'),
            'services' => $this->countByCode('hongvan_services', 'DEMO-%'),
            'crop_categories' => $this->countByCode('hongvan_crop_categories', 'DEMO-%'),
            'crops' => $this->countByCode('hongvan_crops', 'DEMO-%'),
            'crop_stages' => $this->countByCode('hongvan_crop_stages', 'DEMO-%'),
            'crop_solutions' => $this->countByCode('hongvan_crop_solutions', 'DEMO-%'),
            'vehicle_types' => $this->countByCode('hongvan_vehicle_types', 'DEMO-%'),
            'vehicles' => $this->countByCode('hongvan_vehicles', 'DEMO-%'),
            'warehouses' => $this->countByCode('hongvan_warehouses', 'DEMO-%'),
        ];
    }

    private function countByCode(string $table, string $pattern, string $column = 'code'): int
    {
        return (int) app('db')->table($table)->where($column, 'like', $pattern)->count();
    }
}
