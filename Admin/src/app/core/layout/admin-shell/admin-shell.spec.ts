import { signal } from '@angular/core';
import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';

import {
  AdminThemePreferences,
  DEFAULT_ADMIN_THEME_PREFERENCES,
} from '../../theme/admin-theme.model';
import { AdminThemeStore } from '../../theme/admin-theme.store';
import { AdminShell } from './admin-shell';

class MemoryAdminThemeStore {
  private readonly state = signal<AdminThemePreferences>({
    ...DEFAULT_ADMIN_THEME_PREFERENCES,
    sidenavOpened: true,
    menuOrientation: 'horizontal',
    menuDensity: 'mini',
  });

  readonly preferences = this.state.asReadonly();

  update(patch: Partial<AdminThemePreferences>): void {
    this.state.update((preferences) => ({ ...preferences, ...patch }));
  }
}

describe('AdminShell theme restoration', () => {
  const originalInnerWidth = window.innerWidth;
  let themeStore: MemoryAdminThemeStore;
  let shell: AdminShell;

  beforeEach(() => {
    Object.defineProperty(window, 'innerWidth', { configurable: true, value: 1200 });
    themeStore = new MemoryAdminThemeStore();

    TestBed.configureTestingModule({
      imports: [AdminShell],
      providers: [
        provideRouter([]),
        {
          provide: AdminThemeStore,
          useValue: themeStore,
        },
      ],
    });

    shell = TestBed.createComponent(AdminShell).componentInstance;
  });

  afterEach(() => {
    Object.defineProperty(window, 'innerWidth', {
      configurable: true,
      value: originalInnerWidth,
    });
  });

  it('temporarily applies mobile layout and restores the saved desktop theme', () => {
    expect(shell.effectiveMenuOrientation()).toBe('horizontal');
    expect(shell.sidenavOpened()).toBe(true);
    expect(shell.compactBrand()).toBe(true);

    Object.defineProperty(window, 'innerWidth', { configurable: true, value: 600 });
    shell.onWindowResize();

    expect(shell.effectiveMenuOrientation()).toBe('vertical');
    expect(shell.sidenavOpened()).toBe(false);
    expect(shell.compactBrand()).toBe(false);

    shell.toggleSidenav();
    expect(shell.sidenavOpened()).toBe(true);
    shell.closeMobileSidenav();

    Object.defineProperty(window, 'innerWidth', { configurable: true, value: 1200 });
    shell.onWindowResize();

    expect(shell.effectiveMenuOrientation()).toBe('horizontal');
    expect(shell.sidenavOpened()).toBe(true);
    expect(shell.compactBrand()).toBe(true);
    expect(themeStore.preferences().menuOrientation).toBe('horizontal');
  });
});
