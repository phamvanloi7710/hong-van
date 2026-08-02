import { ChangeDetectionStrategy, Component, input } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { RouterLink, RouterLinkActive } from '@angular/router';

import { ADMIN_MENU_ITEMS } from '../../navigation/admin-menu';
import { AdminMenuDensity } from '../../theme/admin-theme.model';

@Component({
  selector: 'hv-admin-horizontal-menu',
  imports: [MatButtonModule, MatIconModule, RouterLink, RouterLinkActive],
  templateUrl: './admin-horizontal-menu.html',
  styleUrl: './admin-horizontal-menu.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminHorizontalMenu {
  readonly density = input.required<AdminMenuDensity>();
  readonly menuItems = ADMIN_MENU_ITEMS;
}
