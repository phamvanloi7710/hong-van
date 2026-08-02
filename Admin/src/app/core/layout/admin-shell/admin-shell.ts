import {
  ChangeDetectionStrategy,
  Component,
  computed,
  HostListener,
  inject,
  signal,
  ViewChild,
} from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSidenavContent, MatSidenavModule } from '@angular/material/sidenav';
import { NavigationEnd, Router, RouterOutlet } from '@angular/router';
import { filter } from 'rxjs';

import { AdminThemeStore } from '../../theme/admin-theme.store';
import { AdminBreadcrumb } from '../admin-breadcrumb/admin-breadcrumb';
import { AdminFooter } from '../admin-footer/admin-footer';
import { AdminHeader } from '../admin-header/admin-header';
import { AdminHorizontalMenu } from '../admin-horizontal-menu/admin-horizontal-menu';
import { AdminSidebar } from '../admin-sidebar/admin-sidebar';
import { ThemeSettingsPanel } from '../theme-settings-panel/theme-settings-panel';

const MOBILE_BREAKPOINT = 960;

@Component({
  selector: 'hv-admin-shell',
  imports: [
    AdminBreadcrumb,
    AdminFooter,
    AdminHeader,
    AdminHorizontalMenu,
    AdminSidebar,
    MatButtonModule,
    MatIconModule,
    MatSidenavModule,
    RouterOutlet,
    ThemeSettingsPanel,
  ],
  templateUrl: './admin-shell.html',
  styleUrl: './admin-shell.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminShell {
  @ViewChild(MatSidenavContent) private sidenavContent?: MatSidenavContent;

  private readonly router = inject(Router);

  readonly themeStore = inject(AdminThemeStore);
  readonly isMobile = signal(window.innerWidth <= MOBILE_BREAKPOINT);
  readonly mobileSidenavOpened = signal(false);
  readonly themePanelOpened = signal(false);
  readonly showBackToTop = signal(false);

  readonly effectiveMenuOrientation = computed(() =>
    this.isMobile() ? 'vertical' : this.themeStore.preferences().menuOrientation,
  );
  readonly sidenavOpened = computed(() =>
    this.isMobile() ? this.mobileSidenavOpened() : this.themeStore.preferences().sidenavOpened,
  );
  readonly sidenavMode = computed(() =>
    this.isMobile() || !this.themeStore.preferences().sidenavPinned ? 'over' : 'side',
  );
  readonly compactBrand = computed(
    () => this.themeStore.preferences().menuDensity === 'mini' && !this.isMobile(),
  );
  readonly shellClasses = computed(() => {
    const preferences = this.themeStore.preferences();

    return [
      'admin-shell',
      `skin-${preferences.skin}`,
      `menu-${preferences.menuDensity}`,
      preferences.fixedHeader ? 'fixed-header' : '',
      preferences.fixedSidenav ? 'fixed-sidenav' : '',
      preferences.fixedFooter ? 'fixed-footer' : '',
      this.effectiveMenuOrientation() === 'horizontal' ? 'horizontal-menu' : 'vertical-menu',
    ]
      .filter(Boolean)
      .join(' ');
  });

  constructor() {
    this.router.events
      .pipe(
        filter((event): event is NavigationEnd => event instanceof NavigationEnd),
        takeUntilDestroyed(),
      )
      .subscribe(() => {
        if (this.isMobile()) {
          this.mobileSidenavOpened.set(false);
        }

        this.scrollToTop('auto');
      });
  }

  @HostListener('window:resize')
  onWindowResize(): void {
    const mobile = window.innerWidth <= MOBILE_BREAKPOINT;
    this.isMobile.set(mobile);

    if (!mobile) {
      this.mobileSidenavOpened.set(false);
    }
  }

  toggleSidenav(): void {
    if (this.isMobile()) {
      this.mobileSidenavOpened.update((opened) => !opened);
      return;
    }

    this.themeStore.update({ sidenavOpened: !this.themeStore.preferences().sidenavOpened });
  }

  togglePinned(): void {
    this.themeStore.update({ sidenavPinned: !this.themeStore.preferences().sidenavPinned });
  }

  closeMobileSidenav(): void {
    if (this.isMobile()) {
      this.mobileSidenavOpened.set(false);
    }
  }

  onContentScroll(event: Event): void {
    const target = event.target;
    this.showBackToTop.set(target instanceof HTMLElement && target.scrollTop > 300);
  }

  scrollToTop(behavior: ScrollBehavior = 'smooth'): void {
    this.sidenavContent?.getElementRef().nativeElement.scrollTo({ top: 0, behavior });
    window.scrollTo({ top: 0, behavior });
  }
}
