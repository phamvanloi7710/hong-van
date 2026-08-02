import { ChangeDetectionStrategy, Component, input, output, signal } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatListModule } from '@angular/material/list';
import { RouterLink, RouterLinkActive } from '@angular/router';

import { ADMIN_MENU_ITEMS } from '../../navigation/admin-menu';
import { AdminMenuItem } from '../../navigation/admin-menu.model';
import { AdminMenuDensity } from '../../theme/admin-theme.model';

@Component({
  selector: 'hv-admin-sidebar',
  imports: [MatButtonModule, MatIconModule, MatListModule, RouterLink, RouterLinkActive],
  templateUrl: './admin-sidebar.html',
  styleUrl: './admin-sidebar.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminSidebar {
  readonly density = input.required<AdminMenuDensity>();
  readonly navigated = output<void>();
  readonly menuItems = ADMIN_MENU_ITEMS;
  readonly expandedGroup = signal<string | null>('catalog');

  toggleGroup(item: AdminMenuItem): void {
    if (item.children === undefined) {
      return;
    }

    this.expandedGroup.update((current) => (current === item.id ? null : item.id));
  }

  isExpanded(item: AdminMenuItem): boolean {
    return this.expandedGroup() === item.id;
  }
}
