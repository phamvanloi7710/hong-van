export type SeoEntityType = 'product' | 'crop_solution' | 'service' | 'post' | 'project' | 'gallery' | 'certification' | 'vehicle' | 'transport_route' | 'transport_service_area' | 'warehouse';
export type SeoLocale = 'vi' | 'en' | 'zh';

export interface SeoEntityOption {
  readonly public_id: string;
  readonly label: string;
  readonly code: string | null;
  readonly status: string | null;
}

export interface SeoImage {
  readonly public_id: string;
  readonly original_filename: string;
  readonly mime_type: string;
  readonly width: number | null;
  readonly height: number | null;
  readonly alt_text: string | null;
  readonly content_url: string;
  readonly variants: readonly { readonly key: string; readonly width: number | null; readonly height: number | null; readonly content_url: string }[];
}

export interface SeoMetaRecord {
  readonly public_id: string | null;
  readonly locale: SeoLocale;
  readonly meta_title: string | null;
  readonly meta_description: string | null;
  readonly canonical_url: string | null;
  readonly robots_index: boolean;
  readonly robots_follow: boolean;
  readonly og_title: string | null;
  readonly og_description: string | null;
  readonly og_image: SeoImage | null;
  readonly og_type: 'website' | 'article' | 'product';
  readonly twitter_card: 'summary' | 'summary_large_image';
  readonly twitter_title: string | null;
  readonly twitter_description: string | null;
  readonly focus_keywords: readonly string[];
  readonly updated_at: string | null;
}

export interface SeoMetaPayload {
  readonly locale: SeoLocale;
  readonly meta_title: string | null;
  readonly meta_description: string | null;
  readonly canonical_url: string | null;
  readonly robots_index: boolean;
  readonly robots_follow: boolean;
  readonly og_title: string | null;
  readonly og_description: string | null;
  readonly og_image_media_id: string | null;
  readonly og_type: 'website' | 'article' | 'product';
  readonly twitter_card: 'summary' | 'summary_large_image';
  readonly twitter_title: string | null;
  readonly twitter_description: string | null;
  readonly focus_keywords: readonly string[];
}
