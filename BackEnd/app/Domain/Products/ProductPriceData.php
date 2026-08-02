<?php

namespace App\Domain\Products;

use App\Models\Product;

final readonly class ProductPriceData
{
    public function __construct(
        public ProductPriceMode $mode,
        public ?string $amount = null,
        public ?string $minimum = null,
        public ?string $maximum = null,
        public string $currency = 'VND',
        public ?string $unit = null,
        public ?string $note = null,
        public bool $visible = true,
    ) {}

    public static function fromProduct(Product $product): self
    {
        $mode = $product->getAttribute('price_mode');

        return new self(
            mode: $mode instanceof ProductPriceMode ? $mode : ProductPriceMode::from((string) $mode),
            amount: self::nullableDecimal($product->getAttribute('price_amount')),
            minimum: self::nullableDecimal($product->getAttribute('price_min')),
            maximum: self::nullableDecimal($product->getAttribute('price_max')),
            currency: (string) $product->getAttribute('currency'),
            unit: self::nullableString($product->getAttribute('price_unit')),
            note: self::nullableString($product->getAttribute('price_note')),
            visible: (bool) $product->getAttribute('is_price_visible'),
        );
    }

    private static function nullableDecimal(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            throw new InvalidProductPrice('A product price amount must be numeric.');
        }

        return (string) $value;
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw new InvalidProductPrice('A product price display field must be text.');
        }

        return $value;
    }
}
