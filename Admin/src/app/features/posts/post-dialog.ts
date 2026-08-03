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
import { PostEditor } from './post-editor';
import { POST_TRANSLATIONS } from './post.i18n';
import { PostDialogData, PostLocale, PostStatus } from './post.models';

type LocalizedField = 'title' | 'slug' | 'excerpt' | 'content_html' | 'meta_title' | 'meta_description';

@Component({
  selector: 'hv-post-dialog',
  imports: [ReactiveFormsModule, MatButtonModule, MatCheckboxModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSelectModule, MatTabsModule, PostEditor],
  providers: [MediaPickerService],
  templateUrl: './post-dialog.html',
  styleUrl: './post-dialog.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PostDialog {
  readonly data = inject<PostDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<PostDialog, unknown>);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly mediaPicker = inject(MediaPickerService);
  readonly locales: readonly PostLocale[] = ['vi', 'en', 'zh'];
  readonly statuses: readonly PostStatus[] = ['draft', 'scheduled', 'published', 'archived'];
  readonly featuredMedia = signal<{ readonly id: string; readonly name: string } | null>(this.data.post?.featured_media ? { id: this.data.post.featured_media.public_id, name: this.data.post.featured_media.file_name } : null);
  readonly form = this.fb.group({
    code: ['', [Validators.required, Validators.maxLength(100)]], author_id: new FormControl<string | null>(null), category_id: new FormControl<string | null>(null), tag_ids: this.fb.control<readonly string[]>([]),
    status: this.fb.control<PostStatus>('draft'), is_featured: false, scheduled_for: new FormControl<string | null>(null), published_at: new FormControl<string | null>(null), unpublished_at: new FormControl<string | null>(null),
    vi_title: ['', Validators.required], vi_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], vi_excerpt: '', vi_content_html: ['', Validators.required], vi_meta_title: '', vi_meta_description: '',
    en_title: ['', Validators.required], en_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], en_excerpt: '', en_content_html: ['', Validators.required], en_meta_title: '', en_meta_description: '',
    zh_title: ['', Validators.required], zh_slug: ['', [Validators.required, Validators.pattern(/^[a-z0-9]+(?:-[a-z0-9]+)*$/)]], zh_excerpt: '', zh_content_html: ['', Validators.required], zh_meta_title: '', zh_meta_description: '',
  });

  constructor() { this.patch(); }

  text(key: string): string { return POST_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  languageLabel(locale: PostLocale): string { return this.text(locale === 'vi' ? 'languageVi' : locale === 'en' ? 'languageEn' : 'languageZh'); }
  categoryName(publicId: string): string { return this.localized(this.data.categories.find((item) => item.public_id === publicId)?.translations ?? []); }
  tagName(publicId: string): string { return this.localized(this.data.tags.find((item) => item.public_id === publicId)?.translations ?? []); }
  control(locale: PostLocale, field: LocalizedField): FormControl<string> { return this.form.controls[`${locale}_${field}`]; }

  chooseImage(): void {
    this.mediaPicker.open({ multiple: false, acceptedMimeTypes: ['image/jpeg', 'image/png', 'image/webp'], selectedIds: this.featuredMedia() ? [this.featuredMedia()!.id] : [] })
      .subscribe((result) => { const media = result?.items[0]; if (media) this.featuredMedia.set({ id: media.public_id, name: media.original_filename }); });
  }

  submit(): void {
    if (this.form.invalid || (this.form.controls.status.value === 'scheduled' && !this.form.controls.scheduled_for.value)) { this.form.markAllAsTouched(); return; }
    const raw = this.form.getRawValue();
    this.dialogRef.close({
      category_id: raw.category_id, author_id: raw.author_id, featured_media_id: this.featuredMedia()?.id ?? null, tag_ids: [...raw.tag_ids], code: raw.code.trim(), status: raw.status,
      is_featured: raw.is_featured, scheduled_for: raw.status === 'scheduled' ? this.utc(raw.scheduled_for) : null, published_at: this.utc(raw.published_at), unpublished_at: this.utc(raw.unpublished_at),
      translations: this.locales.map((locale) => ({
        locale, title: this.control(locale, 'title').value.trim(), slug: this.control(locale, 'slug').value.trim(), excerpt: this.nullable(this.control(locale, 'excerpt').value),
        content_html: this.control(locale, 'content_html').value, meta_title: this.nullable(this.control(locale, 'meta_title').value), meta_description: this.nullable(this.control(locale, 'meta_description').value),
      })),
    });
  }

  private patch(): void {
    const post = this.data.post;
    if (!post) return;
    this.form.patchValue({
      code: post.code, author_id: post.author?.public_id ?? null, category_id: post.category?.public_id ?? null, tag_ids: post.tags.map((tag) => tag.public_id), status: post.status, is_featured: post.is_featured,
      scheduled_for: this.local(post.scheduled_for), published_at: this.local(post.published_at), unpublished_at: this.local(post.unpublished_at),
    });
    for (const translation of post.translations) {
      this.form.patchValue({
        [`${translation.locale}_title`]: translation.title, [`${translation.locale}_slug`]: translation.slug, [`${translation.locale}_excerpt`]: translation.excerpt ?? '',
        [`${translation.locale}_content_html`]: translation.content_html, [`${translation.locale}_meta_title`]: translation.meta_title ?? '', [`${translation.locale}_meta_description`]: translation.meta_description ?? '',
      });
    }
  }

  private localized(items: readonly { readonly locale: PostLocale; readonly name: string }[]): string { const locale = this.i18n.locale(); return items.find((item) => item.locale === locale)?.name ?? items.find((item) => item.locale === 'vi')?.name ?? items[0]?.name ?? '—'; }
  private nullable(value: string): string | null { return value.trim() || null; }
  private local(value: string | null): string | null { return value ? value.slice(0, 16) : null; }
  private utc(value: string | null): string | null { return value ? new Date(value).toISOString() : null; }
}
