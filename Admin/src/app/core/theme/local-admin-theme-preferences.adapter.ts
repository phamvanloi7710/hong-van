import { Injectable } from '@angular/core';

import { AdminThemePreferencesAdapter } from './admin-theme-preferences.adapter';
import {
  ADMIN_MENU_DENSITIES,
  ADMIN_MENU_ORIENTATIONS,
  ADMIN_SKINS,
  AdminMenuDensity,
  AdminMenuOrientation,
  AdminSkin,
  AdminThemePreferences,
} from './admin-theme.model';

const STORAGE_KEY = 'hongvan.admin.theme-settings.v1';

@Injectable({ providedIn: 'root' })
export class LocalAdminThemePreferencesAdapter implements AdminThemePreferencesAdapter {
  load(): AdminThemePreferences | null {
    try {
      const value = localStorage.getItem(STORAGE_KEY);

      if (value === null) {
        return null;
      }

      const parsed: unknown = JSON.parse(value);

      return isAdminThemePreferences(parsed) ? parsed : null;
    } catch {
      return null;
    }
  }

  save(preferences: AdminThemePreferences): void {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(preferences));
    } catch {
      // The in-memory settings remain usable when storage is unavailable.
    }
  }
}

function isAdminThemePreferences(value: unknown): value is AdminThemePreferences {
  if (!isRecord(value)) {
    return false;
  }

  return (
    isBoolean(value['fixedHeader']) &&
    isBoolean(value['fixedSidenav']) &&
    isBoolean(value['fixedFooter']) &&
    isBoolean(value['sidenavOpened']) &&
    isBoolean(value['sidenavPinned']) &&
    isMenuOrientation(value['menuOrientation']) &&
    isMenuDensity(value['menuDensity']) &&
    isSkin(value['skin']) &&
    isBoolean(value['rtl'])
  );
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null;
}

function isBoolean(value: unknown): value is boolean {
  return typeof value === 'boolean';
}

function isMenuOrientation(value: unknown): value is AdminMenuOrientation {
  return (
    typeof value === 'string' && ADMIN_MENU_ORIENTATIONS.includes(value as AdminMenuOrientation)
  );
}

function isMenuDensity(value: unknown): value is AdminMenuDensity {
  return typeof value === 'string' && ADMIN_MENU_DENSITIES.includes(value as AdminMenuDensity);
}

function isSkin(value: unknown): value is AdminSkin {
  return typeof value === 'string' && ADMIN_SKINS.some((skin) => skin.id === value);
}
