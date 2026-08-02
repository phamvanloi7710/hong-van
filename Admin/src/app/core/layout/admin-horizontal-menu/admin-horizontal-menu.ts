import { ChangeDetectionStrategy, Component, inject, input } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { RouterLink, RouterLinkActive } from '@angular/router';

import { AuthStore } from '../../auth/auth.store';
import { ADMIN_MENU_ITEMS } from '../../navigation/admin-menu';
import { AdminMenuItem } from '../../navigation/admin-menu.model';
import { AdminMenuDensity } from '../../theme/admin-theme.model';

@Component({
  selector: 'hv-admin-horizontal-menu',
  imports: [MatButtonModule, MatIconModule, RouterLink, RouterLinkActive],
  templateUrl: './admin-horizontal-menu.html',
  styleUrl: './admin-horizontal-menu.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminHorizontalMenu {
  private readonly authStore = inject(AuthStore);
  readonly density = input.required<AdminMenuDensity>();
  readonly menuItems = ADMIN_MENU_ITEMS;

  canSee(item: AdminMenuItem): boolean {
    return this.authStore.hasPermission(item.permission);
  }

  canSeeGroup(item: AdminMenuItem): boolean {
    return item.children?.some((child) => this.canSee(child)) ?? true;
  }
}
