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
import { WAREHOUSE_TRANSLATIONS } from './warehouse.i18n';
import { MapDisplay, WarehouseDialogData, WarehouseLocale, WarehouseStatus } from './warehouse.models';

type TranslationField = 'name' | 'slug' | 'summary' | 'description' | 'address_display' | 'area_description' | 'capacity_description' | 'security_description' | 'fire_safety_description' | 'business_hours_description' | 'meta_title' | 'meta_description';

@Component({
  selector: 'hv-warehouse-dialog',
  imports: [ReactiveFormsModule, MatButtonModule, MatCheckboxModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSelectModule, MatTabsModule],
  providers: [MediaPickerService],
  templateUrl: './warehouse-dialog.html',
  styleUrl: './warehouse-dialog.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class WarehouseDialog {
  readonly data = inject<WarehouseDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<WarehouseDialog, unknown>);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly mediaPicker = inject(MediaPickerService);
  readonly locales: readonly WarehouseLocale[] = ['vi', 'en', 'zh'];
  readonly statuses: readonly WarehouseStatus[] = ['draft', 'published', 'scheduled', 'archived'];
  readonly mapDisplays: readonly MapDisplay[] = ['hidden', 'approximate', 'exact'];
  readonly selectedMediaIds = signal<readonly string[]>(this.data.item?.media?.map((item) => item.public_id) ?? []);
  readonly form = this.fb.group({
    code: ['', Validators.required], icon: '', is_active: true, sort_order: [0, Validators.min(0)],
    area_value: new FormControl<number | null>(null), area_unit: this.fb.control<'m2'>('m2'), latitude: new FormControl<number | null>(null), longitude: new FormControl<number | null>(null),
    map_display: this.fb.control<MapDisplay>('hidden'), business_hours: '', facility_ids: this.fb.control<readonly string[]>([]), service_ids: this.fb.control<readonly string[]>([]),
    status: this.fb.control<WarehouseStatus>('draft'), is_featured: false, published_at: new FormControl<string | null>(null), unpublished_at: new FormControl<string | null>(null),
    vi_name: ['', Validators.required], vi_slug: '', vi_summary: '', vi_description: '', vi_address_display: '', vi_area_description: '', vi_capacity_description: '', vi_security_description: '', vi_fire_safety_description: '', vi_business_hours_description: '', vi_meta_title: '', vi_meta_description: '',
    en_name: ['', Validators.required], en_slug: '', en_summary: '', en_description: '', en_address_display: '', en_area_description: '', en_capacity_description: '', en_security_description: '', en_fire_safety_description: '', en_business_hours_description: '', en_meta_title: '', en_meta_description: '',
    zh_name: ['', Validators.required], zh_slug: '', zh_summary: '', zh_description: '', zh_address_display: '', zh_area_description: '', zh_capacity_description: '', zh_security_description: '', zh_fire_safety_description: '', zh_business_hours_description: '', zh_meta_title: '', zh_meta_description: '',
  });

  constructor() {
    if (this.data.kind === 'warehouses') for (const locale of this.locales) this.control(locale, 'slug').addValidators([Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]);
    this.patch();
  }

  text(key: string): string { return WAREHOUSE_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  title(): string { return `${this.text(this.data.item ? 'update' : 'create')} · ${this.text(this.data.kind)}`; }
  languageLabel(locale: WarehouseLocale): string { return this.text(locale === 'vi' ? 'languageVi' : locale === 'en' ? 'languageEn' : 'languageZh'); }
  control(locale: WarehouseLocale, field: TranslationField): FormControl<string> { return this.form.controls[`${locale}_${field}`]; }
  localized(item: { readonly translations: readonly { readonly locale: WarehouseLocale; readonly name: string }[] }): string { return item.translations.find((translation) => translation.locale === this.i18n.locale())?.name ?? item.translations.find((translation) => translation.locale === 'vi')?.name ?? '—'; }

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
    if (this.data.kind !== 'warehouses') {
      this.dialogRef.close({ ...common, icon: this.nullable(raw.icon), is_active: raw.is_active, translations: this.locales.map((locale) => ({ locale, name: this.control(locale, 'name').value.trim(), description: this.nullable(this.control(locale, 'description').value) })) });
      return;
    }
    this.dialogRef.close({ ...common, area_value: raw.area_value, area_unit: raw.area_value === null ? null : raw.area_unit, latitude: raw.latitude, longitude: raw.longitude, map_display: raw.map_display,
      business_hours: this.businessHours(raw.business_hours), status: raw.status, is_featured: raw.is_featured, published_at: this.iso(raw.published_at), unpublished_at: this.iso(raw.unpublished_at),
      facility_ids: raw.facility_ids, service_ids: raw.service_ids,
      translations: this.locales.map((locale) => ({ locale, name: this.control(locale, 'name').value.trim(), slug: this.control(locale, 'slug').value.trim(), summary: this.nullable(this.control(locale, 'summary').value), description: this.nullable(this.control(locale, 'description').value), address_display: this.nullable(this.control(locale, 'address_display').value), area_description: this.nullable(this.control(locale, 'area_description').value), capacity_description: this.nullable(this.control(locale, 'capacity_description').value), security_description: this.nullable(this.control(locale, 'security_description').value), fire_safety_description: this.nullable(this.control(locale, 'fire_safety_description').value), business_hours_description: this.nullable(this.control(locale, 'business_hours_description').value), meta_title: this.nullable(this.control(locale, 'meta_title').value), meta_description: this.nullable(this.control(locale, 'meta_description').value) })),
      media: this.selectedMediaIds().map((mediaId, index) => ({ media_id: mediaId, role: index === 0 ? 'hero' : 'gallery', sort_order: index })),
    });
  }

  private patch(): void {
    const item = this.data.item;
    if (!item) return;
    this.form.patchValue({ code: item.code, icon: item.icon ?? '', is_active: item.is_active ?? true, sort_order: item.sort_order, area_value: item.area_value ? Number(item.area_value) : null, area_unit: 'm2', latitude: item.latitude ? Number(item.latitude) : null, longitude: item.longitude ? Number(item.longitude) : null, map_display: item.map_display ?? 'hidden', business_hours: this.hoursText(item.business_hours ?? []), facility_ids: item.facility_ids ?? [], service_ids: item.service_ids ?? [], status: item.status ?? 'draft', is_featured: item.is_featured ?? false, published_at: this.localDate(item.published_at), unpublished_at: this.localDate(item.unpublished_at) });
    for (const translation of item.translations) for (const field of ['name', 'slug', 'summary', 'description', 'address_display', 'area_description', 'capacity_description', 'security_description', 'fire_safety_description', 'business_hours_description', 'meta_title', 'meta_description'] as const) this.form.patchValue({ [`${translation.locale}_${field}`]: translation[field] ?? '' });
  }

  private businessHours(value: string): readonly { readonly day: string; readonly opens: string | null; readonly closes: string | null; readonly closed: boolean }[] {
    return value.split('\n').map((line) => line.trim()).filter(Boolean).map((line) => { const [day, opens, closes, closed] = line.split('|').map((part) => part.trim()); return { day, opens: opens || null, closes: closes || null, closed: closed === 'true' }; });
  }
  private hoursText(hours: readonly { readonly day: string; readonly opens: string | null; readonly closes: string | null; readonly closed: boolean }[]): string { return hours.map((hour) => `${hour.day}|${hour.opens ?? ''}|${hour.closes ?? ''}|${hour.closed}`).join('\n'); }
  private nullable(value: string): string | null { return value.trim() || null; }
  private iso(value: string | null): string | null { return value ? new Date(value).toISOString() : null; }
  private localDate(value: string | null | undefined): string | null { return value ? new Date(value).toISOString().slice(0, 16) : null; }
}
