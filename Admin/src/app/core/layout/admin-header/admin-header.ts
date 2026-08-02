import {
  ChangeDetectionStrategy,
  Component,
  DestroyRef,
  inject,
  input,
  output,
  signal,
} from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatToolbarModule } from '@angular/material/toolbar';
import { Router } from '@angular/router';
import { finalize } from 'rxjs';

import { AdminBrand } from '../../../shared/components/admin-brand/admin-brand';
import { AuthService } from '../../auth/auth.service';

@Component({
  selector: 'hv-admin-header',
  imports: [AdminBrand, MatButtonModule, MatIconModule, MatMenuModule, MatToolbarModule],
  templateUrl: './admin-header.html',
  styleUrl: './admin-header.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminHeader {
  private readonly authService = inject(AuthService);
  private readonly destroyRef = inject(DestroyRef);
  private readonly router = inject(Router);

  readonly verticalMenu = input(true);
  readonly pinned = input(true);
  readonly compactBrand = input(false);
  readonly toggleMenu = output<void>();
  readonly togglePinned = output<void>();

  readonly searchOpened = signal(false);
  readonly logoutPending = signal(false);
  readonly logoutError = signal<string | null>(null);
  readonly authStore = this.authService.store;

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
        error: () => this.logoutError.set('Không thể đăng xuất. Vui lòng thử lại.'),
      });
  }
}
