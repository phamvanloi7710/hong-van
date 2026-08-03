import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormControl, FormRecord, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { finalize } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { I18nService } from '../../core/i18n/i18n.service';
import { ThemeStudioDataService } from './theme-studio-data.service';
import { THEME_STUDIO_TRANSLATIONS, themeTokenName } from './theme-studio.i18n';
import { PublicThemeRecord, THEME_TOKEN_CONTROLS, ThemeTokenControlSpec, ThemeTokenGroupName, ThemeTokens, ThemeTokenValue, ThemeVersionRecord } from './theme-studio.models';

@Component({
  selector: 'hv-theme-studio-page',
  imports: [CommonModule, ReactiveFormsModule, MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule, MatSelectModule, MatSnackBarModule],
  templateUrl: './theme-studio-page.html',
  styleUrl: './theme-studio-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ThemeStudioPage {
  private readonly data = inject(ThemeStudioDataService);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);

  readonly groups: readonly ThemeTokenGroupName[] = ['colors', 'fonts', 'sizes', 'spacing', 'radii', 'shadows', 'containers', 'buttons', 'headings', 'sections', 'animation'];
  readonly form = new FormRecord<FormControl<ThemeTokenValue>>({});
  readonly theme = signal<PublicThemeRecord | null>(null);
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly error = signal<string | null>(null);

  constructor() {
    this.load();
  }

  text(key: string): string {
    return THEME_STUDIO_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  tokenName(key: string): string {
    return themeTokenName(this.i18n.locale(), key);
  }

  controlsFor(group: ThemeTokenGroupName): readonly ThemeTokenControlSpec[] {
    return THEME_TOKEN_CONTROLS.filter((control) => control.group === group);
  }

  controlName(control: ThemeTokenControlSpec): string {
    return `${control.group}.${control.key}`;
  }

  status(version: ThemeVersionRecord): string {
    return version.status === 'published' ? this.text('statusPublished') : this.text(version.status);
  }

  load(): void {
    this.loading.set(true);
    this.error.set(null);
    this.data.active().pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (theme) => this.applyTheme(theme),
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
    });
  }

  save(): void {
    const theme = this.theme();
    if (!theme || this.form.invalid) return;
    this.saving.set(true);
    this.data.saveDraft(theme.public_id, this.tokens()).pipe(finalize(() => this.saving.set(false))).subscribe({
      next: (updated) => { this.applyTheme(updated); this.notify('saved'); },
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('saveError'))),
    });
  }

  preview(): void {
    const theme = this.theme();
    if (!theme) return;
    this.data.preview(theme.public_id).subscribe({
      next: (url) => window.open(url, '_blank', 'noopener,noreferrer'),
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('previewError'))),
    });
  }

  publish(): void {
    const theme = this.theme();
    if (!theme || !window.confirm(this.text('confirmPublish'))) return;
    this.saving.set(true);
    this.data.publish(theme.public_id).pipe(finalize(() => this.saving.set(false))).subscribe({
      next: (updated) => { this.applyTheme(updated); this.notify('published'); },
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('publishError'))),
    });
  }

  rollback(version: ThemeVersionRecord): void {
    const theme = this.theme();
    if (!theme || version.status !== 'published' || !window.confirm(this.text('confirmRollback'))) return;
    this.saving.set(true);
    this.data.rollback(theme.public_id, version.public_id).pipe(finalize(() => this.saving.set(false))).subscribe({
      next: (updated) => { this.applyTheme(updated); this.notify('rolledBack'); },
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('rollbackError'))),
    });
  }

  private applyTheme(theme: PublicThemeRecord): void {
    this.theme.set(theme);
    this.error.set(null);
    for (const key of Object.keys(this.form.controls)) this.form.removeControl(key);
    for (const spec of THEME_TOKEN_CONTROLS) {
      const value = theme.draft.tokens[spec.group][spec.key];
      const validators = spec.kind === 'number' ? [Validators.required, Validators.min(spec.min ?? 0), Validators.max(spec.max ?? 9999)] : [Validators.required];
      this.form.addControl(this.controlName(spec), new FormControl(value, { nonNullable: true, validators }));
    }
    this.form.markAsPristine();
  }

  private tokens(): ThemeTokens {
    const theme = this.theme();
    if (!theme) throw new Error('Theme is not loaded.');
    const tokens = structuredClone(theme.draft.tokens);
    const values = this.form.getRawValue();
    for (const spec of THEME_TOKEN_CONTROLS) tokens[spec.group][spec.key] = values[this.controlName(spec)];
    return tokens;
  }

  private notify(key: string): void {
    this.snackBar.open(this.text(key), undefined, { duration: 2800 });
  }
}
