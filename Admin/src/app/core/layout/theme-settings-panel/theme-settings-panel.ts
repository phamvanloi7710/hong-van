import { ChangeDetectionStrategy, Component, inject, input, output } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';

import {
  ADMIN_MENU_DENSITIES,
  ADMIN_MENU_ORIENTATIONS,
  ADMIN_SKINS,
  AdminMenuDensity,
  AdminMenuOrientation,
  AdminSkin,
} from '../../theme/admin-theme.model';
import { AdminThemeStore } from '../../theme/admin-theme.store';

@Component({
  selector: 'hv-theme-settings-panel',
  imports: [MatButtonModule, MatIconModule, MatSlideToggleModule],
  templateUrl: './theme-settings-panel.html',
  styleUrl: './theme-settings-panel.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ThemeSettingsPanel {
  readonly opened = input(false);
  readonly closed = output<void>();
  readonly themeStore = inject(AdminThemeStore);
  readonly skins = ADMIN_SKINS;
  readonly menuOrientations = ADMIN_MENU_ORIENTATIONS;
  readonly menuDensities = ADMIN_MENU_DENSITIES;

  setBoolean(
    key: 'fixedHeader' | 'fixedSidenav' | 'fixedFooter' | 'sidenavOpened' | 'sidenavPinned' | 'rtl',
    value: boolean,
  ): void {
    this.themeStore.update({ [key]: value });
  }

  setMenuOrientation(menuOrientation: AdminMenuOrientation): void {
    this.themeStore.update({
      menuOrientation,
      fixedSidenav:
        menuOrientation === 'horizontal' ? false : this.themeStore.preferences().fixedSidenav,
    });
  }

  setMenuDensity(menuDensity: AdminMenuDensity): void {
    this.themeStore.update({ menuDensity });
  }

  setSkin(skin: AdminSkin): void {
    this.themeStore.update({ skin });
  }
}
