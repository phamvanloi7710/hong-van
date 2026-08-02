import { AdminLocale } from '../i18n/i18n.service';
import {
  AdminThemePreferences,
  DEFAULT_ADMIN_THEME_PREFERENCES,
} from '../theme/admin-theme.model';

export interface AdminPreferences {
  readonly theme: AdminThemePreferences;
  readonly locale: AdminLocale;
  readonly favoriteMenuIds: readonly string[];
}

export const DEFAULT_ADMIN_PREFERENCES: AdminPreferences = {
  theme: DEFAULT_ADMIN_THEME_PREFERENCES,
  locale: 'vi',
  favoriteMenuIds: [],
};

export interface AdminThemePreferencesDto {
  readonly fixed_header: boolean;
  readonly fixed_sidenav: boolean;
  readonly fixed_footer: boolean;
  readonly sidenav_opened: boolean;
  readonly sidenav_pinned: boolean;
  readonly menu_orientation: AdminThemePreferences['menuOrientation'];
  readonly menu_density: AdminThemePreferences['menuDensity'];
  readonly skin: AdminThemePreferences['skin'];
  readonly rtl: boolean;
}

export interface AdminPreferencesDto {
  readonly theme: AdminThemePreferencesDto;
  readonly locale: AdminLocale;
  readonly favorite_menu_ids: readonly string[];
}

export type AdminPreferencesUpdateDto = Partial<AdminPreferencesDto>;
