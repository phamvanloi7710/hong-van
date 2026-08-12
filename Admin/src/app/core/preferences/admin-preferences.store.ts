import { computed, inject, Injectable, signal } from '@angular/core';
import { catchError, concatMap, EMPTY, finalize, Observable, of, Subject, tap } from 'rxjs';

import { AdminLocale, I18nService } from '../i18n/i18n.service';
import { AdminThemePreferences } from '../theme/admin-theme.model';
import { AdminPreferenceApi } from './admin-preference.api';
import {
  AdminPreferences,
  AdminPreferencesDto,
  AdminPreferencesUpdateDto,
  DEFAULT_ADMIN_PREFERENCES,
} from './admin-preferences.model';
import { LocalAdminPreferencesCache } from './local-admin-preferences.cache';

type AdminPreferenceWrite =
  | {
      readonly kind: 'update';
      readonly sequence: number;
      readonly userPublicId: string;
      readonly update: AdminPreferencesUpdateDto;
    }
  | {
      readonly kind: 'reset';
      readonly sequence: number;
      readonly userPublicId: string;
    };

@Injectable({ providedIn: 'root' })
export class AdminPreferencesStore {
  private readonly api = inject(AdminPreferenceApi);
  private readonly cache = inject(LocalAdminPreferencesCache);
  private readonly i18n = inject(I18nService);
  private readonly state = signal<AdminPreferences>(DEFAULT_ADMIN_PREFERENCES);
  private readonly saveQueue = new Subject<AdminPreferenceWrite>();
  private activeUserPublicId: string | null = null;
  private pendingSaves = 0;
  private latestWriteSequence = 0;

  readonly preferences = this.state.asReadonly();
  readonly theme = computed(() => this.state().theme);
  readonly locale = computed(() => this.state().locale);
  readonly favoriteMenuIds = computed(() => this.state().favoriteMenuIds);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly error = signal<string | null>(null);

  constructor() {
    this.saveQueue
      .pipe(
        concatMap((write) => {
          if (write.userPublicId !== this.activeUserPublicId) {
            this.completePendingSave();

            return EMPTY;
          }

          return (write.kind === 'update' ? this.api.update(write.update) : this.api.reset()).pipe(
            tap((preferences) => {
              if (
                write.userPublicId === this.activeUserPublicId &&
                write.sequence === this.latestWriteSequence
              ) {
                this.applyDto(preferences);
              }
            }),
            catchError(() => {
              if (write.userPublicId === this.activeUserPublicId) {
                this.error.set(this.i18n.t('preferences.saveError'));
              }

              return EMPTY;
            }),
            finalize(() => this.completePendingSave()),
          );
        }),
      )
      .subscribe();
  }

  initialize(userPublicId: string): Observable<AdminPreferencesDto | null> {
    this.activeUserPublicId = userPublicId;
    this.error.set(null);

    const cached = this.cache.load(userPublicId);
    if (cached !== null) {
      this.applyDto(cached);
    } else {
      this.apply(DEFAULT_ADMIN_PREFERENCES, false);
    }

    this.loading.set(true);

    return this.api.get().pipe(
      tap((preferences) => {
        if (this.activeUserPublicId === userPublicId) {
          this.applyDto(preferences);
        }
      }),
      finalize(() => {
        if (this.activeUserPublicId === userPublicId) {
          this.loading.set(false);
        }
      }),
      catchError(() => {
        if (this.activeUserPublicId === userPublicId) {
          this.error.set(this.i18n.t('preferences.loadError'));
        }

        return of(null);
      }),
    );
  }

  applyServerPreferences(preferences: AdminPreferencesDto | null): void {
    if (preferences !== null) {
      this.applyDto(preferences);
    }
  }

  updateTheme(theme: AdminThemePreferences): void {
    this.apply({ ...this.state(), theme });
    this.persist({ theme: toThemeDto(theme) });
  }

  updateLocale(locale: AdminLocale): void {
    this.apply({ ...this.state(), locale });
    this.persist({ locale });
  }

  toggleFavorite(menuId: string): void {
    const current = this.state().favoriteMenuIds;
    const favoriteMenuIds = current.includes(menuId)
      ? current.filter((id) => id !== menuId)
      : [...current, menuId];

    this.updateFavoriteMenuIds(favoriteMenuIds);
  }

  updateFavoriteMenuIds(favoriteMenuIds: readonly string[]): void {
    this.apply({ ...this.state(), favoriteMenuIds });
    this.persist({ favorite_menu_ids: favoriteMenuIds });
  }

  reset(): void {
    const userPublicId = this.activeUserPublicId;
    if (userPublicId === null) {
      return;
    }

    this.error.set(null);
    this.pendingSaves += 1;
    this.saving.set(true);
    this.saveQueue.next({
      kind: 'reset',
      sequence: ++this.latestWriteSequence,
      userPublicId,
    });
  }

  clear(): void {
    if (this.activeUserPublicId !== null) {
      this.cache.remove(this.activeUserPublicId);
    }

    this.activeUserPublicId = null;
    this.latestWriteSequence += 1;
    this.apply(DEFAULT_ADMIN_PREFERENCES, false);
  }

  private persist(update: AdminPreferencesUpdateDto): void {
    const userPublicId = this.activeUserPublicId;
    if (userPublicId === null) {
      return;
    }

    this.saveCache();
    this.error.set(null);
    this.pendingSaves += 1;
    this.saving.set(true);
    this.saveQueue.next({
      kind: 'update',
      sequence: ++this.latestWriteSequence,
      userPublicId,
      update,
    });
  }

  private applyDto(preferences: AdminPreferencesDto): void {
    this.apply({
      theme: {
        fixedHeader: preferences.theme.fixed_header,
        fixedSidenav: preferences.theme.fixed_sidenav,
        fixedFooter: preferences.theme.fixed_footer,
        sidenavOpened: preferences.theme.sidenav_opened,
        sidenavPinned: preferences.theme.sidenav_pinned,
        menuOrientation: preferences.theme.menu_orientation,
        menuDensity: preferences.theme.menu_density,
        skin: preferences.theme.skin,
        rtl: preferences.theme.rtl,
      },
      locale: preferences.locale,
      favoriteMenuIds: preferences.favorite_menu_ids,
    });
  }

  private apply(preferences: AdminPreferences, saveCache = true): void {
    this.state.set(preferences);
    this.i18n.setLocale(preferences.locale);

    if (saveCache) {
      this.saveCache();
    }
  }

  private saveCache(): void {
    if (this.activeUserPublicId === null) {
      return;
    }

    const preferences = this.state();
    this.cache.save(this.activeUserPublicId, {
      theme: toThemeDto(preferences.theme),
      locale: preferences.locale,
      favorite_menu_ids: preferences.favoriteMenuIds,
    });
  }

  private completePendingSave(): void {
    this.pendingSaves = Math.max(0, this.pendingSaves - 1);
    this.saving.set(this.pendingSaves > 0);
  }
}

function toThemeDto(theme: AdminThemePreferences) {
  return {
    fixed_header: theme.fixedHeader,
    fixed_sidenav: theme.fixedSidenav,
    fixed_footer: theme.fixedFooter,
    sidenav_opened: theme.sidenavOpened,
    sidenav_pinned: theme.sidenavPinned,
    menu_orientation: theme.menuOrientation,
    menu_density: theme.menuDensity,
    skin: theme.skin,
    rtl: theme.rtl,
  } as const;
}
