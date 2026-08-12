import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../auth/auth.models';
import { AdminPreferencesDto } from './admin-preferences.model';
import { AdminPreferencesStore } from './admin-preferences.store';

const preferences: AdminPreferencesDto = {
  theme: {
    fixed_header: true,
    fixed_sidenav: true,
    fixed_footer: false,
    sidenav_opened: true,
    sidenav_pinned: true,
    menu_orientation: 'vertical',
    menu_density: 'default',
    skin: 'teal-light',
    rtl: false,
  },
  locale: 'en',
  favorite_menu_ids: ['dashboard'],
};

function envelope(data: AdminPreferencesDto): ApiEnvelope<AdminPreferencesDto> {
  return {
    success: true,
    data,
    meta: { request_id: '01JREQUEST0000000000000000' },
    message: null,
  };
}

describe('AdminPreferencesStore', () => {
  let store: AdminPreferencesStore;
  let httpTesting: HttpTestingController;

  beforeEach(() => {
    localStorage.clear();
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    store = TestBed.inject(AdminPreferencesStore);
    httpTesting = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpTesting.verify());

  it('loads the server source of truth for the active user', () => {
    store.initialize('01JUSER').subscribe();
    httpTesting.expectOne('/api/admin/v1/preferences').flush(envelope(preferences));

    expect(store.theme().skin).toBe('teal-light');
    expect(store.locale()).toBe('en');
    expect(store.favoriteMenuIds()).toEqual(['dashboard']);
  });

  it('stores ordered favorite menu ids selected from the template picker', () => {
    store.initialize('01JUSER').subscribe();
    httpTesting.expectOne('/api/admin/v1/preferences').flush(envelope(preferences));

    store.toggleFavorite('identity');

    const request = httpTesting.expectOne('/api/admin/v1/preferences');
    expect(request.request.method).toBe('PUT');
    expect(request.request.body).toEqual({ favorite_menu_ids: ['dashboard', 'identity'] });
    request.flush(envelope({ ...preferences, favorite_menu_ids: ['dashboard', 'identity'] }));
    expect(store.favoriteMenuIds()).toEqual(['dashboard', 'identity']);
  });

  it('serializes rapid preference writes so the latest favorite selection wins', () => {
    store.initialize('01JUSER').subscribe();
    httpTesting.expectOne('/api/admin/v1/preferences').flush(envelope(preferences));

    store.updateFavoriteMenuIds(['dashboard', 'identity']);
    store.updateFavoriteMenuIds([]);

    const firstRequest = httpTesting.expectOne('/api/admin/v1/preferences');
    expect(firstRequest.request.body).toEqual({
      favorite_menu_ids: ['dashboard', 'identity'],
    });
    httpTesting.expectNone('/api/admin/v1/preferences');
    firstRequest.flush(envelope({ ...preferences, favorite_menu_ids: ['dashboard', 'identity'] }));

    const latestRequest = httpTesting.expectOne('/api/admin/v1/preferences');
    expect(latestRequest.request.body).toEqual({ favorite_menu_ids: [] });
    latestRequest.flush(envelope({ ...preferences, favorite_menu_ids: [] }));

    expect(store.favoriteMenuIds()).toEqual([]);
  });

  it('serializes reset with pending writes and keeps the latest local change', () => {
    store.initialize('01JUSER').subscribe();
    httpTesting.expectOne('/api/admin/v1/preferences').flush(envelope(preferences));

    store.updateFavoriteMenuIds(['dashboard', 'page-builder']);
    store.reset();
    store.updateLocale('zh');

    expect(store.favoriteMenuIds()).toEqual(['dashboard', 'page-builder']);
    expect(store.locale()).toBe('zh');

    const favoriteRequest = httpTesting.expectOne('/api/admin/v1/preferences');
    expect(favoriteRequest.request.method).toBe('PUT');
    favoriteRequest.flush(
      envelope({ ...preferences, favorite_menu_ids: ['dashboard', 'page-builder'] }),
    );

    const resetRequest = httpTesting.expectOne('/api/admin/v1/preferences');
    expect(resetRequest.request.method).toBe('DELETE');
    resetRequest.flush(
      envelope({
        ...preferences,
        theme: { ...preferences.theme, skin: 'indigo-light' },
        locale: 'vi',
        favorite_menu_ids: [],
      }),
    );
    expect(store.locale()).toBe('zh');

    const localeRequest = httpTesting.expectOne('/api/admin/v1/preferences');
    expect(localeRequest.request.method).toBe('PUT');
    expect(localeRequest.request.body).toEqual({ locale: 'zh' });
    localeRequest.flush(
      envelope({
        ...preferences,
        theme: { ...preferences.theme, skin: 'indigo-light' },
        locale: 'zh',
        favorite_menu_ids: [],
      }),
    );

    expect(store.locale()).toBe('zh');
    expect(store.favoriteMenuIds()).toEqual([]);
    expect(store.theme().skin).toBe('indigo-light');
  });

  it('does not apply a completed write after the active user is cleared', () => {
    store.initialize('01JUSER').subscribe();
    httpTesting.expectOne('/api/admin/v1/preferences').flush(envelope(preferences));

    store.updateLocale('zh');
    const request = httpTesting.expectOne('/api/admin/v1/preferences');

    store.clear();
    request.flush(envelope({ ...preferences, locale: 'zh' }));

    expect(store.locale()).toBe('vi');
    expect(store.favoriteMenuIds()).toEqual([]);
  });
});
