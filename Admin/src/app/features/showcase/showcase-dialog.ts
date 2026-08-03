import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormControl, NonNullableFormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatTabsModule } from '@angular/material/tabs';

import { I18nService } from '../../core/i18n/i18n.service';
import { MediaPickerService } from '../media/media-picker.service';
import { SHOWCASE_TRANSLATIONS } from './showcase.i18n';
import { ShowcaseDialogData, ShowcaseLocale, ShowcaseMedia, ShowcaseStatus } from './showcase.models';

type LocalField = 'name' | 'title' | 'slug' | 'description' | 'content' | 'location' | 'issuer' | 'alt' | 'caption' | 'document_label' | 'meta_title' | 'meta_description';

@Component({
  selector: 'hv-showcase-dialog',
  imports: [ReactiveFormsModule, MatButtonModule, MatCheckboxModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSelectModule, MatTabsModule],
  providers: [MediaPickerService],
  templateUrl: './showcase-dialog.html',
  styleUrl: './showcase-dialog.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ShowcaseDialog {
  readonly data = inject<ShowcaseDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<ShowcaseDialog, unknown>);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly picker = inject(MediaPickerService);
  readonly locales: readonly ShowcaseLocale[] = ['vi', 'en', 'zh'];
  readonly statuses: readonly ShowcaseStatus[] = ['draft', 'published', 'archived'];
  readonly primaryMedia = signal<ShowcaseMedia | null>(this.data.item?.media ?? this.data.item?.logo_media ?? this.data.item?.image_media ?? null);
  readonly documentMedia = signal<ShowcaseMedia | null>(this.data.item?.document_media ?? null);
  readonly projectMedia = signal<readonly ShowcaseMedia[]>(this.data.item?.media_items?.map((item) => item.media) ?? []);
  readonly form = this.fb.group({
    code: '', gallery_id: '', status: this.fb.control<ShowcaseStatus>('draft'), is_featured: false, sort_order: 0,
    website_url: '', issued_on: '', expires_on: '', started_on: '', completed_on: '', document_visibility: this.fb.control<'private' | 'public'>('private'),
    vi_name: '', vi_title: '', vi_slug: '', vi_description: '', vi_content: '', vi_location: '', vi_issuer: '', vi_alt: '', vi_caption: '', vi_document_label: '', vi_meta_title: '', vi_meta_description: '',
    en_name: '', en_title: '', en_slug: '', en_description: '', en_content: '', en_location: '', en_issuer: '', en_alt: '', en_caption: '', en_document_label: '', en_meta_title: '', en_meta_description: '',
    zh_name: '', zh_title: '', zh_slug: '', zh_description: '', zh_content: '', zh_location: '', zh_issuer: '', zh_alt: '', zh_caption: '', zh_document_label: '', zh_meta_title: '', zh_meta_description: '',
  });

  constructor() { this.configure(); this.patch(); }

  text(key: string): string { return SHOWCASE_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  languageLabel(locale: ShowcaseLocale): string { return this.text(locale === 'vi' ? 'languageVi' : locale === 'en' ? 'languageEn' : 'languageZh'); }
  control(locale: ShowcaseLocale, field: LocalField): FormControl<string> { return this.form.get(`${locale}_${field}`) as FormControl<string>; }
  galleryName(item: { readonly translations: readonly { readonly locale: ShowcaseLocale; readonly name?: string | null }[] }): string { return item.translations.find((t) => t.locale === this.i18n.locale())?.name ?? item.translations.find((t) => t.locale === 'vi')?.name ?? '—'; }
  usesCode(): boolean { return this.data.kind !== 'gallery-items'; }
  usesSlug(): boolean { return ['galleries', 'certifications', 'projects'].includes(this.data.kind); }
  nameField(): boolean { return ['galleries', 'partners', 'certifications'].includes(this.data.kind); }

  choosePrimary(): void {
    const accepted = this.data.kind === 'gallery-items' ? ['image/jpeg', 'image/png', 'image/webp'] : ['image/jpeg', 'image/png', 'image/webp'];
    this.picker.open({ multiple: false, acceptedMimeTypes: accepted, selectedIds: this.primaryMedia() ? [this.primaryMedia()!.public_id] : [] }).subscribe((result) => {
      const item = result?.items[0]; if (item) this.primaryMedia.set({ public_id: item.public_id, file_name: item.original_filename, mime_type: item.mime_type });
    });
  }
  chooseDocument(): void { this.picker.open({ multiple: false, acceptedMimeTypes: ['application/pdf'], selectedIds: this.documentMedia() ? [this.documentMedia()!.public_id] : [] }).subscribe((result) => { const item = result?.items[0]; if (item) this.documentMedia.set({ public_id: item.public_id, file_name: item.original_filename, mime_type: item.mime_type }); }); }
  chooseProjectMedia(): void { this.picker.open({ multiple: true, acceptedMimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], selectedIds: this.projectMedia().map((item) => item.public_id) }).subscribe((result) => { if (result) this.projectMedia.set(result.items.map((item) => ({ public_id: item.public_id, file_name: item.original_filename, mime_type: item.mime_type }))); }); }

  submit(): void {
    if (this.form.invalid || (this.data.kind === 'gallery-items' && !this.primaryMedia())) { this.form.markAllAsTouched(); return; }
    const raw = this.form.getRawValue();
    const common = { status: raw.status, is_featured: raw.is_featured, sort_order: raw.sort_order };
    const translations = this.locales.map((locale) => this.translation(locale));
    const payload = this.data.kind === 'gallery-items' ? { ...common, gallery_id: raw.gallery_id, media_id: this.primaryMedia()?.public_id ?? null, translations }
      : this.data.kind === 'partners' ? { ...common, code: raw.code.trim(), website_url: this.nullable(raw.website_url), logo_media_id: this.primaryMedia()?.public_id ?? null, translations }
      : this.data.kind === 'certifications' ? { ...common, code: raw.code.trim(), image_media_id: this.primaryMedia()?.public_id ?? null, document_media_id: this.documentMedia()?.public_id ?? null, document_visibility: raw.document_visibility, issued_on: this.nullable(raw.issued_on), expires_on: this.nullable(raw.expires_on), translations }
      : this.data.kind === 'projects' ? { ...common, code: raw.code.trim(), started_on: this.nullable(raw.started_on), completed_on: this.nullable(raw.completed_on), translations, media_items: this.projectMedia().map((media, index) => ({ media_id: media.public_id, role: media.mime_type === 'application/pdf' ? 'document' : index === 0 ? 'cover' : 'gallery', sort_order: index, translations: this.locales.map((locale) => ({ locale, alt_text: this.control(locale, 'title').value.trim() || media.file_name, caption: null })) })) }
      : { ...common, code: raw.code.trim(), translations };
    this.dialogRef.close(payload);
  }

  private configure(): void {
    if (this.usesCode()) this.form.controls.code.addValidators([Validators.required, Validators.maxLength(100)]);
    if (this.data.kind === 'gallery-items') this.form.controls.gallery_id.addValidators(Validators.required);
    for (const locale of this.locales) {
      this.control(locale, this.nameField() ? 'name' : 'title').addValidators(Validators.required);
      if (this.usesSlug()) this.control(locale, 'slug').addValidators([Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]);
      if (this.data.kind === 'gallery-items' || this.data.kind === 'partners') this.control(locale, 'alt').addValidators(Validators.required);
    }
  }
  private patch(): void {
    const item = this.data.item; if (!item) return;
    this.form.patchValue({ code: item.code ?? '', gallery_id: item.gallery_id ?? '', status: item.status, is_featured: item.is_featured, sort_order: item.sort_order, website_url: item.website_url ?? '', issued_on: item.issued_on ?? '', expires_on: item.expires_on ?? '', started_on: item.started_on ?? '', completed_on: item.completed_on ?? '', document_visibility: item.document_visibility ?? 'private' });
    for (const t of item.translations) this.form.patchValue({ [`${t.locale}_name`]: t.name ?? '', [`${t.locale}_title`]: t.title ?? '', [`${t.locale}_slug`]: t.slug ?? '', [`${t.locale}_description`]: t.description ?? t.summary ?? '', [`${t.locale}_content`]: t.content ?? '', [`${t.locale}_location`]: t.location ?? '', [`${t.locale}_issuer`]: t.issuer ?? '', [`${t.locale}_alt`]: t.alt_text ?? t.logo_alt ?? t.image_alt ?? '', [`${t.locale}_caption`]: t.caption ?? '', [`${t.locale}_document_label`]: t.document_label ?? '', [`${t.locale}_meta_title`]: t.meta_title ?? '', [`${t.locale}_meta_description`]: t.meta_description ?? '' });
  }
  private translation(locale: ShowcaseLocale): Record<string, string | null> {
    const base = { locale };
    if (this.data.kind === 'gallery-items') return { ...base, title: this.nullable(this.control(locale, 'title').value), caption: this.nullable(this.control(locale, 'caption').value), alt_text: this.control(locale, 'alt').value.trim() };
    if (this.data.kind === 'partners') return { ...base, name: this.control(locale, 'name').value.trim(), description: this.nullable(this.control(locale, 'description').value), logo_alt: this.control(locale, 'alt').value.trim() };
    if (this.data.kind === 'certifications') return { ...base, name: this.control(locale, 'name').value.trim(), slug: this.control(locale, 'slug').value.trim(), issuer: this.nullable(this.control(locale, 'issuer').value), description: this.nullable(this.control(locale, 'description').value), image_alt: this.nullable(this.control(locale, 'alt').value), document_label: this.nullable(this.control(locale, 'document_label').value) };
    if (this.data.kind === 'projects') return { ...base, title: this.control(locale, 'title').value.trim(), slug: this.control(locale, 'slug').value.trim(), summary: this.nullable(this.control(locale, 'description').value), content: this.nullable(this.control(locale, 'content').value), location: this.nullable(this.control(locale, 'location').value), meta_title: this.nullable(this.control(locale, 'meta_title').value), meta_description: this.nullable(this.control(locale, 'meta_description').value) };
    return { ...base, name: this.control(locale, 'name').value.trim(), slug: this.control(locale, 'slug').value.trim(), description: this.nullable(this.control(locale, 'description').value), meta_title: this.nullable(this.control(locale, 'meta_title').value), meta_description: this.nullable(this.control(locale, 'meta_description').value) };
  }
  private nullable(value: string): string | null { return value.trim() || null; }
}
