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
import { SERVICE_TRANSLATIONS } from './service.i18n';
import {
  ServiceCtaType,
  ServiceDialogData,
  ServiceLocale,
  ServiceStatus,
  ServiceType,
} from './service.models';

@Component({
  selector: 'hv-service-dialog',
  imports: [ReactiveFormsModule, MatButtonModule, MatCheckboxModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSelectModule, MatTabsModule],
  providers: [MediaPickerService],
  templateUrl: './service-dialog.html',
  styleUrl: './service-dialog.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ServiceDialog {
  readonly data = inject<ServiceDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<ServiceDialog, unknown>);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly mediaPicker = inject(MediaPickerService);
  readonly locales: readonly ServiceLocale[] = ['vi', 'en', 'zh'];
  readonly statuses: readonly ServiceStatus[] = ['draft', 'published', 'scheduled', 'archived'];
  readonly serviceTypes: readonly ServiceType[] = ['general', 'transportation_link', 'warehouse_link'];
  readonly ctaTypes: readonly ServiceCtaType[] = ['none', 'contact', 'quote'];
  readonly selectedMediaIds = signal<readonly string[]>(this.data.service?.media.map((item) => item.public_id) ?? []);
  readonly form = this.fb.group({
    category_id: new FormControl<string | null>(null), code: ['', Validators.required], service_type: this.fb.control<ServiceType>('general'),
    status: this.fb.control<ServiceStatus>('draft'), cta_type: this.fb.control<ServiceCtaType>('contact'), is_featured: false, sort_order: [0, Validators.min(0)],
    published_at: new FormControl<string | null>(null), unpublished_at: new FormControl<string | null>(null),
    vi_name: ['', Validators.required], vi_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], vi_summary: '', vi_content: '', vi_sections: '', vi_cta_label: '', vi_meta_title: '', vi_meta_description: '',
    en_name: ['', Validators.required], en_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], en_summary: '', en_content: '', en_sections: '', en_cta_label: '', en_meta_title: '', en_meta_description: '',
    zh_name: ['', Validators.required], zh_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], zh_summary: '', zh_content: '', zh_sections: '', zh_cta_label: '', zh_meta_title: '', zh_meta_description: '',
  });

  constructor() { this.patch(); }

  text(key: string): string { return SERVICE_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  languageLabel(locale: ServiceLocale): string { return this.text(locale === 'vi' ? 'languageVi' : locale === 'en' ? 'languageEn' : 'languageZh'); }
  typeLabel(type: ServiceType): string { return this.text(type === 'general' ? 'generalType' : type === 'transportation_link' ? 'transportationLink' : 'warehouseLink'); }
  categoryName(publicId: string): string { return this.localized(this.data.categories.find((item) => item.public_id === publicId)?.translations ?? []); }
  control(locale: ServiceLocale, field: 'name' | 'slug' | 'summary' | 'content' | 'sections' | 'cta_label' | 'meta_title' | 'meta_description'): FormControl<string> { return this.form.controls[`${locale}_${field}`]; }

  chooseMedia(): void {
    this.mediaPicker.open({ multiple: true, selectedIds: [...this.selectedMediaIds()] }).subscribe((result) => {
      if (result) this.selectedMediaIds.set(result.items.map((item) => item.public_id));
    });
  }

  removeMedia(publicId: string): void {
    this.selectedMediaIds.update((items) => items.filter((item) => item !== publicId));
  }

  submit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    const raw = this.form.getRawValue();
    const isGeneral = raw.service_type === 'general';
    this.dialogRef.close({
      category_id: raw.category_id, code: raw.code.trim(), service_type: raw.service_type, status: raw.status,
      cta_type: isGeneral ? raw.cta_type : 'none', is_featured: raw.is_featured, sort_order: raw.sort_order,
      published_at: this.iso(raw.published_at), unpublished_at: this.iso(raw.unpublished_at),
      translations: this.locales.map((locale) => ({
        locale, name: this.control(locale, 'name').value.trim(), slug: this.control(locale, 'slug').value.trim(),
        summary: this.nullable(this.control(locale, 'summary').value), content: isGeneral ? this.nullable(this.control(locale, 'content').value) : null,
        content_sections: isGeneral ? this.sections(this.control(locale, 'sections').value) : [],
        cta_label: isGeneral && raw.cta_type !== 'none' ? this.nullable(this.control(locale, 'cta_label').value) : null,
        meta_title: this.nullable(this.control(locale, 'meta_title').value), meta_description: this.nullable(this.control(locale, 'meta_description').value),
      })),
      media: isGeneral ? this.selectedMediaIds().map((mediaId, sortOrder) => ({ media_id: mediaId, role: this.mediaRole(mediaId, sortOrder), sort_order: sortOrder })) : [],
    });
  }

  private patch(): void {
    const service = this.data.service;
    if (!service) return;
    this.form.patchValue({
      category_id: service.category?.public_id ?? null, code: service.code, service_type: service.service_type, status: service.status,
      cta_type: service.cta_type, is_featured: service.is_featured, sort_order: service.sort_order,
      published_at: this.localDate(service.published_at), unpublished_at: this.localDate(service.unpublished_at),
    });
    for (const translation of service.translations) {
      this.form.patchValue({
        [`${translation.locale}_name`]: translation.name, [`${translation.locale}_slug`]: translation.slug,
        [`${translation.locale}_summary`]: translation.summary ?? '', [`${translation.locale}_content`]: translation.content ?? '',
        [`${translation.locale}_sections`]: translation.content_sections.map((section) => `${section.title} | ${section.body}`).join('\n'),
        [`${translation.locale}_cta_label`]: translation.cta_label ?? '', [`${translation.locale}_meta_title`]: translation.meta_title ?? '',
        [`${translation.locale}_meta_description`]: translation.meta_description ?? '',
      });
    }
  }

  private mediaRole(publicId: string, index: number): 'hero' | 'gallery' | 'document' {
    return this.data.service?.media.find((item) => item.public_id === publicId)?.role ?? (index === 0 ? 'hero' : 'gallery');
  }
  private localized(translations: readonly { readonly locale: ServiceLocale; readonly name: string }[]): string {
    const locale = this.i18n.locale();
    return translations.find((item) => item.locale === locale)?.name ?? translations.find((item) => item.locale === 'vi')?.name ?? translations[0]?.name ?? '—';
  }
  private sections(value: string): readonly { readonly title: string; readonly body: string }[] {
    return value.split('\n').map((line) => line.trim()).filter(Boolean).map((line) => {
      const separator = line.indexOf('|');
      return separator < 0 ? { title: line, body: line } : { title: line.slice(0, separator).trim(), body: line.slice(separator + 1).trim() };
    });
  }
  private nullable(value: string): string | null { return value.trim() || null; }
  private iso(value: string | null): string | null { return value ? new Date(value).toISOString() : null; }
  private localDate(value: string | null): string | null { return value ? new Date(value).toISOString().slice(0, 16) : null; }
}
