import { computed, inject, Injectable, signal } from '@angular/core';

import { ADMIN_THEME_PREFERENCES_ADAPTER } from './admin-theme-preferences.adapter';
import { AdminThemePreferences, DEFAULT_ADMIN_THEME_PREFERENCES } from './admin-theme.model';

@Injectable({ providedIn: 'root' })
export class AdminThemeStore {
  private readonly adapter = inject(ADMIN_THEME_PREFERENCES_ADAPTER);
  private readonly preferencesState = signal(
    this.adapter.load() ?? DEFAULT_ADMIN_THEME_PREFERENCES,
  );

  readonly preferences = this.preferencesState.asReadonly();
  readonly isDark = computed(() => this.preferences().skin.endsWith('-dark'));

  update(patch: Partial<AdminThemePreferences>): void {
    const preferences = { ...this.preferencesState(), ...patch };
    this.preferencesState.set(preferences);
    this.adapter.save(preferences);
  }

  reset(): void {
    this.preferencesState.set(DEFAULT_ADMIN_THEME_PREFERENCES);
    this.adapter.save(DEFAULT_ADMIN_THEME_PREFERENCES);
  }
}
