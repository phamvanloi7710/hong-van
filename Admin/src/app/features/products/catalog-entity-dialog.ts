import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { FormControl, NonNullableFormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';

import { I18nService } from '../../core/i18n/i18n.service';
import { MediaPickerService } from '../media/media-picker.service';
import { PRODUCT_TRANSLATIONS } from './product.i18n';
import {
  BrandTranslation,
  CatalogEntityDialogData,
  CatalogEntityDialogResult,
  CategoryTranslation,
  ProductAttribute,
  ProductBrand,
  ProductCategory,
  ProductTag,
} from './product.models';

@Component({
  selector: 'hv-catalog-entity-dialog',
  imports: [ReactiveFormsModule, MatButtonModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSelectModule, MatSlideToggleModule],
  providers: [MediaPickerService],
  templateUrl: './catalog-entity-dialog.html',
  styleUrl: './catalog-entity-dialog.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CatalogEntityDialog {
  readonly data = inject<CatalogEntityDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<CatalogEntityDialog, CatalogEntityDialogResult>);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly mediaPicker = inject(MediaPickerService);

  readonly type = this.data.type;
  readonly categories = this.data.categories;
  readonly form = this.fb.group({
    code: ['', [Validators.required, Validators.maxLength(100)]],
    name: ['', [Validators.required, Validators.maxLength(255)]],
    slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]],
    parent_id: new FormControl<string | null>(null),
    logo_media_id: new FormControl<string | null>(null),
    is_active: true,
    is_featured: false,
    sort_order: [0, [Validators.required, Validators.min(0)]],
    data_type: 'text',
    unit: new FormControl<string | null>(null),
    options: '',
    is_filterable: false,
    is_required: false,
    vi_name: '', vi_slug: '', vi_description: '',
    en_name: '', en_slug: '', en_description: '',
    zh_name: '', zh_slug: '', zh_description: '',
  });

  constructor() {
    this.patchItem();
  }

  text(key: string): string {
    return PRODUCT_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  chooseLogo(): void {
    this.mediaPicker.open({ multiple: false, acceptedMimeTypes: ['image/'] }).subscribe((result) => {
      const selected = result?.items[0];
      if (selected) this.form.controls.logo_media_id.setValue(selected.public_id);
    });
  }

  save(): void {
    this.ensureLocalizedRequirements();
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.dialogRef.close({ publicId: this.data.item?.public_id ?? null, payload: this.payload() });
  }

  private patchItem(): void {
    const item = this.data.item;
    if (item === null) return;

    if (this.type === 'category') {
      const category = item as ProductCategory;
      this.form.patchValue({ code: category.code, parent_id: category.parent_id, is_active: category.is_active, is_featured: category.is_featured, sort_order: category.sort_order });
      this.patchTranslations(category.translations);
    } else if (this.type === 'brand') {
      const brand = item as ProductBrand;
      this.form.patchValue({ code: brand.code, logo_media_id: brand.logo_media_id, is_active: brand.is_active, sort_order: brand.sort_order });
      this.patchTranslations(brand.translations);
    } else if (this.type === 'tag') {
      const tag = item as ProductTag;
      this.form.patchValue({ name: tag.name, slug: tag.slug });
    } else {
      const attribute = item as ProductAttribute;
      this.form.patchValue({
        code: attribute.code, name: attribute.name, data_type: attribute.data_type, unit: attribute.unit,
        options: attribute.options?.join('\n') ?? '', is_filterable: attribute.is_filterable,
        is_required: attribute.is_required, sort_order: attribute.sort_order,
      });
    }
  }

  private patchTranslations(translations: readonly CategoryTranslation[] | readonly BrandTranslation[]): void {
    for (const translation of translations) {
      const description = 'summary' in translation ? translation.summary : translation.description;
      this.form.controls[`${translation.locale}_name`].setValue(translation.name);
      this.form.controls[`${translation.locale}_slug`].setValue(translation.slug);
      this.form.controls[`${translation.locale}_description`].setValue(description ?? '');
    }
  }

  private ensureLocalizedRequirements(): void {
    if (this.type !== 'category' && this.type !== 'brand') return;
    for (const locale of ['vi', 'en', 'zh'] as const) {
      this.form.controls[`${locale}_name`].addValidators(Validators.required);
      this.form.controls[`${locale}_slug`].addValidators([Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]);
      this.form.controls[`${locale}_name`].updateValueAndValidity();
      this.form.controls[`${locale}_slug`].updateValueAndValidity();
    }
  }

  private payload(): unknown {
    const value = this.form.getRawValue();
    if (this.type === 'tag') return { name: value.name, slug: value.slug };
    if (this.type === 'attribute') {
      return {
        code: value.code, name: value.name, data_type: value.data_type, unit: value.unit || null,
        options: value.data_type === 'option' ? value.options.split('\n').map((option) => option.trim()).filter(Boolean) : null,
        is_filterable: value.is_filterable, is_required: value.is_required, sort_order: value.sort_order,
      };
    }

    const translations = (['vi', 'en', 'zh'] as const).map((locale) => ({
      locale,
      name: value[`${locale}_name`],
      slug: value[`${locale}_slug`],
      ...(this.type === 'category'
        ? { summary: value[`${locale}_description`] || null, meta_title: null, meta_description: null }
        : { description: value[`${locale}_description`] || null, meta_title: null, meta_description: null }),
    }));

    return this.type === 'category'
      ? { parent_id: value.parent_id, code: value.code, is_active: value.is_active, is_featured: value.is_featured, sort_order: value.sort_order, translations }
      : { code: value.code, logo_media_id: value.logo_media_id, is_active: value.is_active, sort_order: value.sort_order, translations };
  }
}
