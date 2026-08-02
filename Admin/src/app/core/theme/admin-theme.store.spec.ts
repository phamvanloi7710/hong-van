import { signal } from '@angular/core';
import { TestBed } from '@angular/core/testing';

import { AdminPreferencesStore } from '../preferences/admin-preferences.store';
import { DEFAULT_ADMIN_THEME_PREFERENCES } from './admin-theme.model';
import { AdminThemeStore } from './admin-theme.store';

class MemoryAdminPreferencesStore {
  private readonly themeState = signal(DEFAULT_ADMIN_THEME_PREFERENCES);
  readonly theme = this.themeState.asReadonly();
  saved = DEFAULT_ADMIN_THEME_PREFERENCES;

  updateTheme(preferences: typeof DEFAULT_ADMIN_THEME_PREFERENCES): void {
    this.saved = preferences;
    this.themeState.set(preferences);
  }
}

describe('AdminThemeStore', () => {
  let preferences: MemoryAdminPreferencesStore;
  let store: AdminThemeStore;

  beforeEach(() => {
    preferences = new MemoryAdminPreferencesStore();
    TestBed.configureTestingModule({
      providers: [
        AdminThemeStore,
        {
          provide: AdminPreferencesStore,
          useValue: preferences,
        },
      ],
    });
    store = TestBed.inject(AdminThemeStore);
  });

  it('should start with the Annular-compatible defaults', () => {
    expect(store.preferences()).toEqual(DEFAULT_ADMIN_THEME_PREFERENCES);
  });

  it('should persist theme changes through the adapter contract', () => {
    store.update({ skin: 'teal-light', menuDensity: 'compact' });

    expect(preferences.saved.skin).toBe('teal-light');
    expect(preferences.saved.menuDensity).toBe('compact');
  });
});
