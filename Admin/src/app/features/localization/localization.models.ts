export interface AdminLanguage {
  readonly public_id: string;
  readonly locale: string;
  readonly name: string;
  readonly native_name: string;
  readonly is_active: boolean;
  readonly is_default: boolean;
  readonly fallback_locale: string | null;
  readonly sort_order: number;
  readonly updated_at: string | null;
}

export interface MissingTranslationLanguage {
  readonly locale: string;
  readonly missing_count: number;
  readonly missing_keys: readonly string[];
}

export interface MissingTranslationReport {
  readonly total_keys: number;
  readonly languages: readonly MissingTranslationLanguage[];
}

export interface LocalizationPayload {
  readonly languages: readonly AdminLanguage[];
  readonly missing_translations: MissingTranslationReport;
  readonly storage_timezone: string;
  readonly display_timezone: string;
  readonly generated_at: string;
}

export interface UpdateLanguagePayload {
  readonly is_active?: boolean;
  readonly is_default?: boolean;
  readonly fallback_locale?: string | null;
  readonly sort_order?: number;
}
