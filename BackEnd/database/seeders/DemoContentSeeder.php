<?php

namespace Database\Seeders;

use App\Domain\Localization\TranslatableModel;
use App\Domain\Products\ProductPriceMode;
use App\Models\Crop;
use App\Models\CropCategory;
use App\Models\CropSolution;
use App\Models\CropStage;
use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use RuntimeException;

final class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Demo content cannot be seeded in production.');
        }

        $media = Media::query()->where('path', DemoMediaSeeder::PATH)->firstOrFail();
        $products = $this->seedProducts($media);
        $this->seedServices($media);
        $this->seedCrops($media, $products);
        $this->seedTransportation($media);
        $this->seedWarehouse($media);
    }

    /** @return array<string, Product> */
    private function seedProducts(Media $media): array
    {
        $category = ProductCategory::query()->updateOrCreate(
            ['code' => 'DEMO-FERTILIZER'],
            ['is_active' => true, 'is_featured' => false, 'sort_order' => 900],
        );
        $this->translate($category, [
            'vi' => ['name' => '[DEMO] Nhóm phân bón mẫu', 'slug' => 'demo-nhom-phan-bon-mau', 'summary' => 'Dữ liệu mẫu, không phải danh mục kinh doanh đã xác nhận.'],
            'en' => ['name' => '[DEMO] Sample fertilizer group', 'slug' => 'demo-sample-fertilizer-group', 'summary' => 'Sample data, not a confirmed business category.'],
            'zh' => ['name' => '[DEMO] 示例肥料组', 'slug' => 'demo-sample-fertilizer-group-zh', 'summary' => '示例数据，不代表已确认的业务类别。'],
        ]);

        $definitions = [
            'contact' => ['sku' => 'DEMO-PROD-CONTACT', 'status' => 'published', 'mode' => ProductPriceMode::Contact, 'amount' => null, 'min' => null, 'max' => null, 'visible' => true],
            'fixed' => ['sku' => 'DEMO-PROD-FIXED', 'status' => 'draft', 'mode' => ProductPriceMode::Fixed, 'amount' => '100000.0000', 'min' => null, 'max' => null, 'visible' => true],
            'range' => ['sku' => 'DEMO-PROD-RANGE', 'status' => 'archived', 'mode' => ProductPriceMode::Range, 'amount' => null, 'min' => '100000.0000', 'max' => '200000.0000', 'visible' => true],
        ];
        $products = [];

        foreach ($definitions as $key => $definition) {
            $product = Product::query()->updateOrCreate(
                ['sku' => $definition['sku']],
                [
                    'code' => $definition['sku'],
                    'product_category_id' => $category->getKey(),
                    'status' => $definition['status'],
                    'origin' => null,
                    'packaging' => '[DEMO]',
                    'is_featured' => false,
                    'price_mode' => $definition['mode'],
                    'price_amount' => $definition['amount'],
                    'price_min' => $definition['min'],
                    'price_max' => $definition['max'],
                    'currency' => 'VND',
                    'price_unit' => null,
                    'price_note' => '[DEMO] Giá chỉ dùng kiểm thử.',
                    'is_price_visible' => $definition['visible'],
                    'published_at' => $definition['status'] === 'published' ? now('UTC') : null,
                    'unpublished_at' => null,
                ],
            );
            $this->translate($product, [
                'vi' => ['name' => "[DEMO] Sản phẩm mẫu {$key}", 'slug' => "demo-san-pham-{$key}", 'short_description' => 'Dữ liệu mẫu để kiểm thử catalog và báo giá.', 'description' => null],
                'en' => ['name' => "[DEMO] Sample product {$key}", 'slug' => "demo-product-{$key}", 'short_description' => 'Sample data for catalog and quote testing.', 'description' => null],
                'zh' => ['name' => "[DEMO] 示例产品 {$key}", 'slug' => "demo-product-{$key}-zh", 'short_description' => '用于目录和报价测试的示例数据。', 'description' => null],
            ]);
            $products[$key] = $product;
        }

        $primary = $products['contact'];
        $primary->media()->syncWithoutDetaching([
            $media->getKey() => ['role' => 'primary', 'locale' => 'vi', 'is_primary' => true, 'sort_order' => 0, 'alt_text' => '[DEMO] Ảnh sản phẩm mẫu'],
        ]);
        $this->recordUsage($media, $primary, 'product', 'primary_media');

        return $products;
    }

    private function seedServices(Media $media): void
    {
        $category = ServiceCategory::query()->updateOrCreate(
            ['code' => 'DEMO-SERVICE-CATEGORY'],
            ['is_active' => true, 'sort_order' => 900],
        );
        $this->translate($category, [
            'vi' => ['name' => '[DEMO] Nhóm dịch vụ mẫu', 'slug' => 'demo-nhom-dich-vu-mau', 'summary' => 'Không đại diện cho năng lực thực tế.'],
            'en' => ['name' => '[DEMO] Sample service group', 'slug' => 'demo-sample-service-group', 'summary' => 'Does not represent actual capabilities.'],
            'zh' => ['name' => '[DEMO] 示例服务组', 'slug' => 'demo-sample-service-group-zh', 'summary' => '不代表实际能力。'],
        ]);

        $service = Service::query()->updateOrCreate(
            ['code' => 'DEMO-SERVICE'],
            ['service_category_id' => $category->getKey(), 'service_type' => 'general', 'status' => 'draft', 'cta_type' => 'contact', 'is_featured' => false, 'sort_order' => 900, 'published_at' => null, 'unpublished_at' => null],
        );
        $this->translate($service, [
            'vi' => ['name' => '[DEMO] Dịch vụ mẫu', 'slug' => 'demo-dich-vu-mau', 'summary' => 'Nội dung giả lập chỉ để kiểm thử.', 'content' => null, 'content_sections' => []],
            'en' => ['name' => '[DEMO] Sample service', 'slug' => 'demo-sample-service', 'summary' => 'Simulated content for testing only.', 'content' => null, 'content_sections' => []],
            'zh' => ['name' => '[DEMO] 示例服务', 'slug' => 'demo-sample-service-zh', 'summary' => '仅用于测试的模拟内容。', 'content' => null, 'content_sections' => []],
        ]);
        $service->media()->syncWithoutDetaching([$media->getKey() => ['role' => 'hero', 'sort_order' => 0]]);
        $this->recordUsage($media, $service, 'service', 'hero_media');
    }

    /** @param array<string, Product> $products */
    private function seedCrops(Media $media, array $products): void
    {
        $category = CropCategory::query()->updateOrCreate(
            ['code' => 'DEMO-CROP-CATEGORY'],
            ['image_media_id' => $media->getKey(), 'is_active' => true, 'sort_order' => 900],
        );
        $this->translate($category, $this->namedTranslations('[DEMO] Nhóm cây mẫu', '[DEMO] Sample crop group', '[DEMO] 示例作物组', 'demo-crop-category'));

        $crop = Crop::query()->updateOrCreate(
            ['code' => 'DEMO-CROP'],
            ['crop_category_id' => $category->getKey(), 'image_media_id' => $media->getKey(), 'is_active' => true, 'sort_order' => 900],
        );
        $this->translate($crop, $this->namedTranslations('[DEMO] Cây trồng mẫu', '[DEMO] Sample crop', '[DEMO] 示例作物', 'demo-crop'));

        $stage = CropStage::query()->updateOrCreate(
            ['crop_id' => $crop->getKey(), 'code' => 'DEMO-STAGE'],
            ['image_media_id' => $media->getKey(), 'is_active' => true, 'sort_order' => 900],
        );
        $this->translate($stage, [
            'vi' => ['name' => '[DEMO] Giai đoạn mẫu', 'summary' => 'Dữ liệu mẫu.', 'content' => null],
            'en' => ['name' => '[DEMO] Sample stage', 'summary' => 'Sample data.', 'content' => null],
            'zh' => ['name' => '[DEMO] 示例阶段', 'summary' => '示例数据。', 'content' => null],
        ]);

        $solution = CropSolution::query()->updateOrCreate(
            ['code' => 'DEMO-CROP-SOLUTION'],
            ['crop_id' => $crop->getKey(), 'crop_stage_id' => $stage->getKey(), 'status' => 'draft', 'hero_media_id' => $media->getKey(), 'is_featured' => false, 'sort_order' => 900, 'published_at' => null, 'unpublished_at' => null],
        );
        $this->translate($solution, [
            'vi' => ['title' => '[DEMO] Giải pháp cây trồng mẫu', 'slug' => 'demo-giai-phap-cay-trong-mau', 'summary' => 'Không phải khuyến nghị canh tác thực tế.', 'content' => null, 'content_sections' => []],
            'en' => ['title' => '[DEMO] Sample crop solution', 'slug' => 'demo-sample-crop-solution', 'summary' => 'Not an actual cultivation recommendation.', 'content' => null, 'content_sections' => []],
            'zh' => ['title' => '[DEMO] 示例作物方案', 'slug' => 'demo-sample-crop-solution-zh', 'summary' => '并非实际种植建议。', 'content' => null, 'content_sections' => []],
        ]);
        $solution->products()->syncWithoutDetaching([
            $products['contact']->getKey() => ['sort_order' => 0, 'recommendation_note' => '[DEMO]', 'created_at' => now('UTC')],
        ]);

        foreach ([['crop_category', $category], ['crop', $crop], ['crop_stage', $stage], ['crop_solution', $solution]] as [$ownerType, $owner]) {
            $this->recordUsage($media, $owner, $ownerType, 'image');
        }
    }

    private function seedTransportation(Media $media): void
    {
        $type = VehicleType::query()->updateOrCreate(
            ['code' => 'DEMO-VEHICLE-TYPE'],
            ['is_active' => true, 'sort_order' => 900],
        );
        $this->translate($type, [
            'vi' => ['name' => '[DEMO] Loại xe mẫu', 'description' => 'Không đại diện đội xe thực tế.'],
            'en' => ['name' => '[DEMO] Sample vehicle type', 'description' => 'Does not represent an actual fleet.'],
            'zh' => ['name' => '[DEMO] 示例车辆类型', 'description' => '不代表实际车队。'],
        ]);

        $vehicle = Vehicle::query()->updateOrCreate(
            ['code' => 'DEMO-VEHICLE'],
            ['vehicle_type_id' => $type->getKey(), 'payload_capacity' => null, 'payload_unit' => null, 'availability_display' => 'contact', 'status' => 'draft', 'is_featured' => false, 'sort_order' => 900, 'published_at' => null, 'unpublished_at' => null],
        );
        $this->translate($vehicle, [
            'vi' => ['name' => '[DEMO] Phương tiện mẫu', 'slug' => 'demo-phuong-tien-mau', 'summary' => 'Dữ liệu mẫu, không phải phương tiện thực tế.', 'description' => null],
            'en' => ['name' => '[DEMO] Sample vehicle', 'slug' => 'demo-sample-vehicle', 'summary' => 'Sample data, not an actual vehicle.', 'description' => null],
            'zh' => ['name' => '[DEMO] 示例车辆', 'slug' => 'demo-sample-vehicle-zh', 'summary' => '示例数据，并非实际车辆。', 'description' => null],
        ]);
        $vehicle->media()->syncWithoutDetaching([$media->getKey() => ['role' => 'hero', 'sort_order' => 0]]);
        $this->recordUsage($media, $vehicle, 'vehicle', 'hero_media');
    }

    private function seedWarehouse(Media $media): void
    {
        $warehouse = Warehouse::query()->updateOrCreate(
            ['code' => 'DEMO-WAREHOUSE'],
            ['area_value' => null, 'area_unit' => null, 'latitude' => null, 'longitude' => null, 'map_display' => 'hidden', 'business_hours' => null, 'status' => 'draft', 'is_featured' => false, 'sort_order' => 900, 'published_at' => null, 'unpublished_at' => null],
        );
        $this->translate($warehouse, [
            'vi' => ['name' => '[DEMO] Kho mẫu', 'slug' => 'demo-kho-mau', 'summary' => 'Dữ liệu mẫu, không phải địa điểm hoặc năng lực thật.', 'description' => null, 'address_display' => null],
            'en' => ['name' => '[DEMO] Sample warehouse', 'slug' => 'demo-sample-warehouse', 'summary' => 'Sample data, not an actual location or capability.', 'description' => null, 'address_display' => null],
            'zh' => ['name' => '[DEMO] 示例仓库', 'slug' => 'demo-sample-warehouse-zh', 'summary' => '示例数据，不代表实际地点或能力。', 'description' => null, 'address_display' => null],
        ]);
        $warehouse->media()->syncWithoutDetaching([$media->getKey() => ['role' => 'hero', 'sort_order' => 0]]);
        $this->recordUsage($media, $warehouse, 'warehouse', 'hero_media');
    }

    /** @param array<string, array<string, mixed>> $translations */
    private function translate(TranslatableModel $model, array $translations): void
    {
        foreach ($translations as $locale => $attributes) {
            $model->translations()->updateOrCreate(['locale' => $locale], $attributes);
        }
    }

    /** @return array<string, array<string, string|null>> */
    private function namedTranslations(string $vi, string $en, string $zh, string $slug): array
    {
        return [
            'vi' => ['name' => $vi, 'slug' => $slug.'-vi', 'summary' => 'Dữ liệu mẫu, không phải thông tin thực tế.'],
            'en' => ['name' => $en, 'slug' => $slug.'-en', 'summary' => 'Sample data, not actual information.'],
            'zh' => ['name' => $zh, 'slug' => $slug.'-zh', 'summary' => '示例数据，并非实际信息。'],
        ];
    }

    private function recordUsage(Media $media, Model $owner, string $ownerType, string $field): void
    {
        MediaUsage::query()->updateOrCreate(
            ['media_id' => $media->getKey(), 'owner_type' => $ownerType, 'owner_public_id' => (string) $owner->getAttribute('public_id'), 'field' => $field],
            ['metadata' => ['demo' => true]],
        );
    }
}
