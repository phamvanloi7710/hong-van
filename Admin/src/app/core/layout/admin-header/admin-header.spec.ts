import { signal } from '@angular/core';
import { TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { MatSelect } from '@angular/material/select';
import { provideRouter } from '@angular/router';
import { of } from 'rxjs';

import { DashboardDataService } from '../../../features/dashboard/dashboard-data.service';
import { AdminUser } from '../../auth/auth.models';
import { AuthService } from '../../auth/auth.service';
import { AuthStore } from '../../auth/auth.store';
import { AdminPreferencesStore } from '../../preferences/admin-preferences.store';
import { AdminHeader } from './admin-header';

const user: AdminUser = {
  public_id: '01JADMINUSER00000000000000',
  name: 'Admin Hồng Vân',
  email: 'admin@example.test',
  email_verified_at: null,
  is_active: true,
  locked_at: null,
  roles: ['content_editor'],
  permissions: ['dashboard.view', 'products.view', 'users.view'],
};

describe('AdminHeader favorite navigation', () => {
  const favoriteMenuIds = signal<readonly string[]>(['identity', 'dashboard', 'products']);
  const updateFavoriteMenuIds = vi.fn<(ids: readonly string[]) => void>();
  let authStore: AuthStore;

  beforeEach(() => {
    favoriteMenuIds.set(['identity', 'dashboard', 'products']);
    updateFavoriteMenuIds.mockReset();
    authStore = new AuthStore();
    authStore.markAuthenticated(user);

    TestBed.configureTestingModule({
      imports: [AdminHeader],
      providers: [
        provideRouter([]),
        {
          provide: AuthService,
          useValue: { store: authStore, logout: () => of(undefined) },
        },
        {
          provide: AdminPreferencesStore,
          useValue: {
            favoriteMenuIds: favoriteMenuIds.asReadonly(),
            updateFavoriteMenuIds,
            updateLocale: vi.fn(),
          },
        },
        {
          provide: DashboardDataService,
          useValue: {
            notifications: () => of({ items: [], unread_count: 0 }),
            markNotificationRead: vi.fn(),
            markAllNotificationsRead: vi.fn(),
          },
        },
      ],
    });
  });

  it('keeps the Annular multi-select before ordered icon-only shortcuts', () => {
    const fixture = TestBed.createComponent(AdminHeader);
    fixture.detectChanges();

    const select = fixture.debugElement.query(By.directive(MatSelect)).componentInstance as MatSelect;
    const navigation = fixture.nativeElement.querySelector('.favorite-navigation') as HTMLElement;
    const shortcuts = Array.from(
      navigation.querySelectorAll<HTMLElement>('.favorite-menu-link mat-icon'),
    ).map((icon) => icon.textContent?.trim());

    expect(select.multiple).toBe(true);
    expect(navigation.firstElementChild?.classList.contains('favorite-picker-shell')).toBe(true);
    expect(shortcuts).toEqual(['admin_panel_settings', 'dashboard', 'eco']);
  });

  it('preserves selection order and removes shortcuts after permission changes', () => {
    const fixture = TestBed.createComponent(AdminHeader);
    const header = fixture.componentInstance;

    expect(header.favoriteMenuItems().map((item) => item.id)).toEqual([
      'identity',
      'dashboard',
      'products',
    ]);

    header.updateFavorites(['products', 'identity']);
    expect(updateFavoriteMenuIds).toHaveBeenCalledWith(['products', 'identity']);

    authStore.markAuthenticated({ ...user, permissions: ['users.view'] });
    fixture.detectChanges();

    expect(header.availableFavoriteMenuItems().map((item) => item.id)).toEqual(['identity']);
    expect(header.favoriteMenuItems().map((item) => item.id)).toEqual(['identity']);
    expect(fixture.nativeElement.querySelectorAll('.favorite-menu-link')).toHaveLength(1);
  });
});
