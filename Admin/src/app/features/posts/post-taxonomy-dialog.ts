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
import { POST_TRANSLATIONS } from './post.i18n';
import { PostDialogResult, PostLocale, PostTaxonomyDialogData } from './post.models';

type TaxonomyField = 'name' | 'slug' | 'description' | 'meta_title' | 'meta_description';

@Component({
  selector: 'hv-post-taxonomy-dialog',
  imports: [ReactiveFormsModule, MatButtonModule, MatDialogModule, MatFormFieldModule, MatInputModule, MatSelectModule, MatSlideToggleModule, MatTabsModule],
  templateUrl: './post-taxonomy-dialog.html',
  styleUrl: './post-dialog.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PostTaxonomyDialog {
  readonly data = inject<PostTaxonomyDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<PostTaxonomyDialog, PostDialogResult>);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  readonly locales: readonly PostLocale[] = ['vi', 'en', 'zh'];
  readonly form = this.fb.group({
    code: ['', [Validators.required, Validators.maxLength(64)]], parent_id: new FormControl<string | null>(null), is_active: true, sort_order: [0, [Validators.required, Validators.min(0)]],
    vi_name: ['', Validators.required], vi_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], vi_description: '', vi_meta_title: '', vi_meta_description: '',
    en_name: ['', Validators.required], en_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], en_description: '', en_meta_title: '', en_meta_description: '',
    zh_name: ['', Validators.required], zh_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], zh_description: '', zh_meta_title: '', zh_meta_description: '',
  });

  constructor() { this.patch(); }

  text(key: string): string { return POST_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  heading(): string { return this.text(`${this.data.item ? 'edit' : 'create'}${this.data.kind === 'category' ? 'Category' : 'Tag'}`); }
  languageLabel(locale: PostLocale): string { return this.text(locale === 'vi' ? 'languageVi' : locale === 'en' ? 'languageEn' : 'languageZh'); }
  categoryName(publicId: string): string { return this.localized(this.data.categories.find((item) => item.public_id === publicId)?.translations ?? []); }
  control(locale: PostLocale, field: TaxonomyField): FormControl<string> { return this.form.controls[`${locale}_${field}`]; }

  submit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    const raw = this.form.getRawValue();
    const translations = this.locales.map((locale) => ({
      locale, name: this.control(locale, 'name').value.trim(), slug: this.control(locale, 'slug').value.trim(),
      ...(this.data.kind === 'category' ? {
        description: this.nullable(this.control(locale, 'description').value), meta_title: this.nullable(this.control(locale, 'meta_title').value), meta_description: this.nullable(this.control(locale, 'meta_description').value),
      } : {}),
    }));
    this.dialogRef.close({ publicId: this.data.item?.public_id ?? null, payload: {
      ...(this.data.kind === 'category' ? { parent_id: raw.parent_id } : {}), code: raw.code.trim(), is_active: raw.is_active, sort_order: raw.sort_order, translations,
    } });
  }

  private patch(): void {
    const item = this.data.item;
    if (!item) return;
    this.form.patchValue({ code: item.code, parent_id: item.parent_id, is_active: item.is_active, sort_order: item.sort_order });
    for (const translation of item.translations) this.form.patchValue({
      [`${translation.locale}_name`]: translation.name, [`${translation.locale}_slug`]: translation.slug, [`${translation.locale}_description`]: translation.description ?? '',
      [`${translation.locale}_meta_title`]: translation.meta_title ?? '', [`${translation.locale}_meta_description`]: translation.meta_description ?? '',
    });
  }

  private localized(items: readonly { readonly locale: PostLocale; readonly name: string }[]): string { const locale = this.i18n.locale(); return items.find((item) => item.locale === locale)?.name ?? items.find((item) => item.locale === 'vi')?.name ?? items[0]?.name ?? '—'; }
  private nullable(value: string): string | null { return value.trim() || null; }
}
