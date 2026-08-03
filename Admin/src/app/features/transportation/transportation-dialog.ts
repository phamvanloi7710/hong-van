import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormControl, NonNullableFormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatDialogModule, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatTabsModule } from '@angular/material/tabs';

import { I18nService } from '../../core/i18n/i18n.service';
import { MediaPickerService } from '../media/media-picker.service';
import { TRANSPORT_TRANSLATIONS } from './transportation.i18n';
import {
  AvailabilityDisplay,
  TransportDialogData,
  TransportLocale,
  TransportStatus,
} from './transportation.models';

type TranslationField = 'name' | 'slug' | 'summary' | 'description' | 'body_dimensions' | 'meta_title' | 'meta_description';

@Component({
  selector: 'hv-transportation-dialog',
  imports: [ReactiveFormsModule, MatButtonModule, MatCheckboxModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSelectModule, MatTabsModule],
  providers: [MediaPickerService],
  templateUrl: './transportation-dialog.html',
  styleUrl: './transportation-dialog.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TransportationDialog {
  readonly data = inject<TransportDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<TransportationDialog, unknown>);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly mediaPicker = inject(MediaPickerService);
  readonly locales: readonly TransportLocale[] = ['vi', 'en', 'zh'];
  readonly statuses: readonly TransportStatus[] = ['draft', 'published', 'scheduled', 'archived'];
  readonly availabilityOptions: readonly AvailabilityDisplay[] = ['available', 'limited', 'unavailable', 'contact'];
  readonly selectedMediaIds = signal<readonly string[]>(this.data.item?.media?.map((item) => item.public_id) ?? []);
  readonly form = this.fb.group({
    code: ['', Validators.required], vehicle_type_id: new FormControl<string | null>(null),
    payload_capacity: new FormControl<number | null>(null), payload_unit: new FormControl<'kg' | 'ton' | null>('ton'),
    availability_display: this.fb.control<AvailabilityDisplay>('contact'), origin_code: '', destination_code: '',
    status: this.fb.control<TransportStatus>('draft'), is_featured: false, is_active: true, sort_order: [0, Validators.min(0)],
    published_at: new FormControl<string | null>(null), unpublished_at: new FormControl<string | null>(null),
    vi_name: ['', Validators.required], vi_slug: '', vi_summary: '', vi_description: '', vi_body_dimensions: '', vi_meta_title: '', vi_meta_description: '',
    en_name: ['', Validators.required], en_slug: '', en_summary: '', en_description: '', en_body_dimensions: '', en_meta_title: '', en_meta_description: '',
    zh_name: ['', Validators.required], zh_slug: '', zh_summary: '', zh_description: '', zh_body_dimensions: '', zh_meta_title: '', zh_meta_description: '',
  });

  constructor() {
    if (this.data.kind !== 'types') {
      for (const locale of this.locales) this.control(locale, 'slug').addValidators([Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]);
    }
    if (this.data.kind === 'vehicles') this.form.controls.vehicle_type_id.addValidators(Validators.required);
    if (this.data.kind === 'routes') {
      this.form.controls.origin_code.addValidators(Validators.required);
      this.form.controls.destination_code.addValidators(Validators.required);
    }
    this.patch();
  }

  text(key: string): string { return TRANSPORT_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  title(): string { return `${this.text(this.data.item ? 'update' : 'create')} · ${this.text(this.data.kind)}`; }
  languageLabel(locale: TransportLocale): string { return this.text(locale === 'vi' ? 'languageVi' : locale === 'en' ? 'languageEn' : 'languageZh'); }
  localizedType(publicId: string): string {
    const translations = this.data.types.find((item) => item.public_id === publicId)?.translations ?? [];
    return translations.find((item) => item.locale === this.i18n.locale())?.name ?? translations.find((item) => item.locale === 'vi')?.name ?? '—';
  }
  control(locale: TransportLocale, field: TranslationField): FormControl<string> { return this.form.controls[`${locale}_${field}`]; }

  chooseMedia(): void {
    this.mediaPicker.open({ multiple: true, acceptedMimeTypes: ['image/jpeg', 'image/png', 'image/webp'], selectedIds: [...this.selectedMediaIds()] }).subscribe((result) => {
      if (result) this.selectedMediaIds.set(result.items.map((item) => item.public_id));
    });
  }

  removeMedia(publicId: string): void { this.selectedMediaIds.update((items) => items.filter((item) => item !== publicId)); }

  submit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    const raw = this.form.getRawValue();
    const common = { code: raw.code.trim(), sort_order: raw.sort_order };
    const translatable = { ...common, status: raw.status, is_featured: raw.is_featured, published_at: this.iso(raw.published_at), unpublished_at: this.iso(raw.unpublished_at) };
    if (this.data.kind === 'types') {
      this.dialogRef.close({ ...common, is_active: raw.is_active, translations: this.locales.map((locale) => ({ locale, name: this.control(locale, 'name').value.trim(), description: this.nullable(this.control(locale, 'description').value) })) });
      return;
    }
    if (this.data.kind === 'vehicles') {
      this.dialogRef.close({ ...translatable, vehicle_type_id: raw.vehicle_type_id, payload_capacity: raw.payload_capacity, payload_unit: raw.payload_unit, availability_display: raw.availability_display,
        translations: this.locales.map((locale) => ({ locale, name: this.control(locale, 'name').value.trim(), slug: this.control(locale, 'slug').value.trim(), summary: this.nullable(this.control(locale, 'summary').value), description: this.nullable(this.control(locale, 'description').value), body_dimensions: this.nullable(this.control(locale, 'body_dimensions').value), meta_title: this.nullable(this.control(locale, 'meta_title').value), meta_description: this.nullable(this.control(locale, 'meta_description').value) })),
        media: this.selectedMediaIds().map((mediaId, index) => ({ media_id: mediaId, role: index === 0 ? 'hero' : 'gallery', sort_order: index })),
      });
      return;
    }
    const translations = this.locales.map((locale) => ({ locale, name: this.control(locale, 'name').value.trim(), slug: this.control(locale, 'slug').value.trim(), summary: this.nullable(this.control(locale, 'summary').value) }));
    this.dialogRef.close(this.data.kind === 'routes' ? { ...translatable, origin_code: raw.origin_code.trim(), destination_code: raw.destination_code.trim(), translations } : { ...translatable, translations });
  }

  private patch(): void {
    const item = this.data.item;
    if (!item) return;
    this.form.patchValue({ code: item.code, vehicle_type_id: item.vehicle_type_id ?? null, payload_capacity: item.payload_capacity ? Number(item.payload_capacity) : null,
      payload_unit: item.payload_unit ?? 'ton', availability_display: item.availability_display ?? 'contact', origin_code: item.origin_code ?? '', destination_code: item.destination_code ?? '',
      status: item.status ?? 'draft', is_featured: item.is_featured ?? false, is_active: item.is_active ?? true, sort_order: item.sort_order,
      published_at: this.localDate(item.published_at), unpublished_at: this.localDate(item.unpublished_at) });
    for (const translation of item.translations) {
      this.form.patchValue({ [`${translation.locale}_name`]: translation.name, [`${translation.locale}_slug`]: translation.slug ?? '', [`${translation.locale}_summary`]: translation.summary ?? '',
        [`${translation.locale}_description`]: translation.description ?? '', [`${translation.locale}_body_dimensions`]: translation.body_dimensions ?? '',
        [`${translation.locale}_meta_title`]: translation.meta_title ?? '', [`${translation.locale}_meta_description`]: translation.meta_description ?? '' });
    }
  }

  private nullable(value: string): string | null { return value.trim() || null; }
  private iso(value: string | null): string | null { return value ? new Date(value).toISOString() : null; }
  private localDate(value: string | null | undefined): string | null { return value ? new Date(value).toISOString().slice(0, 16) : null; }
}
