import { SeoLocale } from './seo.models';

export interface RedirectRule {
  readonly public_id: string;
  readonly source_path: string;
  readonly locale: SeoLocale | '*';
  readonly target_path: string | null;
  readonly status_code: 301 | 302 | 410;
  readonly is_active: boolean;
  readonly hit_count: number;
  readonly last_hit_at: string | null;
  readonly note: string | null;
}

export interface RedirectPayload {
  readonly source_path: string;
  readonly locale: SeoLocale | '*';
  readonly target_path: string | null;
  readonly status_code: 301 | 302 | 410;
  readonly is_active: boolean;
  readonly note: string | null;
}

export interface SitemapHealth {
  readonly generated_at: string | null;
  readonly shard_count: number;
  readonly url_count: number;
  readonly sitemap_url: string;
  readonly robots_url: string;
  readonly public_indexing_enabled: boolean;
  readonly disallow_paths: string;
}

export interface SchemaPreview {
  readonly type: 'organization' | 'local_business' | 'website';
  readonly locale: SeoLocale;
  readonly schema: Readonly<Record<string, unknown>> | null;
  readonly json: string | null;
}
