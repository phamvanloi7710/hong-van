import { TestBed } from '@angular/core/testing';

import { I18nService } from './i18n.service';
import { EN_TRANSLATIONS, VI_TRANSLATIONS, ZH_TRANSLATIONS } from './translation-catalog';

describe('I18nService', () => {
  beforeEach(() => localStorage.clear());

  afterEach(() => localStorage.clear());

  it('keeps complete, non-empty and matching Vietnamese, English and Chinese catalogs', () => {
    const vietnameseKeys = Object.keys(VI_TRANSLATIONS).sort();

    expect(Object.keys(EN_TRANSLATIONS).sort()).toEqual(vietnameseKeys);
    expect(Object.keys(ZH_TRANSLATIONS).sort()).toEqual(vietnameseKeys);

    for (const catalog of [VI_TRANSLATIONS, EN_TRANSLATIONS, ZH_TRANSLATIONS]) {
      expect(Object.values(catalog).every((translation) => translation.trim().length > 0)).toBe(
        true,
      );
    }
  });

  it('falls back deterministically to Vietnamese for an unsupported guest locale', () => {
    localStorage.setItem('hongvan.admin.guest-locale', 'fr');

    const service = TestBed.inject(I18nService);

    expect(service.locale()).toBe('vi');
    expect(service.t('menu.dashboard')).toBe('Tổng quan');
    expect(document.documentElement.lang).toBe('vi');
  });

  it('switches the complete core catalog between Vietnamese, English and Chinese', () => {
    const service = TestBed.inject(I18nService);

    service.setLocale('vi');
    expect(service.t('menu.dashboard')).toBe('Tổng quan');
    expect(service.t('language.en')).toBe('Tiếng Anh');

    service.setLocale('en');
    expect(service.t('menu.dashboard')).toBe('Dashboard');
    expect(service.t('language.vi')).toBe('Vietnamese');

    service.setLocale('zh');
    expect(service.t('menu.dashboard')).toBe('总览');
    expect(service.t('language.en')).toBe('英语');
    expect(document.documentElement.lang).toBe('zh');
  });

  it('interpolates safe display parameters', () => {
    const service = TestBed.inject(I18nService);
    service.setLocale('en');

    expect(service.t('identity.confirmDeleteUser', { name: 'Alice' })).toBe('Delete user Alice?');
  });
});
