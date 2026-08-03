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
import { CROP_SOLUTION_TRANSLATIONS } from './crop-solution.i18n';
import { CropLocale, CropSolutionDialogData, CropSolutionStatus } from './crop-solution.models';

@Component({
  selector: 'hv-crop-solution-dialog',
  imports: [ReactiveFormsModule, MatButtonModule, MatCheckboxModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSelectModule, MatTabsModule],
  providers: [MediaPickerService],
  templateUrl: './crop-solution-dialog.html',
  styleUrl: './crop-solution-dialog.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CropSolutionDialog {
  readonly data = inject<CropSolutionDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<CropSolutionDialog, unknown>);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly mediaPicker = inject(MediaPickerService);

  readonly locales: readonly CropLocale[] = ['vi', 'en', 'zh'];
  readonly statuses: readonly CropSolutionStatus[] = ['draft', 'published', 'scheduled', 'archived'];
  readonly heroMediaId = signal<string | null>(this.data.solution?.hero_media_id ?? null);
  readonly form = this.fb.group({
    crop_id: ['', Validators.required], stage_id: new FormControl<string | null>(null), code: ['', Validators.required],
    status: this.fb.control<CropSolutionStatus>('draft'), is_featured: false, sort_order: [0, Validators.min(0)],
    product_ids: this.fb.control<readonly string[]>([]), published_at: new FormControl<string | null>(null), unpublished_at: new FormControl<string | null>(null),
    vi_title: ['', Validators.required], vi_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], vi_summary: '', vi_content: '', vi_sections: '', vi_meta_title: '', vi_meta_description: '',
    en_title: ['', Validators.required], en_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], en_summary: '', en_content: '', en_sections: '', en_meta_title: '', en_meta_description: '',
    zh_title: ['', Validators.required], zh_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], zh_summary: '', zh_content: '', zh_sections: '', zh_meta_title: '', zh_meta_description: '',
  });
  constructor() { this.patchSolution(); }

  text(key: string): string { return CROP_SOLUTION_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  languageLabel(locale: CropLocale): string { return this.text(locale === 'vi' ? 'languageVi' : locale === 'en' ? 'languageEn' : 'languageZh'); }
  control(locale: CropLocale, field: 'title' | 'slug' | 'summary' | 'content' | 'sections' | 'meta_title' | 'meta_description'): FormControl<string> { return this.form.controls[`${locale}_${field}`]; }
  cropName(publicId: string): string { return this.localized(this.data.crops.find((crop) => crop.public_id === publicId)?.translations ?? [], 'name'); }
  stageName(publicId: string): string { return this.localized(this.data.stages.find((stage) => stage.public_id === publicId)?.translations ?? [], 'name'); }
  productName(publicId: string): string { const product = this.data.products.find((item) => item.public_id === publicId); return `${this.localized(product?.translations ?? [], 'name')} · ${product?.sku ?? ''}`; }

  chooseHero(): void {
    this.mediaPicker.open({ multiple: false, acceptedMimeTypes: ['image/*'], selectedIds: this.heroMediaId() ? [this.heroMediaId()!] : [] })
      .subscribe((result) => this.heroMediaId.set(result?.items[0]?.public_id ?? this.heroMediaId()));
  }

  submit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    const raw = this.form.getRawValue();
    this.dialogRef.close({
      crop_id: raw.crop_id, stage_id: raw.stage_id, code: raw.code.trim(), status: raw.status,
      hero_media_id: this.heroMediaId(), is_featured: raw.is_featured, sort_order: raw.sort_order,
      published_at: this.iso(raw.published_at), unpublished_at: this.iso(raw.unpublished_at),
      translations: this.locales.map((locale) => ({
        locale, title: this.control(locale, 'title').value.trim(), slug: this.control(locale, 'slug').value.trim(),
        summary: this.nullable(this.control(locale, 'summary').value), content: this.nullable(this.control(locale, 'content').value),
        content_sections: this.sections(this.control(locale, 'sections').value),
        meta_title: this.nullable(this.control(locale, 'meta_title').value), meta_description: this.nullable(this.control(locale, 'meta_description').value),
      })),
      products: raw.product_ids.map((productId, sortOrder) => ({ product_id: productId, sort_order: sortOrder, recommendation_note: null })),
    });
  }

  private patchSolution(): void {
    const solution = this.data.solution;
    if (!solution) return;
    this.form.patchValue({
      crop_id: solution.crop.public_id, stage_id: solution.stage?.public_id ?? null, code: solution.code, status: solution.status,
      is_featured: solution.is_featured, sort_order: solution.sort_order, product_ids: solution.products.map((product) => product.public_id),
      published_at: this.localDate(solution.published_at), unpublished_at: this.localDate(solution.unpublished_at),
    });
    for (const translation of solution.translations) {
      const locale = translation.locale;
      this.form.patchValue({
        [`${locale}_title`]: translation.title, [`${locale}_slug`]: translation.slug, [`${locale}_summary`]: translation.summary ?? '',
        [`${locale}_content`]: translation.content ?? '', [`${locale}_sections`]: translation.content_sections.map((section) => `${section.title} | ${section.body}`).join('\n'),
        [`${locale}_meta_title`]: translation.meta_title ?? '', [`${locale}_meta_description`]: translation.meta_description ?? '',
      });
    }
  }

  private localized(items: readonly { readonly locale: CropLocale; readonly name?: string; readonly title?: string }[], field: 'name' | 'title'): string {
    const locale = this.i18n.locale();
    const item = items.find((value) => value['locale'] === locale) ?? items.find((value) => value['locale'] === 'vi') ?? items[0];
    return item?.[field] ?? '—';
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
