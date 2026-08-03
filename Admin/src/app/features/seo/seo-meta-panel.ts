import { ChangeDetectionStrategy, Component, effect, inject, input, signal } from '@angular/core';
import { AbstractControl, FormControl, FormGroup, ReactiveFormsModule, ValidationErrors } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatTabsModule } from '@angular/material/tabs';
import { finalize } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { AuthStore } from '../../core/auth/auth.store';
import { I18nService } from '../../core/i18n/i18n.service';
import { MediaPickerService } from '../media/media-picker.service';
import { SeoDataService } from './seo-data.service';
import { SEO_TRANSLATIONS } from './seo.i18n';
import { SeoEntityType, SeoImage, SeoLocale, SeoMetaPayload, SeoMetaRecord } from './seo.models';

@Component({
  selector: 'hv-seo-meta-panel',
  imports: [ReactiveFormsModule, MatButtonModule, MatCardModule, MatCheckboxModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule, MatSelectModule, MatTabsModule],
  providers: [MediaPickerService],
  templateUrl: './seo-meta-panel.html',
  styleUrl: './seo-meta-panel.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SeoMetaPanel {
  readonly entityType = input.required<SeoEntityType>();
  readonly entityId = input.required<string>();
  readonly locale = input.required<SeoLocale>();
  readonly entityLabel = input.required<string>();

  private readonly data = inject(SeoDataService);
  private readonly picker = inject(MediaPickerService);
  private readonly snackBar = inject(MatSnackBar);
  private readonly i18n = inject(I18nService);
  readonly authStore = inject(AuthStore);

  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly error = signal<string | null>(null);
  readonly image = signal<SeoImage | null>(null);
  readonly form = new FormGroup({
    meta_title: new FormControl('', { nonNullable: true }),
    meta_description: new FormControl('', { nonNullable: true }),
    canonical_url: new FormControl('', { nonNullable: true, validators: [httpUrl] }),
    robots_index: new FormControl(true, { nonNullable: true }),
    robots_follow: new FormControl(true, { nonNullable: true }),
    og_title: new FormControl('', { nonNullable: true }),
    og_description: new FormControl('', { nonNullable: true }),
    og_type: new FormControl<'website' | 'article' | 'product'>('website', { nonNullable: true }),
    twitter_card: new FormControl<'summary' | 'summary_large_image'>('summary_large_image', { nonNullable: true }),
    twitter_title: new FormControl('', { nonNullable: true }),
    twitter_description: new FormControl('', { nonNullable: true }),
    focus_keywords: new FormControl('', { nonNullable: true }),
  });

  constructor() {
    effect(() => {
      const type = this.entityType();
      const id = this.entityId();
      const locale = this.locale();
      this.load(type, id, locale);
    });
  }

  text(key: string): string {
    return SEO_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  chooseImage(): void {
    this.picker.open({ acceptedMimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/avif'], selectedIds: this.image() ? [this.image()!.public_id] : [] }).subscribe((result) => {
      const item = result?.items[0];
      if (!item) return;
      this.image.set({ ...item, variants: [] });
    });
  }

  removeImage(): void {
    this.image.set(null);
  }

  save(): void {
    if (this.form.invalid || this.saving()) {
      this.form.markAllAsTouched();
      return;
    }
    this.saving.set(true);
    this.data.update(this.entityType(), this.entityId(), this.payload()).pipe(finalize(() => this.saving.set(false))).subscribe({
      next: (record) => {
        this.patch(record);
        this.snackBar.open(this.text('saved'), this.i18n.t('common.close'), { duration: 3000 });
      },
      error: (error: unknown) => this.snackBar.open(authErrorMessage(error, this.text('saveError')), this.i18n.t('common.close'), { duration: 5000 }),
    });
  }

  previewTitle(): string {
    return this.form.controls.meta_title.value.trim() || this.form.controls.og_title.value.trim() || this.entityLabel();
  }

  previewDescription(): string {
    return this.form.controls.meta_description.value.trim() || this.form.controls.og_description.value.trim() || this.text('noDescription');
  }

  private load(type: SeoEntityType, id: string, locale: SeoLocale): void {
    this.loading.set(true);
    this.error.set(null);
    this.data.show(type, id, locale).pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (record) => this.patch(record),
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
    });
  }

  private patch(record: SeoMetaRecord): void {
    this.image.set(record.og_image);
    this.form.reset({
      meta_title: record.meta_title ?? '', meta_description: record.meta_description ?? '', canonical_url: record.canonical_url ?? '',
      robots_index: record.robots_index, robots_follow: record.robots_follow, og_title: record.og_title ?? '',
      og_description: record.og_description ?? '', og_type: record.og_type, twitter_card: record.twitter_card,
      twitter_title: record.twitter_title ?? '', twitter_description: record.twitter_description ?? '',
      focus_keywords: record.focus_keywords.join(', '),
    });
  }

  private payload(): SeoMetaPayload {
    const value = this.form.getRawValue();
    const nullable = (text: string): string | null => text.trim() || null;
    return {
      locale: this.locale(), meta_title: nullable(value.meta_title), meta_description: nullable(value.meta_description),
      canonical_url: nullable(value.canonical_url), robots_index: value.robots_index, robots_follow: value.robots_follow,
      og_title: nullable(value.og_title), og_description: nullable(value.og_description), og_image_media_id: this.image()?.public_id ?? null,
      og_type: value.og_type, twitter_card: value.twitter_card, twitter_title: nullable(value.twitter_title),
      twitter_description: nullable(value.twitter_description), focus_keywords: value.focus_keywords.split(',').map((item) => item.trim()).filter(Boolean).slice(0, 10),
    };
  }
}

function httpUrl(control: AbstractControl<string>): ValidationErrors | null {
  if (!control.value.trim()) return null;
  try {
    const url = new URL(control.value);
    return url.protocol === 'http:' || url.protocol === 'https:' ? null : { httpUrl: true };
  } catch {
    return { httpUrl: true };
  }
}
