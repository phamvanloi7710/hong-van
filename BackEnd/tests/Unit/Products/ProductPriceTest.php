<?php

namespace Tests\Unit\Products;

use App\Domain\Products\InvalidProductPrice;
use App\Domain\Products\ProductPriceData;
use App\Domain\Products\ProductPriceMode;
use App\Domain\Products\ProductPriceResolver;
use App\Domain\Products\ProductPriceValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ProductPriceTest extends TestCase
{
    private ProductPriceResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new ProductPriceResolver(new ProductPriceValidator);
    }

    public function test_fixed_price_preserves_currency_unit_and_note(): void
    {
        $view = $this->resolver->resolve(new ProductPriceData(
            mode: ProductPriceMode::Fixed,
            amount: '125000.5000',
            currency: 'VND',
            unit: 'bao 50 kg',
            note: 'Giá tham khảo',
        ));

        $this->assertTrue($view->showsNumericPrice);
        $this->assertFalse($view->requiresQuote);
        $this->assertSame('125.000,5 ₫', $view->label);
        $this->assertSame('VND', $view->currency);
        $this->assertSame('bao 50 kg', $view->unit);
        $this->assertSame('Giá tham khảo', $view->note);
    }

    public function test_from_price_uses_requested_language(): void
    {
        $price = new ProductPriceData(mode: ProductPriceMode::From, minimum: '1000.0000', currency: 'USD');

        $this->assertSame('From USD 1,000', $this->resolver->resolve($price, 'en')->label);
        $this->assertSame('起价 USD 1,000', $this->resolver->resolve($price, 'zh')->label);
    }

    public function test_range_price_formats_both_exact_amounts(): void
    {
        $view = $this->resolver->resolve(new ProductPriceData(
            mode: ProductPriceMode::Range,
            minimum: '100000.0000',
            maximum: '250000.0000',
        ));

        $this->assertSame('100.000 ₫ – 250.000 ₫', $view->label);
        $this->assertSame('100.000 ₫', $view->formattedMinimum);
        $this->assertSame('250.000 ₫', $view->formattedMaximum);
    }

    /** @return iterable<string, array{ProductPriceMode, string, string}> */
    public static function quoteModeProvider(): iterable
    {
        yield 'contact' => [ProductPriceMode::Contact, 'Liên hệ báo giá', 'Contact for a quote'];
        yield 'market' => [ProductPriceMode::Market, 'Giá thị trường', 'Market price'];
        yield 'dealer' => [ProductPriceMode::Dealer, 'Liên hệ giá đại lý', 'Contact for dealer price'];
        yield 'quantity' => [ProductPriceMode::Quantity, 'Giá theo số lượng', 'Quantity pricing'];
    }

    #[DataProvider('quoteModeProvider')]
    public function test_non_numeric_modes_require_a_quote(
        ProductPriceMode $mode,
        string $vietnameseLabel,
        string $englishLabel,
    ): void {
        $price = new ProductPriceData(mode: $mode);

        $vietnamese = $this->resolver->resolve($price, 'vi');
        $english = $this->resolver->resolve($price, 'en');

        $this->assertFalse($vietnamese->showsNumericPrice);
        $this->assertTrue($vietnamese->requiresQuote);
        $this->assertSame($vietnameseLabel, $vietnamese->label);
        $this->assertSame($englishLabel, $english->label);
    }

    /** @return iterable<string, array{ProductPriceData}> */
    public static function invalidNumericPriceProvider(): iterable
    {
        yield 'fixed null' => [new ProductPriceData(mode: ProductPriceMode::Fixed)];
        yield 'fixed zero' => [new ProductPriceData(mode: ProductPriceMode::Fixed, amount: '0.0000')];
        yield 'from null' => [new ProductPriceData(mode: ProductPriceMode::From)];
        yield 'from zero' => [new ProductPriceData(mode: ProductPriceMode::From, minimum: '0')];
        yield 'range null' => [new ProductPriceData(mode: ProductPriceMode::Range)];
        yield 'range inverted' => [new ProductPriceData(mode: ProductPriceMode::Range, minimum: '20', maximum: '10')];
    }

    #[DataProvider('invalidNumericPriceProvider')]
    public function test_invalid_numeric_prices_never_render_zero_currency(ProductPriceData $price): void
    {
        $view = $this->resolver->resolve($price, 'vi');

        $this->assertSame(ProductPriceMode::Contact, $view->mode);
        $this->assertSame('Liên hệ báo giá', $view->label);
        $this->assertStringNotContainsString('0 ₫', $view->label);
        $this->assertTrue($view->requiresQuote);
    }

    public function test_hidden_numeric_price_falls_back_to_contact(): void
    {
        $view = $this->resolver->resolve(new ProductPriceData(
            mode: ProductPriceMode::Fixed,
            amount: '999000.0000',
            visible: false,
        ));

        $this->assertSame(ProductPriceMode::Contact, $view->mode);
        $this->assertSame('Liên hệ báo giá', $view->label);
    }

    public function test_validator_rejects_invalid_currency_code(): void
    {
        $this->expectException(InvalidProductPrice::class);

        (new ProductPriceValidator)->validate(new ProductPriceData(
            mode: ProductPriceMode::Contact,
            currency: 'vnd',
        ));
    }
}
