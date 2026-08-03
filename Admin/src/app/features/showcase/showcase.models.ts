export type ShowcaseLocale = 'vi' | 'en' | 'zh';
export type ShowcaseKind = 'galleries' | 'gallery-items' | 'partners' | 'certifications' | 'projects';
export type ShowcaseStatus = 'draft' | 'published' | 'archived';

export interface ShowcaseTranslation {
  readonly locale: ShowcaseLocale;
  readonly name?: string | null; readonly title?: string | null; readonly slug?: string | null;
  readonly description?: string | null; readonly summary?: string | null; readonly content?: string | null;
  readonly location?: string | null; readonly issuer?: string | null; readonly alt_text?: string | null;
  readonly logo_alt?: string | null; readonly image_alt?: string | null; readonly caption?: string | null;
  readonly document_label?: string | null; readonly meta_title?: string | null; readonly meta_description?: string | null;
}

export interface ShowcaseMedia { readonly public_id: string; readonly file_name: string; readonly mime_type: string; }
export interface ProjectMediaItem { readonly public_id: string; readonly role: 'cover' | 'gallery' | 'document'; readonly sort_order: number; readonly media: ShowcaseMedia; readonly translations: readonly ShowcaseTranslation[]; }

export interface ShowcaseItem {
  readonly public_id: string; readonly code: string | null; readonly status: ShowcaseStatus;
  readonly is_featured: boolean; readonly sort_order: number; readonly translations: readonly ShowcaseTranslation[];
  readonly published_at: string | null; readonly deleted_at: string | null; readonly updated_at: string;
  readonly gallery_id?: string; readonly items_count?: number; readonly media?: ShowcaseMedia | null;
  readonly website_url?: string | null; readonly logo_media?: ShowcaseMedia | null;
  readonly document_visibility?: 'private' | 'public'; readonly issued_on?: string | null; readonly expires_on?: string | null;
  readonly image_media?: ShowcaseMedia | null; readonly document_media?: ShowcaseMedia | null;
  readonly started_on?: string | null; readonly completed_on?: string | null; readonly media_items?: readonly ProjectMediaItem[];
}

export interface ShowcaseFilters { readonly search?: string; readonly status?: ShowcaseStatus | ''; readonly trashed?: 'without' | 'with' | 'only'; readonly gallery_id?: string; }
export interface ShowcasePageResult { readonly items: readonly ShowcaseItem[]; readonly total: number; }
export interface ShowcaseDialogData { readonly kind: ShowcaseKind; readonly item: ShowcaseItem | null; readonly galleries: readonly ShowcaseItem[]; }
