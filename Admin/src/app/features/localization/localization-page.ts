import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatSnackBar } from '@angular/material/snack-bar';
import { finalize } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { AuthStore } from '../../core/auth/auth.store';
import { DateTimeService } from '../../core/i18n/date-time.service';
import { I18nService } from '../../core/i18n/i18n.service';
import { TranslationPipe } from '../../core/i18n/translation.pipe';
import { LocalizationDataService } from './localization-data.service';
import { LOCALIZATION_TRANSLATIONS } from './localization.i18n';
import { AdminLanguage, LocalizationPayload, MissingTranslationLanguage, UpdateLanguagePayload } from './localization.models';

@Component({
  selector: 'hv-localization-page',
  imports: [MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatProgressSpinnerModule, MatSelectModule, MatSlideToggleModule, TranslationPipe],
  templateUrl: './localization-page.html',
  styleUrl: './localization-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LocalizationPage {
  private readonly data = inject(LocalizationDataService);
  private readonly dateTimes = inject(DateTimeService);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);
  readonly authStore = inject(AuthStore);

  readonly payload = signal<LocalizationPayload | null>(null);
  readonly loading = signal(true);
  readonly savingId = signal<string | null>(null);
  readonly error = signal<string | null>(null);

  constructor() {
    this.reload();
  }

  text(key: string): string {
    return LOCALIZATION_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  reload(): void {
    this.loading.set(true);
    this.error.set(null);
    this.data.load().pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (payload) => this.payload.set(payload),
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
    });
  }

  setActive(language: AdminLanguage, isActive: boolean): void {
    this.update(language, { is_active: isActive });
  }

  setDefault(language: AdminLanguage): void {
    this.update(language, { is_default: true });
  }

  setFallback(language: AdminLanguage, fallbackLocale: string | null): void {
    this.update(language, { fallback_locale: fallbackLocale });
  }

  fallbackOptions(language: AdminLanguage): readonly AdminLanguage[] {
    return this.payload()?.languages.filter((candidate) => candidate.locale !== language.locale) ?? [];
  }

  reportFor(locale: string): MissingTranslationLanguage | undefined {
    return this.payload()?.missing_translations.languages.find((report) => report.locale === locale);
  }

  previewMissing(report: MissingTranslationLanguage | undefined): readonly string[] {
    return report?.missing_keys.slice(0, 5) ?? [];
  }

  formattedTime(value: string | null, timezone?: string): string {
    if (!value) return '—';
    return this.dateTimes.format(value, timezone);
  }

  private update(language: AdminLanguage, data: UpdateLanguagePayload): void {
    this.savingId.set(language.public_id);
    this.data.updateLanguage(language.public_id, data).pipe(finalize(() => this.savingId.set(null))).subscribe({
      next: (payload) => {
        this.payload.set(payload);
        this.snackBar.open(this.text('saved'), this.i18n.t('common.close'), { duration: 3000 });
      },
      error: (error: unknown) => this.snackBar.open(authErrorMessage(error, this.text('saveError')), this.i18n.t('common.close'), { duration: 5000 }),
    });
  }
}
