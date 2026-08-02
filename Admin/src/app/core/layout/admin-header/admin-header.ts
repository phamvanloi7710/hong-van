import {
  ChangeDetectionStrategy,
  Component,
  computed,
  DestroyRef,
  inject,
  input,
  output,
  signal,
} from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatSelectModule } from '@angular/material/select';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatTooltipModule } from '@angular/material/tooltip';
import { Router, RouterLink } from '@angular/router';
import { finalize } from 'rxjs';

import { AdminBrand } from '../../../shared/components/admin-brand/admin-brand';
import { AuthService } from '../../auth/auth.service';
import { ADMIN_LOCALES, AdminLocale, I18nService } from '../../i18n/i18n.service';
import { TranslationPipe } from '../../i18n/translation.pipe';
import {
  findAdminMenuItemById,
  NAVIGABLE_ADMIN_MENU_ITEMS,
} from '../../navigation/admin-menu';
import { AdminMenuItem } from '../../navigation/admin-menu.model';
import { AdminPreferencesStore } from '../../preferences/admin-preferences.store';

@Component({
  selector: 'hv-admin-header',
  imports: [
    AdminBrand,
    MatButtonModule,
    MatFormFieldModule,
    MatIconModule,
    MatMenuModule,
    MatSelectModule,
    MatToolbarModule,
    MatTooltipModule,
    RouterLink,
    TranslationPipe,
  ],
  templateUrl: './admin-header.html',
  styleUrl: './admin-header.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminHeader {
  private readonly authService = inject(AuthService);
  private readonly destroyRef = inject(DestroyRef);
  private readonly router = inject(Router);
  readonly preferences = inject(AdminPreferencesStore);
  readonly i18n = inject(I18nService);

  readonly verticalMenu = input(true);
  readonly pinned = input(true);
  readonly compactBrand = input(false);
  readonly toggleMenu = output<void>();
  readonly togglePinned = output<void>();

  readonly searchOpened = signal(false);
  readonly logoutPending = signal(false);
  readonly logoutError = signal<string | null>(null);
  readonly authStore = this.authService.store;
  readonly locales = ADMIN_LOCALES;
  readonly availableFavoriteMenuItems = computed(() =>
    NAVIGABLE_ADMIN_MENU_ITEMS.filter((item) => this.authStore.hasPermission(item.permission)),
  );
  readonly favoriteMenuItems = computed(() =>
    this.preferences
      .favoriteMenuIds()
      .map((id) => findAdminMenuItemById(id))
      .filter((item): item is AdminMenuItem =>
        item !== undefined && item.route !== undefined && this.authStore.hasPermission(item.permission),
      ),
  );

  toggleSearch(): void {
    this.searchOpened.update((opened) => !opened);
  }

  toggleFullscreen(): void {
    if (document.fullscreenElement === null) {
      void document.documentElement.requestFullscreen();
      return;
    }

    void document.exitFullscreen();
  }

  updateFavorites(value: unknown): void {
    if (Array.isArray(value) && value.every((item) => typeof item === 'string')) {
      this.preferences.updateFavoriteMenuIds(value);
    }
  }

  selectLocale(locale: AdminLocale): void {
    this.preferences.updateLocale(locale);
  }

  logout(): void {
    if (this.logoutPending()) {
      return;
    }

    this.logoutError.set(null);
    this.logoutPending.set(true);
    this.authService
      .logout()
      .pipe(
        finalize(() => this.logoutPending.set(false)),
        takeUntilDestroyed(this.destroyRef),
      )
      .subscribe({
        next: () => void this.router.navigate(['/login']),
        error: () => this.logoutError.set(this.i18n.t('header.logoutError')),
      });
  }
}
