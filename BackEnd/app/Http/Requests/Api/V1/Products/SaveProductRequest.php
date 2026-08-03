<?php

namespace App\Http\Requests\Api\V1\Products;

use App\Domain\Products\InvalidProductPrice;
use App\Domain\Products\ProductPriceData;
use App\Domain\Products\ProductPriceMode;
use App\Domain\Products\ProductPriceValidator;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SaveProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->getKey() : null;

        return [
            'sku' => ['required', 'string', 'max:100', Rule::unique('hongvan_products', 'sku')->ignore($productId)],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('hongvan_products', 'code')->ignore($productId)],
            'status' => ['required', 'string', 'in:draft,published,archived,scheduled'],
            'category_id' => ['nullable', 'ulid', 'exists:hongvan_product_categories,public_id'],
            'brand_id' => ['nullable', 'ulid', 'exists:hongvan_brands,public_id'],
            'origin' => ['nullable', 'string', 'max:255'],
            'packaging' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['required', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'unpublished_at' => ['nullable', 'date', 'after:published_at'],
            'price' => ['required', 'array:mode,amount,minimum,maximum,currency,unit,note,visible'],
            'price.mode' => ['required', 'string', 'in:fixed,from,range,market,dealer,quantity,contact'],
            'price.amount' => ['nullable', 'decimal:0,4'],
            'price.minimum' => ['nullable', 'decimal:0,4'],
            'price.maximum' => ['nullable', 'decimal:0,4'],
            'price.currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'price.unit' => ['nullable', 'string', 'max:100'],
            'price.note' => ['nullable', 'string', 'max:4000'],
            'price.visible' => ['required', 'boolean'],
            'translations' => ['required', 'array', 'min:1', 'max:3'],
            'translations.*' => ['required', 'array:locale,name,slug,short_description,description,benefits,usage_instructions,meta_title,meta_description'],
            'translations.*.locale' => ['required', 'string', 'distinct', 'in:vi,en,zh'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'translations.*.short_description' => ['nullable', 'string', 'max:4000'],
            'translations.*.description' => ['nullable', 'string', 'max:100000'],
            'translations.*.benefits' => ['nullable', 'string', 'max:100000'],
            'translations.*.usage_instructions' => ['nullable', 'string', 'max:100000'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:4000'],
            'media' => ['sometimes', 'array', 'max:100'],
            'media.*' => ['required', 'array:media_id,role,locale,is_primary,sort_order,alt_text'],
            'media.*.media_id' => ['required', 'ulid', 'distinct', 'exists:hongvan_media,public_id'],
            'media.*.role' => ['required', 'string', 'in:primary,gallery,document,certificate'],
            'media.*.locale' => ['required', 'string', 'in:*,vi,en,zh'],
            'media.*.is_primary' => ['required', 'boolean'],
            'media.*.sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'media.*.alt_text' => ['nullable', 'string', 'max:255'],
            'tag_ids' => ['sometimes', 'array', 'max:100'],
            'tag_ids.*' => ['required', 'ulid', 'distinct', 'exists:hongvan_product_tags,public_id'],
            'attributes' => ['sometimes', 'array', 'max:100'],
            'attributes.*' => ['required', 'array:definition_id,locale,value_text,value_decimal,value_boolean,value_json'],
            'attributes.*.definition_id' => ['required', 'ulid', 'exists:hongvan_product_attribute_definitions,public_id'],
            'attributes.*.locale' => ['required', 'string', 'in:*,vi,en,zh'],
            'attributes.*.value_text' => ['nullable', 'string', 'max:10000'],
            'attributes.*.value_decimal' => ['nullable', 'decimal:0,4'],
            'attributes.*.value_boolean' => ['nullable', 'boolean'],
            'attributes.*.value_json' => ['nullable', 'array'],
            'specifications' => ['sometimes', 'array', 'max:200'],
            'specifications.*' => ['required', 'array:locale,label,value,unit,sort_order'],
            'specifications.*.locale' => ['required', 'string', 'in:vi,en,zh'],
            'specifications.*.label' => ['required', 'string', 'max:255'],
            'specifications.*.value' => ['required', 'string', 'max:10000'],
            'specifications.*.unit' => ['nullable', 'string', 'max:64'],
            'specifications.*.sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'related_product_ids' => ['sometimes', 'array', 'max:50'],
            'related_product_ids.*' => ['required', 'ulid', 'distinct', 'exists:hongvan_products,public_id'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $this->validatePrice($validator);
            $this->validateTranslationSlugs($validator);
            $this->validateMediaPrimaries($validator);
            $this->validateRelatedProducts($validator);
        }];
    }

    protected function prepareForValidation(): void
    {
        $price = $this->input('price');
        if (! is_array($price)) {
            return;
        }

        foreach (['amount', 'minimum', 'maximum'] as $key) {
            if (isset($price[$key]) && (is_int($price[$key]) || is_float($price[$key]))) {
                $price[$key] = (string) $price[$key];
            }
        }

        $this->merge(['price' => $price]);
    }

    private function validatePrice(Validator $validator): void
    {
        $price = $this->input('price');
        if (! is_array($price) || ! isset($price['mode'], $price['currency'], $price['visible'])) {
            return;
        }

        try {
            app(ProductPriceValidator::class)->validate(new ProductPriceData(
                mode: ProductPriceMode::from((string) $price['mode']),
                amount: $this->nullableString($price['amount'] ?? null),
                minimum: $this->nullableString($price['minimum'] ?? null),
                maximum: $this->nullableString($price['maximum'] ?? null),
                currency: (string) $price['currency'],
                unit: $this->nullableString($price['unit'] ?? null),
                note: $this->nullableString($price['note'] ?? null),
                visible: filter_var($price['visible'], FILTER_VALIDATE_BOOL),
            ));
        } catch (InvalidProductPrice|\ValueError) {
            $validator->errors()->add('price', __('products.invalid_price'));
        }
    }

    private function validateTranslationSlugs(Validator $validator): void
    {
        $product = $this->route('product');
        foreach ((array) $this->input('translations', []) as $index => $translation) {
            if (! is_array($translation) || ! is_string($translation['locale'] ?? null) || ! is_string($translation['slug'] ?? null)) {
                continue;
            }
            $exists = DB::table('hongvan_product_translations')
                ->where('locale', $translation['locale'])
                ->where('slug', $translation['slug'])
                ->when($product instanceof Product, static fn ($query) => $query->where('product_id', '!=', $product->getKey()))
                ->exists();
            if ($exists) {
                $validator->errors()->add("translations.$index.slug", __('products.slug_taken'));
            }
        }
    }

    private function validateMediaPrimaries(Validator $validator): void
    {
        $primarySlots = [];
        foreach ((array) $this->input('media', []) as $index => $media) {
            if (! is_array($media) || ! filter_var($media['is_primary'] ?? false, FILTER_VALIDATE_BOOL)) {
                continue;
            }
            $slot = ($media['role'] ?? '').'|'.($media['locale'] ?? '');
            if (isset($primarySlots[$slot])) {
                $validator->errors()->add("media.$index.is_primary", __('products.duplicate_primary_media'));
            }
            $primarySlots[$slot] = true;
        }
    }

    private function validateRelatedProducts(Validator $validator): void
    {
        $product = $this->route('product');
        if ($product instanceof Product && in_array($product->public_id, (array) $this->input('related_product_ids', []), true)) {
            $validator->errors()->add('related_product_ids', __('products.related_self'));
        }
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
