<?php

namespace App\Domain\Products;

final class ProductPriceValidator
{
    public function validate(ProductPriceData $price): void
    {
        match ($price->mode) {
            ProductPriceMode::Fixed => $this->requirePositive($price->amount, 'price_amount'),
            ProductPriceMode::From => $this->requirePositive($price->minimum, 'price_min'),
            ProductPriceMode::Range => $this->validateRange($price->minimum, $price->maximum),
            ProductPriceMode::Market,
            ProductPriceMode::Dealer,
            ProductPriceMode::Quantity,
            ProductPriceMode::Contact => null,
        };

        if (preg_match('/^[A-Z]{3}$/', $price->currency) !== 1) {
            throw new InvalidProductPrice('currency must be a three-letter uppercase ISO 4217 code.');
        }
    }

    private function validateRange(?string $minimum, ?string $maximum): void
    {
        $this->requirePositive($minimum, 'price_min');
        $this->requirePositive($maximum, 'price_max');

        if ($this->compareDecimals((string) $minimum, (string) $maximum) > 0) {
            throw new InvalidProductPrice('price_min must be less than or equal to price_max.');
        }
    }

    private function requirePositive(?string $value, string $field): void
    {
        if ($value === null || ! $this->isPositiveDecimal($value)) {
            throw new InvalidProductPrice($field.' must be a positive decimal amount.');
        }
    }

    private function isPositiveDecimal(string $value): bool
    {
        if (preg_match('/^\d+(?:\.\d+)?$/', $value) !== 1) {
            return false;
        }

        return trim(str_replace(['.', '0'], '', $value)) !== '';
    }

    private function compareDecimals(string $left, string $right): int
    {
        [$leftInteger, $leftFraction] = $this->decimalParts($left);
        [$rightInteger, $rightFraction] = $this->decimalParts($right);

        $integerLength = max(strlen($leftInteger), strlen($rightInteger));
        $integerComparison = strcmp(
            str_pad($leftInteger, $integerLength, '0', STR_PAD_LEFT),
            str_pad($rightInteger, $integerLength, '0', STR_PAD_LEFT),
        );

        if ($integerComparison !== 0) {
            return $integerComparison;
        }

        $fractionLength = max(strlen($leftFraction), strlen($rightFraction));

        return strcmp(
            str_pad($leftFraction, $fractionLength, '0'),
            str_pad($rightFraction, $fractionLength, '0'),
        );
    }

    /** @return array{0: string, 1: string} */
    private function decimalParts(string $value): array
    {
        [$integer, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        return [ltrim($integer, '0') ?: '0', rtrim($fraction, '0')];
    }
}
