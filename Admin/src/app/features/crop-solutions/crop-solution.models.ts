export type CropLocale = 'vi' | 'en' | 'zh';
export type CropSolutionStatus = 'draft' | 'published' | 'scheduled' | 'archived';

export interface CropCategoryTranslation {
  readonly locale: CropLocale;
  readonly name: string;
  readonly slug: string;
  readonly summary: string | null;
  readonly meta_title: string | null;
  readonly meta_description: string | null;
}

export interface CropCategoryItem {
  readonly public_id: string;
  readonly parent_id: string | null;
  readonly code: string;
  readonly image_media_id: string | null;
  readonly is_active: boolean;
  readonly sort_order: number;
  readonly translations: readonly CropCategoryTranslation[];
  readonly crops_count?: number;
}

export interface CropTranslation extends CropCategoryTranslation {
  readonly description: string | null;
}

export interface CropItem {
  readonly public_id: string;
  readonly category_id: string | null;
  readonly code: string;
  readonly image_media_id: string | null;
  readonly is_active: boolean;
  readonly sort_order: number;
  readonly translations: readonly CropTranslation[];
  readonly stages: readonly CropStageItem[];
  readonly stages_count?: number;
  readonly solutions_count?: number;
}

export interface CropStageTranslation {
  readonly locale: CropLocale;
  readonly name: string;
  readonly summary: string | null;
  readonly content: string | null;
}

export interface CropStageItem {
  readonly public_id: string;
  readonly crop_id: string;
  readonly code: string;
  readonly image_media_id: string | null;
  readonly is_active: boolean;
  readonly sort_order: number;
  readonly translations: readonly CropStageTranslation[];
  readonly solutions_count?: number;
}

export interface CropSolutionTranslation {
  readonly locale: CropLocale;
  readonly title: string;
  readonly slug: string;
  readonly summary: string | null;
  readonly content: string | null;
  readonly content_sections: readonly { readonly title: string; readonly body: string }[];
  readonly meta_title: string | null;
  readonly meta_description: string | null;
}

export interface CropSolutionProduct {
  readonly public_id: string;
  readonly sku: string;
  readonly status: string;
  readonly deleted_at: string | null;
  readonly sort_order: number;
  readonly recommendation_note: string | null;
  readonly translations: readonly { readonly locale: CropLocale; readonly name: string; readonly slug: string }[];
}

export interface CropSolutionItem {
  readonly public_id: string;
  readonly crop: { readonly public_id: string; readonly translations: readonly { readonly locale: CropLocale; readonly name: string; readonly slug: string }[] };
  readonly stage: { readonly public_id: string; readonly translations: readonly { readonly locale: CropLocale; readonly name: string }[] } | null;
  readonly code: string;
  readonly status: CropSolutionStatus;
  readonly hero_media_id: string | null;
  readonly is_featured: boolean;
  readonly sort_order: number;
  readonly translations: readonly CropSolutionTranslation[];
  readonly products: readonly CropSolutionProduct[];
  readonly published_at: string | null;
  readonly unpublished_at: string | null;
  readonly deleted_at: string | null;
  readonly updated_at: string;
}

export interface CropProductOption {
  readonly public_id: string;
  readonly sku: string;
  readonly status: string;
  readonly translations: readonly { readonly locale: CropLocale; readonly name: string; readonly slug: string }[];
}

export interface CropPagination {
  readonly page: number;
  readonly last_page: number;
  readonly per_page: number;
  readonly total: number;
}

export interface CropSolutionPageResult {
  readonly items: readonly CropSolutionItem[];
  readonly pagination: CropPagination;
}

export interface CropSolutionFilters {
  readonly search?: string;
  readonly status?: CropSolutionStatus | '';
  readonly crop_id?: string;
  readonly stage_id?: string;
}

export interface CropReferenceDialogData {
  readonly type: 'category' | 'crop' | 'stage';
  readonly item: CropCategoryItem | CropItem | CropStageItem | null;
  readonly categories: readonly CropCategoryItem[];
  readonly crops: readonly CropItem[];
}

export interface CropDialogResult {
  readonly publicId: string | null;
  readonly payload: unknown;
}

export interface CropSolutionDialogData {
  readonly solution: CropSolutionItem | null;
  readonly crops: readonly CropItem[];
  readonly stages: readonly CropStageItem[];
  readonly products: readonly CropProductOption[];
}
