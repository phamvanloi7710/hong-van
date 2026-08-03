import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormControl, NonNullableFormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatTabsModule } from '@angular/material/tabs';

import { I18nService } from '../../core/i18n/i18n.service';
import { MediaPickerService } from '../media/media-picker.service';
import { PRODUCT_TRANSLATIONS } from './product.i18n';
import {
  AdminContentLocale,
  ProductAttribute,
  ProductAttributeValue,
  ProductEditorData,
  ProductMedia,
  ProductPayload,
  ProductPriceMode,
  ProductSpecification,
  ProductStatus,
  ProductTranslation,
} from './product.models';

@Component({
  selector: 'hv-product-editor-dialog',
  imports: [ReactiveFormsModule, MatButtonModule, MatCheckboxModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSelectModule, MatSlideToggleModule, MatTabsModule],
  providers: [MediaPickerService],
  templateUrl: './product-editor-dialog.html',
  styleUrl: './product-editor-dialog.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ProductEditorDialog {
  readonly data = inject<ProductEditorData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<ProductEditorDialog, ProductPayload>);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly mediaPicker = inject(MediaPickerService);

  readonly locales: readonly AdminContentLocale[] = ['vi', 'en', 'zh'];
  readonly priceModes: readonly ProductPriceMode[] = ['fixed', 'from', 'range', 'market', 'dealer', 'quantity', 'contact'];
  readonly media = signal<readonly ProductMedia[]>(this.data.product?.media ?? []);
  readonly specifications = signal<readonly ProductSpecification[]>(this.data.product?.specifications ?? []);
  readonly attributeValues = signal<Readonly<Record<string, string>>>(this.initialAttributeValues());

  readonly form = this.fb.group({
    sku: ['', [Validators.required, Validators.maxLength(100)]],
    code: new FormControl<string | null>(null),
    status: this.fb.control<ProductStatus>('draft'),
    category_id: new FormControl<string | null>(null),
    brand_id: new FormControl<string | null>(null),
    origin: new FormControl<string | null>(null),
    packaging: new FormControl<string | null>(null),
    is_featured: false,
    price_mode: this.fb.control<ProductPriceMode>('contact'),
    price_amount: new FormControl<string | null>(null),
    price_minimum: new FormControl<string | null>(null),
    price_maximum: new FormControl<string | null>(null),
    currency: ['VND', [Validators.required, Validators.pattern(/^[A-Z]{3}$/)]],
    price_unit: new FormControl<string | null>(null),
    price_note: new FormControl<string | null>(null),
    price_visible: true,
    tag_ids: this.fb.control<readonly string[]>([]),
    related_product_ids: this.fb.control<readonly string[]>([]),
    published_at: new FormControl<string | null>(null),
    unpublished_at: new FormControl<string | null>(null),
    vi_name: ['', Validators.required], vi_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], vi_short: '', vi_description: '', vi_benefits: '', vi_usage: '', vi_meta_title: '', vi_meta_description: '',
    en_name: ['', Validators.required], en_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], en_short: '', en_description: '', en_benefits: '', en_usage: '', en_meta_title: '', en_meta_description: '',
    zh_name: ['', Validators.required], zh_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], zh_short: '', zh_description: '', zh_benefits: '', zh_usage: '', zh_meta_title: '', zh_meta_description: '',
  });

  constructor() {
    this.patchProduct();
  }

  text(key: string): string {
    return PRODUCT_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  languageLabel(locale: AdminContentLocale): string {
    return this.text(locale === 'vi' ? 'languageVi' : locale === 'en' ? 'languageEn' : 'languageZh');
  }

  translationControl(locale: AdminContentLocale, field: 'name' | 'slug' | 'short' | 'description' | 'benefits' | 'usage' | 'meta_title' | 'meta_description'): FormControl<string> {
    return this.form.controls[`${locale}_${field}`];
  }

  chooseMedia(): void {
    this.mediaPicker.open({ multiple: true, selectedIds: this.media().map((item) => item.media_id) }).subscribe((result) => {
      if (!result) return;
      const existing = new Map(this.media().map((item) => [item.media_id, item]));
      for (const item of result.items) {
        if (!existing.has(item.public_id)) {
          existing.set(item.public_id, {
            ...item,
            media_id: item.public_id,
            title: item.original_filename,
            role: 'gallery',
            locale: '*',
            is_primary: existing.size === 0,
            sort_order: existing.size,
          });
        }
      }
      this.media.set([...existing.values()]);
    });
  }

  setMediaRole(mediaId: string, role: ProductMedia['role']): void {
    this.updateMedia(mediaId, { role });
  }

  setMediaPrimary(mediaId: string): void {
    this.media.update((items) => items.map((item) => ({ ...item, is_primary: item.media_id === mediaId })));
  }

  moveMedia(index: number, offset: -1 | 1): void {
    const target = index + offset;
    if (target < 0 || target >= this.media().length) return;
    const items = [...this.media()];
    [items[index], items[target]] = [items[target], items[index]];
    this.media.set(items.map((item, sortOrder) => ({ ...item, sort_order: sortOrder })));
  }

  removeMedia(mediaId: string): void {
    const remaining = this.media().filter((item) => item.media_id !== mediaId);
    if (remaining.length > 0 && !remaining.some((item) => item.is_primary)) remaining[0] = { ...remaining[0], is_primary: true };
    this.media.set(remaining.map((item, sortOrder) => ({ ...item, sort_order: sortOrder })));
  }

  attributeValue(attribute: ProductAttribute): string {
    return this.attributeValues()[attribute.public_id] ?? '';
  }

  setAttributeValue(publicId: string, value: string): void {
    this.attributeValues.update((values) => ({ ...values, [publicId]: value }));
  }

  valueFromEvent(event: Event): string {
    return event.target instanceof HTMLInputElement ? event.target.value : '';
  }

  addSpecification(): void {
    this.specifications.update((items) => [...items, { locale: 'vi', label: '', value: '', unit: null, sort_order: items.length }]);
  }

  updateSpecification(index: number, field: keyof ProductSpecification, value: string): void {
    this.specifications.update((items) => items.map((item, itemIndex) => itemIndex === index ? { ...item, [field]: field === 'unit' ? value || null : value } : item));
  }

  removeSpecification(index: number): void {
    this.specifications.update((items) => items.filter((_, itemIndex) => itemIndex !== index).map((item, sortOrder) => ({ ...item, sort_order: sortOrder })));
  }

  save(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    this.dialogRef.close(this.payload());
  }

  private patchProduct(): void {
    const product = this.data.product;
    if (!product) return;
    this.form.patchValue({
      sku: product.sku, code: product.code, status: product.status, category_id: product.category?.public_id ?? null,
      brand_id: product.brand?.public_id ?? null, origin: product.origin, packaging: product.packaging,
      is_featured: product.is_featured, price_mode: product.price.mode, price_amount: product.price.amount,
      price_minimum: product.price.minimum, price_maximum: product.price.maximum, currency: product.price.currency,
      price_unit: product.price.unit, price_note: product.price.note, price_visible: product.price.visible,
      tag_ids: product.tags.map((tag) => tag.public_id), related_product_ids: product.related_products.map((related) => related.public_id),
      published_at: this.toDateTimeLocal(product.published_at), unpublished_at: this.toDateTimeLocal(product.unpublished_at),
    });
    for (const translation of product.translations) this.patchTranslation(translation);
  }

  private patchTranslation(translation: ProductTranslation): void {
    this.translationControl(translation.locale, 'name').setValue(translation.name);
    this.translationControl(translation.locale, 'slug').setValue(translation.slug);
    this.translationControl(translation.locale, 'short').setValue(translation.short_description ?? '');
    this.translationControl(translation.locale, 'description').setValue(translation.description ?? '');
    this.translationControl(translation.locale, 'benefits').setValue(translation.benefits ?? '');
    this.translationControl(translation.locale, 'usage').setValue(translation.usage_instructions ?? '');
    this.translationControl(translation.locale, 'meta_title').setValue(translation.meta_title ?? '');
    this.translationControl(translation.locale, 'meta_description').setValue(translation.meta_description ?? '');
  }

  private payload(): ProductPayload {
    const value = this.form.getRawValue();
    return {
      sku: value.sku, code: value.code || null, status: value.status, category_id: value.category_id,
      brand_id: value.brand_id, origin: value.origin || null, packaging: value.packaging || null,
      is_featured: value.is_featured, published_at: this.toIso(value.published_at), unpublished_at: this.toIso(value.unpublished_at),
      price: {
        mode: value.price_mode, amount: value.price_amount || null, minimum: value.price_minimum || null,
        maximum: value.price_maximum || null, currency: value.currency.toUpperCase(), unit: value.price_unit || null,
        note: value.price_note || null, visible: value.price_visible,
      },
      translations: this.locales.map((locale) => this.translationPayload(locale)),
      media: this.media().map((item) => ({ media_id: item.media_id, role: item.role, locale: item.locale, is_primary: item.is_primary, sort_order: item.sort_order, alt_text: item.alt_text })),
      tag_ids: value.tag_ids,
      attributes: this.attributePayload(),
      specifications: this.specifications().filter((item) => item.label.trim() !== '' && item.value.trim() !== ''),
      related_product_ids: value.related_product_ids,
    };
  }

  private translationPayload(locale: AdminContentLocale): ProductTranslation {
    const get = (field: Parameters<ProductEditorDialog['translationControl']>[1]) => this.translationControl(locale, field).value.trim();
    return {
      locale, name: get('name'), slug: get('slug'), short_description: get('short') || null,
      description: get('description') || null, benefits: get('benefits') || null,
      usage_instructions: get('usage') || null, meta_title: get('meta_title') || null,
      meta_description: get('meta_description') || null,
    };
  }

  private attributePayload(): readonly Omit<ProductAttributeValue, 'definition_name'>[] {
    return this.data.attributes.flatMap((attribute) => {
      const value = this.attributeValue(attribute).trim();
      if (value === '') return [];
      return [{
        definition_id: attribute.public_id,
        locale: '*',
        value_text: ['text', 'option', 'json'].includes(attribute.data_type) ? value : null,
        value_decimal: attribute.data_type === 'decimal' ? value : null,
        value_boolean: attribute.data_type === 'boolean' ? value === 'true' : null,
        value_json: null,
      }];
    });
  }

  private initialAttributeValues(): Readonly<Record<string, string>> {
    const values: Record<string, string> = {};
    for (const attribute of this.data.product?.attributes ?? []) {
      values[attribute.definition_id] = attribute.value_text ?? attribute.value_decimal ?? (attribute.value_boolean === null ? '' : String(attribute.value_boolean));
    }
    return values;
  }

  private updateMedia(mediaId: string, changes: Partial<ProductMedia>): void {
    this.media.update((items) => items.map((item) => item.media_id === mediaId ? { ...item, ...changes } : item));
  }

  private toDateTimeLocal(value: string | null): string | null {
    if (!value) return null;
    const date = new Date(value);
    const offset = date.getTimezoneOffset() * 60_000;
    return new Date(date.getTime() - offset).toISOString().slice(0, 16);
  }

  private toIso(value: string | null): string | null {
    return value ? new Date(value).toISOString() : null;
  }
}
