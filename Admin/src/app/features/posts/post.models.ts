export type PostLocale = 'vi' | 'en' | 'zh';
export type PostStatus = 'draft' | 'scheduled' | 'published' | 'archived';

export interface PostAuthor { readonly public_id: string; readonly name: string; readonly email: string; }

export interface PostTranslation {
  readonly locale: PostLocale;
  readonly title: string;
  readonly slug: string;
  readonly excerpt: string | null;
  readonly content_html: string;
  readonly meta_title: string | null;
  readonly meta_description: string | null;
}

export interface PostTaxonomyTranslation {
  readonly locale: PostLocale;
  readonly name: string;
  readonly slug: string;
  readonly description?: string | null;
  readonly meta_title?: string | null;
  readonly meta_description?: string | null;
}

export interface PostTaxonomyItem {
  readonly public_id: string;
  readonly parent_id: string | null;
  readonly code: string;
  readonly is_active: boolean;
  readonly sort_order: number;
  readonly translations: readonly PostTaxonomyTranslation[];
  readonly posts_count: number;
}

export interface PostItem {
  readonly public_id: string;
  readonly code: string;
  readonly status: PostStatus;
  readonly is_featured: boolean;
  readonly category: { readonly public_id: string; readonly translations: readonly PostTaxonomyTranslation[] } | null;
  readonly author: { readonly public_id: string; readonly name: string; readonly email: string } | null;
  readonly featured_media: { readonly public_id: string; readonly file_name: string; readonly mime_type: string } | null;
  readonly tags: readonly { readonly public_id: string; readonly translations: readonly PostTaxonomyTranslation[] }[];
  readonly translations: readonly PostTranslation[];
  readonly scheduled_for: string | null;
  readonly published_at: string | null;
  readonly unpublished_at: string | null;
  readonly deleted_at: string | null;
  readonly updated_at: string;
}

export interface PostPagination { readonly page: number; readonly last_page: number; readonly per_page: number; readonly total: number; }
export interface PostPageResult { readonly items: readonly PostItem[]; readonly pagination: PostPagination; }
export interface PostFilters { readonly search?: string; readonly status?: PostStatus | ''; readonly category_id?: string; readonly trashed?: 'without' | 'only'; }
export interface PostDialogData { readonly post: PostItem | null; readonly authors: readonly PostAuthor[]; readonly categories: readonly PostTaxonomyItem[]; readonly tags: readonly PostTaxonomyItem[]; }
export interface PostTaxonomyDialogData { readonly kind: 'category' | 'tag'; readonly item: PostTaxonomyItem | null; readonly categories: readonly PostTaxonomyItem[]; }
export interface PostDialogResult { readonly publicId: string | null; readonly payload: unknown; }
