<?php

namespace App\Domain\Products;

use Illuminate\Support\Facades\Lang;

final class ProductPriceResolver
{
    public function __construct(private readonly ProductPriceValidator $validator) {}

    public function resolve(ProductPriceData $price, string $locale = 'vi'): ProductPriceViewModel
    {
        if (! $price->visible || ! $this->isValid($price)) {
            return $this->contactView($price, $locale);
        }

        return match ($price->mode) {
            ProductPriceMode::Fixed => $this->numericView($price, $locale, (string) $price->amount),
            ProductPriceMode::From => $this->numericView($price, $locale, (string) $price->minimum, from: true),
            ProductPriceMode::Range => $this->rangeView($price, $locale),
            ProductPriceMode::Market,
            ProductPriceMode::Dealer,
            ProductPriceMode::Quantity,
            ProductPriceMode::Contact => $this->nonNumericView($price, $locale),
        };
    }

    private function isValid(ProductPriceData $price): bool
    {
        try {
            $this->validator->validate($price);

            return true;
        } catch (InvalidProductPrice) {
            return false;
        }
    }

    private function numericView(ProductPriceData $price, string $locale, string $minimum, bool $from = false): ProductPriceViewModel
    {
        $formatted = $this->formatMoney($minimum, $price->currency, $locale);

        return new ProductPriceViewModel(
            mode: $price->mode,
            showsNumericPrice: true,
            label: $from ? $this->label('from', $locale).' '.$formatted : $formatted,
            formattedMinimum: $formatted,
            formattedMaximum: null,
            currency: $price->currency,
            unit: $price->unit,
            note: $price->note,
            requiresQuote: false,
        );
    }

    private function rangeView(ProductPriceData $price, string $locale): ProductPriceViewModel
    {
        $minimum = $this->formatMoney((string) $price->minimum, $price->currency, $locale);
        $maximum = $this->formatMoney((string) $price->maximum, $price->currency, $locale);

        return new ProductPriceViewModel(
            mode: $price->mode,
            showsNumericPrice: true,
            label: $minimum.' – '.$maximum,
            formattedMinimum: $minimum,
            formattedMaximum: $maximum,
            currency: $price->currency,
            unit: $price->unit,
            note: $price->note,
            requiresQuote: false,
        );
    }

    private function nonNumericView(ProductPriceData $price, string $locale): ProductPriceViewModel
    {
        return new ProductPriceViewModel(
            mode: $price->mode,
            showsNumericPrice: false,
            label: $this->label($price->mode->value, $locale),
            formattedMinimum: null,
            formattedMaximum: null,
            currency: $price->currency,
            unit: $price->unit,
            note: $price->note,
            requiresQuote: true,
        );
    }

    private function contactView(ProductPriceData $price, string $locale): ProductPriceViewModel
    {
        return new ProductPriceViewModel(
            mode: ProductPriceMode::Contact,
            showsNumericPrice: false,
            label: $this->label('contact', $locale),
            formattedMinimum: null,
            formattedMaximum: null,
            currency: $price->currency,
            unit: $price->unit,
            note: $price->note,
            requiresQuote: true,
        );
    }

    private function formatMoney(string $amount, string $currency, string $locale): string
    {
        [$integer, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
        $groupSeparator = $locale === 'vi' ? '.' : ',';
        $formatted = strrev(implode($groupSeparator, str_split(strrev(ltrim($integer, '0') ?: '0'), 3)));
        $fraction = rtrim($fraction, '0');

        if ($fraction !== '') {
            $formatted .= ($locale === 'vi' ? ',' : '.').$fraction;
        }

        return $currency === 'VND' ? $formatted.' ₫' : $currency.' '.$formatted;
    }

    private function label(string $key, string $locale): string
    {
        $supportedLocale = in_array($locale, ['vi', 'en', 'zh'], true) ? $locale : 'en';
        $translationKey = in_array($key, ['from', 'market', 'dealer', 'quantity', 'contact'], true)
            ? $key
            : 'contact';

        return Lang::get('products.price.'.$translationKey, [], $supportedLocale);
    }
}
