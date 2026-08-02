import { Injectable, signal } from '@angular/core';

import {
  EN_TRANSLATIONS,
  TranslationKey,
  VI_TRANSLATIONS,
  ZH_TRANSLATIONS,
} from './translation-catalog';

export type AdminLocale = 'vi' | 'en' | 'zh';

export interface AdminLocaleOption {
  readonly id: AdminLocale;
  readonly labelKey: TranslationKey;
  readonly shortLabel: string;
}

const CATALOGS: Record<AdminLocale, Record<TranslationKey, string>> = {
  vi: VI_TRANSLATIONS,
  en: EN_TRANSLATIONS,
  zh: ZH_TRANSLATIONS,
};

export const ADMIN_LOCALES: readonly AdminLocaleOption[] = [
  { id: 'vi', labelKey: 'language.vi', shortLabel: 'VI' },
  { id: 'en', labelKey: 'language.en', shortLabel: 'EN' },
  { id: 'zh', labelKey: 'language.zh', shortLabel: '中文' },
];

@Injectable({ providedIn: 'root' })
export class I18nService {
  private readonly localeState = signal<AdminLocale>(loadGuestLocale());

  readonly locale = this.localeState.asReadonly();

  constructor() {
    document.documentElement.lang = this.localeState();
  }

  setLocale(locale: AdminLocale): void {
    this.localeState.set(locale);
    document.documentElement.lang = locale;

    try {
      localStorage.setItem('hongvan.admin.guest-locale', locale);
    } catch {
      // Language switching remains available when local storage is unavailable.
    }
  }

  t(key: TranslationKey, parameters: Readonly<Record<string, string | number>> = {}): string {
    const translation = CATALOGS[this.localeState()][key] ?? VI_TRANSLATIONS[key];

    return Object.entries(parameters).reduce(
      (result, [name, value]) => result.replaceAll(`{${name}}`, String(value)),
      translation,
    );
  }
}

function loadGuestLocale(): AdminLocale {
  try {
    const locale = localStorage.getItem('hongvan.admin.guest-locale');

    return locale === 'en' || locale === 'zh' ? locale : 'vi';
  } catch {
    return 'vi';
  }
}
