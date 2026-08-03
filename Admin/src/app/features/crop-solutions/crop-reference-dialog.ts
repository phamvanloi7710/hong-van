import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormControl, NonNullableFormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatDialogModule, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatTabsModule } from '@angular/material/tabs';

import { I18nService } from '../../core/i18n/i18n.service';
import { MediaPickerService } from '../media/media-picker.service';
import { CROP_SOLUTION_TRANSLATIONS } from './crop-solution.i18n';
import { CropDialogResult, CropLocale, CropReferenceDialogData } from './crop-solution.models';

@Component({
  selector: 'hv-crop-reference-dialog',
  imports: [ReactiveFormsModule, MatButtonModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSelectModule, MatSlideToggleModule, MatTabsModule],
  providers: [MediaPickerService],
  templateUrl: './crop-reference-dialog.html',
  styleUrl: './crop-reference-dialog.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CropReferenceDialog {
  readonly data = inject<CropReferenceDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<CropReferenceDialog, CropDialogResult>);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly mediaPicker = inject(MediaPickerService);

  readonly locales: readonly CropLocale[] = ['vi', 'en', 'zh'];
  readonly imageId = signal<string | null>(this.data.item?.image_media_id ?? null);
  readonly form = this.fb.group({
    code: ['', [Validators.required, Validators.maxLength(64)]],
    parent_id: new FormControl<string | null>(null),
    category_id: new FormControl<string | null>(null),
    crop_id: new FormControl<string | null>(null),
    is_active: true,
    sort_order: [0, [Validators.required, Validators.min(0)]],
    vi_name: ['', Validators.required], vi_slug: ['', Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)], vi_summary: '', vi_content: '', vi_description: '', vi_meta_title: '', vi_meta_description: '',
    en_name: ['', Validators.required], en_slug: ['', Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)], en_summary: '', en_content: '', en_description: '', en_meta_title: '', en_meta_description: '',
    zh_name: ['', Validators.required], zh_slug: ['', Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)], zh_summary: '', zh_content: '', zh_description: '', zh_meta_title: '', zh_meta_description: '',
  });

  constructor() {
    this.patchItem();
  }

  text(key: string): string { return CROP_SOLUTION_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  languageLabel(locale: CropLocale): string { return this.text(locale === 'vi' ? 'languageVi' : locale === 'en' ? 'languageEn' : 'languageZh'); }
  control(locale: CropLocale, field: 'name' | 'slug' | 'summary' | 'content' | 'description' | 'meta_title' | 'meta_description'): FormControl<string> { return this.form.controls[`${locale}_${field}`]; }

  chooseImage(): void {
    this.mediaPicker.open({ multiple: false, acceptedMimeTypes: ['image/*'], selectedIds: this.imageId() ? [this.imageId()!] : [] })
      .subscribe((result) => this.imageId.set(result?.items[0]?.public_id ?? this.imageId()));
  }

  submit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    const raw = this.form.getRawValue();
    const translations = this.locales.map((locale) => this.translation(locale));
    const common = { code: raw.code.trim(), image_media_id: this.imageId(), is_active: raw.is_active, sort_order: raw.sort_order, translations };
    const payload = this.data.type === 'category'
      ? { ...common, parent_id: raw.parent_id }
      : this.data.type === 'crop'
        ? { ...common, category_id: raw.category_id }
        : { ...common, crop_id: raw.crop_id };
    this.dialogRef.close({ publicId: this.data.item?.public_id ?? null, payload });
  }

  private translation(locale: CropLocale): Record<string, unknown> {
    const result: Record<string, unknown> = {
      locale,
      name: this.control(locale, 'name').value.trim(),
      summary: this.nullable(this.control(locale, 'summary').value),
    };
    if (this.data.type !== 'stage') {
      result['slug'] = this.control(locale, 'slug').value.trim();
      result['meta_title'] = this.nullable(this.control(locale, 'meta_title').value);
      result['meta_description'] = this.nullable(this.control(locale, 'meta_description').value);
    }
    if (this.data.type === 'crop') result['description'] = this.nullable(this.control(locale, 'description').value);
    if (this.data.type === 'stage') result['content'] = this.nullable(this.control(locale, 'content').value);
    return result;
  }

  private patchItem(): void {
    const item = this.data.item;
    if (!item) return;
    this.form.patchValue({
      code: item.code, is_active: item.is_active, sort_order: item.sort_order,
      parent_id: this.data.type === 'category' && 'parent_id' in item ? item.parent_id : null,
      category_id: this.data.type === 'crop' && 'category_id' in item ? item.category_id : null,
      crop_id: this.data.type === 'stage' && 'crop_id' in item ? item.crop_id : null,
    });
    for (const translation of item.translations) {
      const locale = translation.locale;
      this.form.patchValue({
        [`${locale}_name`]: translation.name,
        [`${locale}_slug`]: 'slug' in translation ? translation.slug : '',
        [`${locale}_summary`]: translation.summary ?? '',
        [`${locale}_content`]: 'content' in translation ? translation.content ?? '' : '',
        [`${locale}_description`]: 'description' in translation ? translation.description ?? '' : '',
        [`${locale}_meta_title`]: 'meta_title' in translation ? translation.meta_title ?? '' : '',
        [`${locale}_meta_description`]: 'meta_description' in translation ? translation.meta_description ?? '' : '',
      });
    }
  }

  private nullable(value: string): string | null { return value.trim() || null; }
}
