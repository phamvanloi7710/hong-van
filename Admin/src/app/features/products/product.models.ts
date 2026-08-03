import { MediaPickerItem } from '../../core/media/media-picker.contract';

export type ProductStatus = 'draft' | 'published' | 'archived' | 'scheduled';
export type ProductPriceMode = 'fixed' | 'from' | 'range' | 'market' | 'dealer' | 'quantity' | 'contact';
export type AdminContentLocale = 'vi' | 'en' | 'zh';

export interface ProductTranslation {
  readonly locale: AdminContentLocale;
  readonly name: string;
  readonly slug: string;
  readonly short_description: string | null;
  readonly description: string | null;
  readonly benefits: string | null;
  readonly usage_instructions: string | null;
  readonly meta_title: string | null;
  readonly meta_description: string | null;
}

export interface CategoryTranslation {
  readonly locale: AdminContentLocale;
  readonly name: string;
  readonly slug: string;
  readonly summary: string | null;
  readonly meta_title: string | null;
  readonly meta_description: string | null;
}

export interface BrandTranslation {
  readonly locale: AdminContentLocale;
  readonly name: string;
  readonly slug: string;
  readonly description: string | null;
  readonly meta_title: string | null;
  readonly meta_description: string | null;
}

export interface ProductCategory {
  readonly public_id: string;
  readonly parent_id: string | null;
  readonly code: string;
  readonly is_active: boolean;
  readonly is_featured: boolean;
  readonly sort_order: number;
  readonly translations: readonly CategoryTranslation[];
  readonly products_count?: number;
}

export interface ProductBrand {
  readonly public_id: string;
  readonly code: string;
  readonly logo_media_id: string | null;
  readonly logo_url: string | null;
  readonly is_active: boolean;
  readonly sort_order: number;
  readonly translations: readonly BrandTranslation[];
  readonly products_count?: number;
}

export interface ProductTag {
  readonly public_id: string;
  readonly name: string;
  readonly slug: string;
  readonly products_count?: number;
}

export interface ProductAttribute {
  readonly public_id: string;
  readonly code: string;
  readonly name: string;
  readonly data_type: 'text' | 'decimal' | 'boolean' | 'option' | 'json';
  readonly unit: string | null;
  readonly options: readonly string[] | null;
  readonly is_filterable: boolean;
  readonly is_required: boolean;
  readonly sort_order: number;
  readonly values_count?: number;
}

export interface ProductMedia extends MediaPickerItem {
  readonly media_id: string;
  readonly title: string;
  readonly role: 'primary' | 'gallery' | 'document' | 'certificate';
  readonly locale: '*' | AdminContentLocale;
  readonly is_primary: boolean;
  readonly sort_order: number;
}

export interface ProductPrice {
  readonly mode: ProductPriceMode;
  readonly amount: string | null;
  readonly minimum: string | null;
  readonly maximum: string | null;
  readonly currency: string;
  readonly unit: string | null;
  readonly note: string | null;
  readonly visible: boolean;
  readonly display: {
    readonly mode: ProductPriceMode;
    readonly label: string;
    readonly shows_numeric_price: boolean;
    readonly requires_quote: boolean;
  };
}

export interface ProductAttributeValue {
  readonly definition_id: string;
  readonly definition_name: string;
  readonly locale: '*' | AdminContentLocale;
  readonly value_text: string | null;
  readonly value_decimal: string | null;
  readonly value_boolean: boolean | null;
  readonly value_json: Readonly<Record<string, unknown>> | null;
}

export interface ProductSpecification {
  readonly locale: AdminContentLocale;
  readonly label: string;
  readonly value: string;
  readonly unit: string | null;
  readonly sort_order: number;
}

export interface ProductItem {
  readonly public_id: string;
  readonly sku: string;
  readonly code: string | null;
  readonly status: ProductStatus;
  readonly category: ProductCategory | null;
  readonly brand: ProductBrand | null;
  readonly origin: string | null;
  readonly packaging: string | null;
  readonly is_featured: boolean;
  readonly price: ProductPrice;
  readonly translations: readonly ProductTranslation[];
  readonly media: readonly ProductMedia[];
  readonly tags: readonly ProductTag[];
  readonly attributes: readonly ProductAttributeValue[];
  readonly specifications: readonly ProductSpecification[];
  readonly related_products: readonly { readonly public_id: string; readonly sku: string; readonly translations: readonly Pick<ProductTranslation, 'locale' | 'name' | 'slug'>[] }[];
  readonly published_at: string | null;
  readonly unpublished_at: string | null;
  readonly deleted_at: string | null;
  readonly created_at: string;
  readonly updated_at: string;
}

export interface ProductPagination {
  readonly page: number;
  readonly last_page: number;
  readonly per_page: number;
  readonly total: number;
}

export interface ProductPageResult {
  readonly items: readonly ProductItem[];
  readonly pagination: ProductPagination;
}

export interface ProductFilters {
  readonly search?: string;
  readonly status?: ProductStatus | '';
  readonly category_id?: string;
  readonly brand_id?: string;
  readonly price_mode?: ProductPriceMode | '';
  readonly featured?: boolean;
}

export interface ProductPayload {
  readonly sku: string;
  readonly code: string | null;
  readonly status: ProductStatus;
  readonly category_id: string | null;
  readonly brand_id: string | null;
  readonly origin: string | null;
  readonly packaging: string | null;
  readonly is_featured: boolean;
  readonly published_at: string | null;
  readonly unpublished_at: string | null;
  readonly price: Omit<ProductPrice, 'display'>;
  readonly translations: readonly ProductTranslation[];
  readonly media: readonly Pick<ProductMedia, 'media_id' | 'role' | 'locale' | 'is_primary' | 'sort_order' | 'alt_text'>[];
  readonly tag_ids: readonly string[];
  readonly attributes: readonly Omit<ProductAttributeValue, 'definition_name'>[];
  readonly specifications: readonly ProductSpecification[];
  readonly related_product_ids: readonly string[];
}

export interface ProductEditorData {
  readonly product: ProductItem | null;
  readonly categories: readonly ProductCategory[];
  readonly brands: readonly ProductBrand[];
  readonly tags: readonly ProductTag[];
  readonly attributes: readonly ProductAttribute[];
  readonly products: readonly ProductItem[];
}

export type CatalogEntityType = 'category' | 'brand' | 'tag' | 'attribute';

export interface CatalogEntityDialogData {
  readonly type: CatalogEntityType;
  readonly item: ProductCategory | ProductBrand | ProductTag | ProductAttribute | null;
  readonly categories: readonly ProductCategory[];
}

export interface CatalogEntityDialogResult {
  readonly publicId: string | null;
  readonly payload: unknown;
}
