import { TestBed } from '@angular/core/testing';

import {
  AdminThemePreferencesAdapter,
  ADMIN_THEME_PREFERENCES_ADAPTER,
} from './admin-theme-preferences.adapter';
import { DEFAULT_ADMIN_THEME_PREFERENCES } from './admin-theme.model';
import { AdminThemeStore } from './admin-theme.store';

class MemoryThemePreferencesAdapter implements AdminThemePreferencesAdapter {
  saved = DEFAULT_ADMIN_THEME_PREFERENCES;

  load() {
    return null;
  }

  save(preferences: typeof DEFAULT_ADMIN_THEME_PREFERENCES): void {
    this.saved = preferences;
  }
}

describe('AdminThemeStore', () => {
  let adapter: MemoryThemePreferencesAdapter;
  let store: AdminThemeStore;

  beforeEach(() => {
    adapter = new MemoryThemePreferencesAdapter();
    TestBed.configureTestingModule({
      providers: [
        AdminThemeStore,
        {
          provide: ADMIN_THEME_PREFERENCES_ADAPTER,
          useValue: adapter,
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

    expect(adapter.saved.skin).toBe('teal-light');
    expect(adapter.saved.menuDensity).toBe('compact');
  });
});
