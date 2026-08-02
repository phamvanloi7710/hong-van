import { ChangeDetectionStrategy, Component, input, output, signal } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatToolbarModule } from '@angular/material/toolbar';

import { AdminBrand } from '../../../shared/components/admin-brand/admin-brand';

@Component({
  selector: 'hv-admin-header',
  imports: [AdminBrand, MatButtonModule, MatIconModule, MatMenuModule, MatToolbarModule],
  templateUrl: './admin-header.html',
  styleUrl: './admin-header.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminHeader {
  readonly verticalMenu = input(true);
  readonly pinned = input(true);
  readonly compactBrand = input(false);
  readonly toggleMenu = output<void>();
  readonly togglePinned = output<void>();
  readonly openThemePanel = output<void>();

  readonly searchOpened = signal(false);

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
}
