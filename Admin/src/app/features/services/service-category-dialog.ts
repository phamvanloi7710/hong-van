import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { FormControl, NonNullableFormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatDialogModule, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatTabsModule } from '@angular/material/tabs';

import { I18nService } from '../../core/i18n/i18n.service';
import { SERVICE_TRANSLATIONS } from './service.i18n';
import { ServiceCategoryDialogData, ServiceDialogResult, ServiceLocale } from './service.models';

@Component({
  selector: 'hv-service-category-dialog',
  imports: [ReactiveFormsModule, MatButtonModule, MatDialogModule, MatFormFieldModule, MatInputModule, MatSelectModule, MatSlideToggleModule, MatTabsModule],
  templateUrl: './service-category-dialog.html',
  styleUrl: './service-dialog.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ServiceCategoryDialog {
  readonly data = inject<ServiceCategoryDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<ServiceCategoryDialog, ServiceDialogResult>);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  readonly locales: readonly ServiceLocale[] = ['vi', 'en', 'zh'];
  readonly form = this.fb.group({
    code: ['', [Validators.required, Validators.maxLength(64)]], parent_id: new FormControl<string | null>(null), is_active: true, sort_order: [0, [Validators.required, Validators.min(0)]],
    vi_name: ['', Validators.required], vi_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], vi_summary: '', vi_meta_title: '', vi_meta_description: '',
    en_name: ['', Validators.required], en_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], en_summary: '', en_meta_title: '', en_meta_description: '',
    zh_name: ['', Validators.required], zh_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], zh_summary: '', zh_meta_title: '', zh_meta_description: '',
  });

  constructor() { this.patch(); }

  text(key: string): string { return SERVICE_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  languageLabel(locale: ServiceLocale): string { return this.text(locale === 'vi' ? 'languageVi' : locale === 'en' ? 'languageEn' : 'languageZh'); }
  categoryName(publicId: string): string { return this.localized(this.data.categories.find((item) => item.public_id === publicId)?.translations ?? []); }
  control(locale: ServiceLocale, field: 'name' | 'slug' | 'summary' | 'meta_title' | 'meta_description'): FormControl<string> { return this.form.controls[`${locale}_${field}`]; }

  submit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    const raw = this.form.getRawValue();
    this.dialogRef.close({
      publicId: this.data.category?.public_id ?? null,
      payload: {
        parent_id: raw.parent_id, code: raw.code.trim(), is_active: raw.is_active, sort_order: raw.sort_order,
        translations: this.locales.map((locale) => ({
          locale, name: this.control(locale, 'name').value.trim(), slug: this.control(locale, 'slug').value.trim(),
          summary: this.nullable(this.control(locale, 'summary').value), meta_title: this.nullable(this.control(locale, 'meta_title').value),
          meta_description: this.nullable(this.control(locale, 'meta_description').value),
        })),
      },
    });
  }

  private patch(): void {
    const category = this.data.category;
    if (!category) return;
    this.form.patchValue({ code: category.code, parent_id: category.parent_id, is_active: category.is_active, sort_order: category.sort_order });
    for (const translation of category.translations) {
      this.form.patchValue({
        [`${translation.locale}_name`]: translation.name, [`${translation.locale}_slug`]: translation.slug,
        [`${translation.locale}_summary`]: translation.summary ?? '', [`${translation.locale}_meta_title`]: translation.meta_title ?? '',
        [`${translation.locale}_meta_description`]: translation.meta_description ?? '',
      });
    }
  }

  private localized(translations: readonly { readonly locale: ServiceLocale; readonly name: string }[]): string {
    const locale = this.i18n.locale();
    return translations.find((item) => item.locale === locale)?.name ?? translations.find((item) => item.locale === 'vi')?.name ?? translations[0]?.name ?? '—';
  }
  private nullable(value: string): string | null { return value.trim() || null; }
}
