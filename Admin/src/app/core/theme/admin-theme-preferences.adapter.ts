import { inject, InjectionToken } from '@angular/core';

import { AdminThemePreferences } from './admin-theme.model';
import { LocalAdminThemePreferencesAdapter } from './local-admin-theme-preferences.adapter';

export interface AdminThemePreferencesAdapter {
  load(): AdminThemePreferences | null;
  save(preferences: AdminThemePreferences): void;
}

export const ADMIN_THEME_PREFERENCES_ADAPTER = new InjectionToken<AdminThemePreferencesAdapter>(
  'ADMIN_THEME_PREFERENCES_ADAPTER',
  {
    providedIn: 'root',
    factory: () => inject(LocalAdminThemePreferencesAdapter),
  },
);
