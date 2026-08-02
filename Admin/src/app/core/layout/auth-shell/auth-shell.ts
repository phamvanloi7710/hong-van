import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { RouterOutlet } from '@angular/router';

import { ADMIN_LOCALES, AdminLocale, I18nService } from '../../i18n/i18n.service';
import { TranslationPipe } from '../../i18n/translation.pipe';

@Component({
  selector: 'hv-auth-shell',
  imports: [MatButtonModule, MatIconModule, MatMenuModule, RouterOutlet, TranslationPipe],
  templateUrl: './auth-shell.html',
  styleUrl: './auth-shell.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AuthShell {
  readonly i18n = inject(I18nService);
  readonly locales = ADMIN_LOCALES;

  selectLocale(locale: AdminLocale): void {
    this.i18n.setLocale(locale);
  }
}
