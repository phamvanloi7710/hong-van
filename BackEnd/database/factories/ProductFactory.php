<?php

namespace Database\Factories;

use App\Domain\Products\ProductPriceMode;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->bothify('HV-#####-????'),
            'code' => fake()->unique()->optional()->bothify('SP-#####'),
            'status' => 'draft',
            'origin' => null,
            'packaging' => null,
            'is_featured' => false,
            'price_mode' => ProductPriceMode::Contact,
            'price_amount' => null,
            'price_min' => null,
            'price_max' => null,
            'currency' => 'VND',
            'price_unit' => null,
            'price_note' => null,
            'is_price_visible' => true,
            'published_at' => null,
            'unpublished_at' => null,
        ];
    }

    public function fixedPrice(string $amount = '100000.0000', string $currency = 'VND'): static
    {
        return $this->state(fn (): array => [
            'price_mode' => ProductPriceMode::Fixed,
            'price_amount' => $amount,
            'currency' => $currency,
            'is_price_visible' => true,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => 'published',
            'published_at' => now()->utc(),
            'unpublished_at' => null,
        ]);
    }
}
