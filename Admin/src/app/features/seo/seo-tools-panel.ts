import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { forkJoin, finalize } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { AuthStore } from '../../core/auth/auth.store';
import { I18nService } from '../../core/i18n/i18n.service';
import { SeoToolsDataService } from './seo-tools-data.service';
import { SEO_TOOLS_TRANSLATIONS } from './seo-tools.i18n';
import { RedirectPayload, RedirectRule, SchemaPreview, SitemapHealth } from './seo-tools.models';
import { SeoLocale } from './seo.models';

@Component({
  selector: 'hv-seo-tools-panel',
  imports: [ReactiveFormsModule, MatButtonModule, MatCardModule, MatCheckboxModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule, MatSelectModule],
  templateUrl: './seo-tools-panel.html',
  styleUrl: './seo-tools-panel.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SeoToolsPanel {
  private readonly data = inject(SeoToolsDataService);
  private readonly formBuilder = inject(FormBuilder);
  private readonly i18n = inject(I18nService);
  readonly authStore = inject(AuthStore);

  readonly rules = signal<readonly RedirectRule[]>([]);
  readonly health = signal<SitemapHealth | null>(null);
  readonly schema = signal<SchemaPreview | null>(null);
  readonly editingId = signal<string | null>(null);
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly message = signal<string | null>(null);
  readonly error = signal<string | null>(null);
  readonly locales: readonly (SeoLocale | '*')[] = ['*', 'vi', 'en', 'zh'];
  readonly statuses: readonly (301 | 302 | 410)[] = [301, 302, 410];
  readonly schemaTypes: readonly SchemaPreview['type'][] = ['organization', 'local_business', 'website'];

  readonly redirectForm = this.formBuilder.nonNullable.group({
    source_path: ['', [Validators.required, Validators.maxLength(500)]],
    locale: ['*' as SeoLocale | '*', Validators.required],
    target_path: ['', Validators.maxLength(500)],
    status_code: [301 as 301 | 302 | 410, Validators.required],
    is_active: [true],
    note: ['', Validators.maxLength(1000)],
  });
  readonly robotsForm = this.formBuilder.nonNullable.group({ disallow_paths: ['', Validators.maxLength(4000)] });
  readonly schemaForm = this.formBuilder.nonNullable.group({ type: ['organization' as SchemaPreview['type']], locale: [this.i18n.locale() as SeoLocale] });

  constructor() {
    this.load();
  }

  text(key: string): string {
    return SEO_TOOLS_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  load(): void {
    this.loading.set(true);
    this.error.set(null);
    forkJoin({ rules: this.data.redirects(), health: this.data.health() }).pipe(finalize(() => this.loading.set(false))).subscribe({
      next: ({ rules, health }) => {
        this.rules.set(rules);
        this.health.set(health);
        this.robotsForm.patchValue({ disallow_paths: health.disallow_paths });
      },
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
    });
  }

  edit(rule: RedirectRule): void {
    this.editingId.set(rule.public_id);
    this.redirectForm.setValue({ source_path: rule.source_path, locale: rule.locale, target_path: rule.target_path ?? '', status_code: rule.status_code, is_active: rule.is_active, note: rule.note ?? '' });
  }

  cancelEdit(): void {
    this.editingId.set(null);
    this.redirectForm.reset({ source_path: '', locale: '*', target_path: '', status_code: 301, is_active: true, note: '' });
  }

  saveRedirect(): void {
    if (this.redirectForm.invalid || !this.canUpdate()) {
      this.redirectForm.markAllAsTouched();
      return;
    }
    const value = this.redirectForm.getRawValue();
    const payload: RedirectPayload = { ...value, target_path: value.status_code === 410 || !value.target_path.trim() ? null : value.target_path.trim(), source_path: value.source_path.trim(), note: value.note.trim() || null };
    this.run(this.data.saveRedirect(this.editingId(), payload), () => {
      this.cancelEdit();
      this.load();
    });
  }

  delete(rule: RedirectRule): void {
    if (!this.canUpdate() || !confirm(this.text('confirmDelete'))) return;
    this.run(this.data.deleteRedirect(rule.public_id), () => this.load());
  }

  saveRobots(): void {
    if (this.robotsForm.invalid || !this.canUpdate()) return;
    this.run(this.data.saveRobots(this.robotsForm.getRawValue().disallow_paths), (value) => this.robotsForm.patchValue({ disallow_paths: value }));
  }

  regenerate(): void {
    if (!this.canUpdate()) return;
    this.run(this.data.regenerate(), () => {
      this.message.set(this.text('queued'));
      this.load();
    });
  }

  previewSchema(): void {
    const value = this.schemaForm.getRawValue();
    this.saving.set(true);
    this.error.set(null);
    this.data.schemaPreview(value.type, value.locale).pipe(finalize(() => this.saving.set(false))).subscribe({
      next: (preview) => this.schema.set(preview),
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
    });
  }

  canUpdate(): boolean {
    return this.authStore.hasPermission('seo.update');
  }

  statusLabel(status: number): string {
    return this.text(status === 301 ? 'permanent' : status === 302 ? 'temporary' : 'gone');
  }

  private run<T>(request: import('rxjs').Observable<T>, success: (value: T) => void): void {
    this.saving.set(true);
    this.error.set(null);
    this.message.set(null);
    request.pipe(finalize(() => this.saving.set(false))).subscribe({
      next: (value) => {
        this.message.set(this.text('saved'));
        success(value);
      },
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('saveError'))),
    });
  }
}
