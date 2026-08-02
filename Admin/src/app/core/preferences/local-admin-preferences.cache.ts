import { Injectable } from '@angular/core';

import { AdminPreferencesDto } from './admin-preferences.model';
import {
  ADMIN_MENU_DENSITIES,
  ADMIN_MENU_ORIENTATIONS,
  ADMIN_SKINS,
} from '../theme/admin-theme.model';

const STORAGE_PREFIX = 'hongvan.admin.preferences.v1.';

@Injectable({ providedIn: 'root' })
export class LocalAdminPreferencesCache {
  load(userPublicId: string): AdminPreferencesDto | null {
    try {
      const value = localStorage.getItem(STORAGE_PREFIX + userPublicId);
      const parsed: unknown = value === null ? null : JSON.parse(value);

      return isPreferencesDto(parsed) ? parsed : null;
    } catch {
      return null;
    }
  }

  save(userPublicId: string, preferences: AdminPreferencesDto): void {
    try {
      localStorage.setItem(STORAGE_PREFIX + userPublicId, JSON.stringify(preferences));
    } catch {
      // Server preferences remain the source of truth when local storage is unavailable.
    }
  }

  remove(userPublicId: string): void {
    try {
      localStorage.removeItem(STORAGE_PREFIX + userPublicId);
    } catch {
      // Ignore unavailable local storage during logout.
    }
  }
}

function isPreferencesDto(value: unknown): value is AdminPreferencesDto {
  if (!isRecord(value) || !isRecord(value['theme'])) {
    return false;
  }

  const theme = value['theme'];

  return (
    ['vi', 'en', 'zh'].includes(String(value['locale'])) &&
    Array.isArray(value['favorite_menu_ids']) &&
    value['favorite_menu_ids'].every((id) => typeof id === 'string') &&
    ['fixed_header', 'fixed_sidenav', 'fixed_footer', 'sidenav_opened', 'sidenav_pinned', 'rtl'].every(
      (key) => typeof theme[key] === 'boolean',
    ) &&
    ADMIN_MENU_ORIENTATIONS.includes(
      theme['menu_orientation'] as (typeof ADMIN_MENU_ORIENTATIONS)[number],
    ) &&
    ADMIN_MENU_DENSITIES.includes(
      theme['menu_density'] as (typeof ADMIN_MENU_DENSITIES)[number],
    ) &&
    ADMIN_SKINS.some((skin) => skin.id === theme['skin'])
  );
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null;
}
