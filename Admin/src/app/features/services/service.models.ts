export type ServiceLocale = 'vi' | 'en' | 'zh';
export type ServiceStatus = 'draft' | 'published' | 'scheduled' | 'archived';
export type ServiceType = 'general' | 'transportation_link' | 'warehouse_link';
export type ServiceCtaType = 'none' | 'contact' | 'quote';

export interface ServiceCategoryTranslation {
  readonly locale: ServiceLocale;
  readonly name: string;
  readonly slug: string;
  readonly summary: string | null;
  readonly meta_title: string | null;
  readonly meta_description: string | null;
}

export interface ServiceCategoryItem {
  readonly public_id: string;
  readonly parent_id: string | null;
  readonly code: string;
  readonly is_active: boolean;
  readonly sort_order: number;
  readonly translations: readonly ServiceCategoryTranslation[];
  readonly services_count: number;
}

export interface ServiceTranslation extends ServiceCategoryTranslation {
  readonly content: string | null;
  readonly content_sections: readonly { readonly title: string; readonly body: string }[];
  readonly cta_label: string | null;
}

export interface ServiceMediaItem {
  readonly public_id: string;
  readonly file_name: string;
  readonly mime_type: string;
  readonly role: 'hero' | 'gallery' | 'document';
  readonly sort_order: number;
}

export interface ServiceItem {
  readonly public_id: string;
  readonly category: { readonly public_id: string; readonly translations: readonly Pick<ServiceCategoryTranslation, 'locale' | 'name' | 'slug'>[] } | null;
  readonly code: string;
  readonly service_type: ServiceType;
  readonly specialized_module: 'transportation' | 'warehouses' | null;
  readonly status: ServiceStatus;
  readonly cta_type: ServiceCtaType;
  readonly is_featured: boolean;
  readonly sort_order: number;
  readonly translations: readonly ServiceTranslation[];
  readonly media: readonly ServiceMediaItem[];
  readonly published_at: string | null;
  readonly unpublished_at: string | null;
  readonly deleted_at: string | null;
  readonly updated_at: string;
}

export interface ServicePagination {
  readonly page: number;
  readonly last_page: number;
  readonly per_page: number;
  readonly total: number;
}

export interface ServicePageResult {
  readonly items: readonly ServiceItem[];
  readonly pagination: ServicePagination;
}

export interface ServiceFilters {
  readonly search?: string;
  readonly status?: ServiceStatus | '';
  readonly service_type?: ServiceType | '';
  readonly category_id?: string;
  readonly trashed?: 'without' | 'with' | 'only';
}

export interface ServiceDialogData {
  readonly service: ServiceItem | null;
  readonly categories: readonly ServiceCategoryItem[];
}

export interface ServiceCategoryDialogData {
  readonly category: ServiceCategoryItem | null;
  readonly categories: readonly ServiceCategoryItem[];
}

export interface ServiceDialogResult {
  readonly publicId: string | null;
  readonly payload: unknown;
}
