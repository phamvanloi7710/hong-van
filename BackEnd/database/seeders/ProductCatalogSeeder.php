<?php

namespace Database\Seeders;

use App\Models\ProductAttributeDefinition;
use Illuminate\Database\Seeder;

final class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['code' => 'nutrient_formula', 'name' => 'Công thức dinh dưỡng', 'data_type' => 'text', 'unit' => null, 'sort_order' => 10],
            ['code' => 'net_weight', 'name' => 'Khối lượng tịnh', 'data_type' => 'decimal', 'unit' => 'kg', 'sort_order' => 20],
            ['code' => 'application_form', 'name' => 'Dạng sử dụng', 'data_type' => 'text', 'unit' => null, 'sort_order' => 30],
        ];

        foreach ($definitions as $definition) {
            ProductAttributeDefinition::query()->updateOrCreate(
                ['code' => $definition['code']],
                [...$definition, 'is_filterable' => false, 'is_required' => false],
            );
        }
    }
}
