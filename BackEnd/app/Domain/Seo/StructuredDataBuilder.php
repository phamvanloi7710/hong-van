<?php

namespace App\Domain\Seo;

use App\Domain\Products\ProductPriceMode;
use App\Domain\Settings\CompanySettingsService;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Service;
use App\Models\ServiceTranslation;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final readonly class StructuredDataBuilder
{
    public function __construct(private CompanySettingsService $settings) {}

    /** @return array<string, mixed> */
    public function organization(): array
    {
        $name = trim((string) $this->settings->value('legal', 'legal_name', $this->settings->value('company', 'company_name', '')));
        $data = ['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => $name, 'url' => url('/')];
        $taxCode = trim((string) $this->settings->value('legal', 'tax_code', ''));
        $phone = trim((string) $this->settings->value('contact', 'primary_phone', ''));
        if ($taxCode !== '') {
            $data['taxID'] = $taxCode;
        }
        if ($phone !== '') {
            $data['telephone'] = $phone;
        }

        return $data;
    }

    /** @return array<string, mixed>|null */
    public function localBusiness(): ?array
    {
        $address = trim((string) $this->settings->value('contact', 'primary_address', ''));
        if ($address === '') {
            return null;
        }
        $data = $this->organization();
        $data['@type'] = 'LocalBusiness';
        $data['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $address, 'addressCountry' => 'VN'];
        $latitude = $this->settings->value('map', 'latitude');
        $longitude = $this->settings->value('map', 'longitude');
        if (is_numeric($latitude) && is_numeric($longitude)) {
            $data['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) $latitude, 'longitude' => (float) $longitude];
        }

        return $data;
    }

    /** @return array<string, mixed> */
    public function website(string $locale): array
    {
        return ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => (string) $this->settings->value('company', 'company_name', ''), 'url' => url('/'), 'inLanguage' => $locale];
    }

    /**
     * @param  list<array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbs(array $items): array
    {
        if ($items === []) {
            throw new InvalidArgumentException('Breadcrumb items are required.');
        }
        $elements = [];
        foreach ($items as $index => $item) {
            if (trim($item['name']) === '' || ! $this->httpUrl($item['url'])) {
                throw new InvalidArgumentException('Breadcrumb names and HTTP URLs are required.');
            }
            $elements[] = ['@type' => 'ListItem', 'position' => $index + 1, 'name' => trim(strip_tags($item['name'])), 'item' => $item['url']];
        }

        return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $elements];
    }

    /** @return array<string, mixed> */
    public function product(Product $product, string $locale, string $canonicalUrl): array
    {
        $translation = ProductTranslation::query()->where('product_id', $product->getKey())->where('locale', $locale)->firstOrFail();
        $data = ['@context' => 'https://schema.org', '@type' => 'Product', 'name' => strip_tags((string) $translation->name), 'url' => $canonicalUrl];
        if ($translation->short_description) {
            $data['description'] = strip_tags((string) $translation->short_description);
        }
        $mode = ProductPriceMode::tryFrom((string) $product->getRawOriginal('price_mode'));
        if ($product->is_price_visible && $mode === ProductPriceMode::Fixed && (float) $product->price_amount > 0) {
            $data['offers'] = ['@type' => 'Offer', 'price' => (string) $product->price_amount, 'priceCurrency' => $product->currency ?: 'VND', 'url' => $canonicalUrl];
        } elseif ($product->is_price_visible && $mode === ProductPriceMode::Range && (float) $product->price_min > 0 && (float) $product->price_max >= (float) $product->price_min) {
            $data['offers'] = ['@type' => 'AggregateOffer', 'lowPrice' => (string) $product->price_min, 'highPrice' => (string) $product->price_max, 'priceCurrency' => $product->currency ?: 'VND'];
        }

        return $data;
    }

    /** @return array<string, mixed> */
    public function article(Post $post, string $locale, string $canonicalUrl): array
    {
        $translation = PostTranslation::query()->where('post_id', $post->getKey())->where('locale', $locale)->firstOrFail();

        return array_filter(['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => strip_tags((string) $translation->title), 'description' => $translation->excerpt ? strip_tags((string) $translation->excerpt) : null, 'url' => $canonicalUrl, 'datePublished' => $this->date($post, 'published_at'), 'dateModified' => $this->date($post, 'updated_at')], fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /** @return array<string, mixed> */
    public function service(Service $service, string $locale, string $canonicalUrl): array
    {
        $translation = ServiceTranslation::query()->where('service_id', $service->getKey())->where('locale', $locale)->firstOrFail();

        return array_filter(['@context' => 'https://schema.org', '@type' => 'Service', 'name' => strip_tags((string) $translation->name), 'description' => $translation->summary ? strip_tags((string) $translation->summary) : null, 'url' => $canonicalUrl, 'provider' => $this->organization()], fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * @param  list<array{question: string, answer: string}>  $items
     * @return array<string, mixed>
     */
    public function faq(array $items): array
    {
        if ($items === []) {
            throw new InvalidArgumentException('FAQ items are required.');
        }
        $entities = [];
        foreach ($items as $item) {
            $question = trim(strip_tags($item['question']));
            $answer = trim(strip_tags($item['answer']));
            if ($question === '' || $answer === '') {
                throw new InvalidArgumentException('FAQ questions and answers are required.');
            }
            $entities[] = ['@type' => 'Question', 'name' => $question, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $answer]];
        }

        return ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $entities];
    }

    /** @param array<string, mixed> $schema */
    public function encode(array $schema): string
    {
        return json_encode($schema, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    private function httpUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    private function date(Model $model, string $attribute): ?string
    {
        $value = $model->getAttribute($attribute);

        return $value instanceof DateTimeInterface ? $value->format(DATE_ATOM) : null;
    }
}
