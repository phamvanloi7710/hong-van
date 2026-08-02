<?php

namespace App\Domain\Products;

final readonly class ProductPriceViewModel
{
    public function __construct(
        public ProductPriceMode $mode,
        public bool $showsNumericPrice,
        public string $label,
        public ?string $formattedMinimum,
        public ?string $formattedMaximum,
        public string $currency,
        public ?string $unit,
        public ?string $note,
        public bool $requiresQuote,
    ) {}
}
