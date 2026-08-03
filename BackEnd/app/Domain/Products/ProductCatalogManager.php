<?php

namespace App\Domain\Products;

use App\Domain\Audit\AuditTrail;
use App\Domain\Localization\TranslatableModel;
use App\Domain\Media\MediaUsageTracker;
use App\Exceptions\ConflictException;
use App\Models\Brand;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductAttributeDefinition;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class ProductCatalogManager
{
    public function __construct(
        private ProductPriceValidator $priceValidator,
        private MediaUsageTracker $mediaUsage,
        private AuditTrail $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function createProduct(User $actor, array $data): Product
    {
        return DB::transaction(function () use ($actor, $data): Product {
            $this->validatePrice($data);
            $product = Product::query()->create([
                ...$this->productAttributes($data),
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);
            $this->syncProductRelations($product, $data);
            $this->record('product.created', $actor, $product, ['sku' => $product->sku]);

            return $this->loadProduct($product);
        });
    }

    /** @param array<string, mixed> $data */
    public function updateProduct(User $actor, Product $product, array $data): Product
    {
        return DB::transaction(function () use ($actor, $product, $data): Product {
            $this->validatePrice($data);
            $product->fill([
                ...$this->productAttributes($data),
                'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncProductRelations($product, $data);
            $this->record('product.updated', $actor, $product, ['fields' => array_keys($data)]);

            return $this->loadProduct($product);
        });
    }

    public function trashProduct(User $actor, Product $product): Product
    {
        $product->forceFill(['deleted_by' => $actor->getKey()])->save();
        $product->delete();
        $this->record('product.trashed', $actor, $product);

        return $product;
    }

    public function restoreProduct(User $actor, Product $product): Product
    {
        $product->restore();
        $product->forceFill(['deleted_by' => null, 'updated_by' => $actor->getKey()])->save();
        $this->record('product.restored', $actor, $product);

        return $this->loadProduct($product);
    }

    public function publishProduct(User $actor, Product $product): Product
    {
        $product->forceFill([
            'status' => 'published',
            'published_at' => $product->published_at ?? now('UTC'),
            'unpublished_at' => null,
            'updated_by' => $actor->getKey(),
        ])->save();
        $this->record('product.published', $actor, $product);

        return $this->loadProduct($product);
    }

    public function archiveProduct(User $actor, Product $product): Product
    {
        $product->forceFill(['status' => 'archived', 'updated_by' => $actor->getKey()])->save();
        $this->record('product.archived', $actor, $product);

        return $this->loadProduct($product);
    }

    /** @param list<Product> $products */
    public function bulkStatus(User $actor, array $products, string $action): void
    {
        DB::transaction(function () use ($actor, $products, $action): void {
            foreach ($products as $product) {
                $action === 'publish'
                    ? $this->publishProduct($actor, $product)
                    : $this->archiveProduct($actor, $product);
            }
        });
    }

    /** @param array<string, mixed> $data */
    public function saveCategory(User $actor, ?ProductCategory $category, array $data): ProductCategory
    {
        return DB::transaction(function () use ($actor, $category, $data): ProductCategory {
            $category ??= new ProductCategory;
            $category->fill([
                ...Arr::only($data, ['code', 'is_active', 'is_featured', 'sort_order']),
                'parent_id' => $this->internalId(ProductCategory::class, $data['parent_id'] ?? null),
                $category->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncTranslations($category, $data['translations']);
            $this->record($category->wasRecentlyCreated ? 'product_category.created' : 'product_category.updated', $actor, $category);

            return $category->fresh(['translations', 'parent.translations']);
        });
    }

    public function trashCategory(User $actor, ProductCategory $category): void
    {
        if ($category->products()->exists() || $category->children()->exists()) {
            throw new ConflictException(__('products.category_in_use'));
        }

        $category->forceFill(['deleted_by' => $actor->getKey()])->save();
        $category->delete();
        $this->record('product_category.trashed', $actor, $category);
    }

    public function restoreCategory(User $actor, ProductCategory $category): ProductCategory
    {
        $category->restore();
        $category->forceFill(['deleted_by' => null, 'updated_by' => $actor->getKey()])->save();
        $this->record('product_category.restored', $actor, $category);

        return $category->fresh(['translations', 'parent']);
    }

    /** @param array<string, mixed> $data */
    public function saveBrand(User $actor, ?Brand $brand, array $data): Brand
    {
        return DB::transaction(function () use ($actor, $brand, $data): Brand {
            $brand ??= new Brand;
            $oldLogo = $brand->logo;
            $brand->fill([
                ...Arr::only($data, ['code', 'is_active', 'sort_order']),
                'logo_media_id' => $this->internalId(Media::class, $data['logo_media_id'] ?? null),
                $brand->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncTranslations($brand, $data['translations']);
            if ($oldLogo instanceof Media && $oldLogo->getKey() !== $brand->logo_media_id) {
                $this->mediaUsage->release($oldLogo, 'product', $brand->public_id, 'brand_logo');
            }
            $brand->load('logo');
            if ($brand->logo instanceof Media) {
                $this->mediaUsage->track($brand->logo, 'product', $brand->public_id, 'brand_logo');
            }
            $this->record($brand->wasRecentlyCreated ? 'brand.created' : 'brand.updated', $actor, $brand);

            return $brand->fresh(['translations', 'logo']);
        });
    }

    public function trashBrand(User $actor, Brand $brand): void
    {
        if ($brand->products()->exists()) {
            throw new ConflictException(__('products.brand_in_use'));
        }

        if ($brand->logo instanceof Media) {
            $this->mediaUsage->release($brand->logo, 'product', $brand->public_id, 'brand_logo');
        }
        $brand->forceFill(['deleted_by' => $actor->getKey()])->save();
        $brand->delete();
        $this->record('brand.trashed', $actor, $brand);
    }

    public function restoreBrand(User $actor, Brand $brand): Brand
    {
        $brand->restore();
        $brand->forceFill(['deleted_by' => null, 'updated_by' => $actor->getKey()])->save();
        $this->record('brand.restored', $actor, $brand);

        return $brand->fresh(['translations', 'logo']);
    }

    /** @param array<string, mixed> $data */
    public function saveTag(User $actor, ?ProductTag $tag, array $data): ProductTag
    {
        $tag ??= new ProductTag;
        $tag->fill([
            ...Arr::only($data, ['name', 'slug']),
            $tag->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ])->save();
        $this->record($tag->wasRecentlyCreated ? 'product_tag.created' : 'product_tag.updated', $actor, $tag);

        return $tag->fresh();
    }

    public function deleteTag(User $actor, ProductTag $tag): void
    {
        if ($tag->products()->exists()) {
            throw new ConflictException(__('products.tag_in_use'));
        }
        $this->record('product_tag.deleted', $actor, $tag);
        $tag->delete();
    }

    /** @param array<string, mixed> $data */
    public function saveAttribute(User $actor, ?ProductAttributeDefinition $attribute, array $data): ProductAttributeDefinition
    {
        $attribute ??= new ProductAttributeDefinition;
        $attribute->fill([
            ...Arr::only($data, ['code', 'name', 'data_type', 'unit', 'options', 'is_filterable', 'is_required', 'sort_order']),
            $attribute->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ])->save();
        $this->record($attribute->wasRecentlyCreated ? 'product_attribute.created' : 'product_attribute.updated', $actor, $attribute);

        return $attribute->fresh();
    }

    public function deleteAttribute(User $actor, ProductAttributeDefinition $attribute): void
    {
        if ($attribute->values()->exists()) {
            throw new ConflictException(__('products.attribute_in_use'));
        }
        $this->record('product_attribute.deleted', $actor, $attribute);
        $attribute->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function productAttributes(array $data): array
    {
        $price = $data['price'];

        return [
            ...Arr::only($data, ['sku', 'code', 'status', 'origin', 'packaging', 'is_featured', 'published_at', 'unpublished_at']),
            'product_category_id' => $this->internalId(ProductCategory::class, $data['category_id'] ?? null),
            'brand_id' => $this->internalId(Brand::class, $data['brand_id'] ?? null),
            'price_mode' => $price['mode'],
            'price_amount' => $price['amount'] ?? null,
            'price_min' => $price['minimum'] ?? null,
            'price_max' => $price['maximum'] ?? null,
            'currency' => $price['currency'],
            'price_unit' => $price['unit'] ?? null,
            'price_note' => $price['note'] ?? null,
            'is_price_visible' => $price['visible'],
        ];
    }

    /** @param array<string, mixed> $data */
    private function validatePrice(array $data): void
    {
        $price = $data['price'];
        $this->priceValidator->validate(new ProductPriceData(
            mode: ProductPriceMode::from($price['mode']),
            amount: $price['amount'] ?? null,
            minimum: $price['minimum'] ?? null,
            maximum: $price['maximum'] ?? null,
            currency: $price['currency'],
            unit: $price['unit'] ?? null,
            note: $price['note'] ?? null,
            visible: $price['visible'],
        ));
    }

    /** @param array<string, mixed> $data */
    private function syncProductRelations(Product $product, array $data): void
    {
        $this->syncTranslations($product, $data['translations']);
        $product->tags()->sync($this->internalIds(ProductTag::class, $data['tag_ids'] ?? []));
        $product->relatedProducts()->sync($this->internalIds(Product::class, $data['related_product_ids'] ?? []));

        $oldMedia = $product->media()->get();
        foreach ($oldMedia as $media) {
            $this->mediaUsage->release($media, 'product', $product->public_id, 'catalog_media');
        }
        $mediaPivot = [];
        foreach ($data['media'] ?? [] as $mediaItem) {
            $mediaId = $this->internalId(Media::class, $mediaItem['media_id']);
            if ($mediaId !== null) {
                $mediaPivot[$mediaId] = Arr::only($mediaItem, ['role', 'locale', 'is_primary', 'sort_order', 'alt_text']);
            }
        }
        $product->media()->sync($mediaPivot);
        foreach ($product->media()->get() as $media) {
            $this->mediaUsage->track($media, 'product', $product->public_id, 'catalog_media');
        }

        $product->attributeValues()->delete();
        foreach ($data['attributes'] ?? [] as $attribute) {
            $product->attributeValues()->create([
                'attribute_definition_id' => $this->internalId(ProductAttributeDefinition::class, $attribute['definition_id']),
                ...Arr::only($attribute, ['locale', 'value_text', 'value_decimal', 'value_boolean', 'value_json']),
            ]);
        }

        $product->specifications()->delete();
        foreach ($data['specifications'] ?? [] as $specification) {
            $product->specifications()->create(Arr::only($specification, ['locale', 'label', 'value', 'unit', 'sort_order']));
        }
    }

    /** @param list<array<string, mixed>> $translations */
    private function syncTranslations(TranslatableModel $model, array $translations): void
    {
        $locales = [];
        foreach ($translations as $translation) {
            $locale = $translation['locale'];
            $locales[] = $locale;
            $model->translations()->updateOrCreate(['locale' => $locale], Arr::except($translation, ['locale']));
        }
        $model->translations()->whereNotIn('locale', $locales)->delete();
    }

    /** @param class-string<Model> $modelClass */
    private function internalId(string $modelClass, mixed $publicId): ?int
    {
        if (! is_string($publicId) || $publicId === '') {
            return null;
        }

        return (int) $modelClass::query()->where('public_id', $publicId)->valueOrFail('id');
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  list<string>  $publicIds
     * @return list<int>
     */
    private function internalIds(string $modelClass, array $publicIds): array
    {
        if ($publicIds === []) {
            return [];
        }

        return $modelClass::query()->whereIn('public_id', $publicIds)->pluck('id')->map(static fn ($id): int => (int) $id)->all();
    }

    private function loadProduct(Product $product): Product
    {
        return $product->fresh([
            'translations',
            'category.translations',
            'category.parent:id,public_id',
            'brand.translations',
            'brand.logo:id,public_id',
            'tags',
            'media',
            'attributeValues.definition',
            'specifications',
            'relatedProducts.translations',
        ]);
    }

    /** @param array<string, mixed> $details */
    private function record(string $action, User $actor, Model $subject, array $details = []): void
    {
        $this->audit->record(
            action: $action,
            actor: $actor,
            subjectType: $subject->getTable(),
            subjectPublicId: (string) $subject->getAttribute('public_id'),
            after: $details,
        );
    }
}
