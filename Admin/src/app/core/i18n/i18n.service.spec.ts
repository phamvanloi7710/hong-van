import { TestBed } from '@angular/core/testing';

import { I18nService } from './i18n.service';

describe('I18nService', () => {
  it('switches the complete core catalog between Vietnamese, English and Chinese', () => {
    const service = TestBed.inject(I18nService);

    service.setLocale('vi');
    expect(service.t('menu.dashboard')).toBe('Tổng quan');

    service.setLocale('en');
    expect(service.t('menu.dashboard')).toBe('Dashboard');

    service.setLocale('zh');
    expect(service.t('menu.dashboard')).toBe('总览');
    expect(document.documentElement.lang).toBe('zh');
  });

  it('interpolates safe display parameters', () => {
    const service = TestBed.inject(I18nService);
    service.setLocale('en');

    expect(service.t('identity.confirmDeleteUser', { name: 'Alice' })).toBe('Delete user Alice?');
  });
});
