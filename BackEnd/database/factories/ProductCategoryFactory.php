<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductCategory> */
final class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('CAT-####-????'),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }
}
