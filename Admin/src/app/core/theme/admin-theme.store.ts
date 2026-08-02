import { computed, inject, Injectable } from '@angular/core';

import { AdminPreferencesStore } from '../preferences/admin-preferences.store';
import { AdminThemePreferences, DEFAULT_ADMIN_THEME_PREFERENCES } from './admin-theme.model';

@Injectable({ providedIn: 'root' })
export class AdminThemeStore {
  private readonly preferenceStore = inject(AdminPreferencesStore);

  readonly preferences = this.preferenceStore.theme;
  readonly isDark = computed(() => this.preferences().skin.endsWith('-dark'));

  update(patch: Partial<AdminThemePreferences>): void {
    this.preferenceStore.updateTheme({ ...this.preferences(), ...patch });
  }

  reset(): void {
    this.preferenceStore.updateTheme(DEFAULT_ADMIN_THEME_PREFERENCES);
  }
}
