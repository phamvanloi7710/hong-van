import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { NonNullableFormBuilder, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatDialog } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatTabsModule } from '@angular/material/tabs';
import { MatTooltipModule } from '@angular/material/tooltip';
import { finalize, forkJoin, Observable } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { AuthStore } from '../../core/auth/auth.store';
import { I18nService } from '../../core/i18n/i18n.service';
import { PostDataService } from './post-data.service';
import { PostDialog } from './post-dialog';
import { POST_TRANSLATIONS } from './post.i18n';
import { PostAuthor, PostDialogResult, PostFilters, PostItem, PostLocale, PostTaxonomyItem } from './post.models';
import { PostTaxonomyDialog } from './post-taxonomy-dialog';

@Component({
  selector: 'hv-post-page',
  imports: [ReactiveFormsModule, MatButtonModule, MatCardModule, MatChipsModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule, MatSelectModule, MatTabsModule, MatTooltipModule],
  templateUrl: './post-page.html',
  styleUrl: './post-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PostPage {
  private readonly data = inject(PostDataService);
  private readonly dialog = inject(MatDialog);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);
  readonly authStore = inject(AuthStore);
  readonly posts = signal<readonly PostItem[]>([]);
  readonly authors = signal<readonly PostAuthor[]>([]);
  readonly categories = signal<readonly PostTaxonomyItem[]>([]);
  readonly tags = signal<readonly PostTaxonomyItem[]>([]);
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly error = signal<string | null>(null);
  readonly filters = this.fb.group({ search: '', status: '', category_id: '', trashed: 'without' });
  readonly statuses = ['draft', 'scheduled', 'published', 'archived'] as const;

  constructor() { this.reloadAll(); }

  text(key: string): string { return POST_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  localizedTitle(post: PostItem): string { const locale = this.i18n.locale(); return post.translations.find((item) => item.locale === locale)?.title ?? post.translations.find((item) => item.locale === 'vi')?.title ?? post.translations[0]?.title ?? '—'; }
  localizedName(item: PostTaxonomyItem | { readonly translations: readonly { readonly locale: PostLocale; readonly name: string }[] }): string { const locale = this.i18n.locale(); return item.translations.find((translation) => translation.locale === locale)?.name ?? item.translations.find((translation) => translation.locale === 'vi')?.name ?? item.translations[0]?.name ?? '—'; }
  categoryName(post: PostItem): string { return post.category ? this.localizedName(post.category) : this.text('noSelection'); }
  displayDate(value: string | null): string { return value ? new Intl.DateTimeFormat(this.i18n.locale(), { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '—'; }

  reloadAll(): void {
    this.loading.set(true); this.error.set(null);
    forkJoin({ page: this.data.list(this.filters.getRawValue() as PostFilters), authors: this.data.authors(), categories: this.data.categories(), tags: this.data.tags() })
      .pipe(finalize(() => this.loading.set(false))).subscribe({
        next: ({ page, authors, categories, tags }) => { this.posts.set(page.items); this.authors.set(authors); this.categories.set(categories); this.tags.set(tags); },
        error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
      });
  }

  clearFilters(): void { this.filters.reset({ search: '', status: '', category_id: '', trashed: 'without' }); this.reloadAll(); }
  openPost(post: PostItem | null = null): void {
    this.dialog.open<PostDialog, { post: PostItem | null; authors: readonly PostAuthor[]; categories: readonly PostTaxonomyItem[]; tags: readonly PostTaxonomyItem[] }, unknown>(PostDialog, {
      data: { post, authors: this.authors(), categories: this.categories(), tags: this.tags() }, width: '1220px', maxWidth: '98vw', maxHeight: '96vh', disableClose: true,
    }).afterClosed().subscribe((payload) => { if (payload) this.run(this.data.savePost(post?.public_id ?? null, payload), this.text(post ? 'updated' : 'created')); });
  }
  openTaxonomy(kind: 'category' | 'tag', item: PostTaxonomyItem | null = null): void {
    this.dialog.open<PostTaxonomyDialog, { kind: 'category' | 'tag'; item: PostTaxonomyItem | null; categories: readonly PostTaxonomyItem[] }, PostDialogResult>(PostTaxonomyDialog, {
      data: { kind, item, categories: this.categories() }, width: '950px', maxWidth: '96vw', maxHeight: '94vh',
    }).afterClosed().subscribe((result) => { if (result) this.run(this.data.saveTaxonomy(kind, result.publicId, result.payload), this.text(result.publicId ? 'updated' : 'created')); });
  }
  publish(post: PostItem): void { this.run(this.data.publish(post.public_id), this.text('publishedMessage')); }
  archive(post: PostItem): void { this.run(this.data.archive(post.public_id), this.text('archivedMessage')); }
  restore(post: PostItem): void { this.run(this.data.restore(post.public_id), this.text('restoredMessage')); }
  deletePost(post: PostItem): void { if (window.confirm(this.text('confirmDelete'))) this.run(this.data.deletePost(post.public_id), this.text('deleted')); }
  deleteTaxonomy(kind: 'category' | 'tag', item: PostTaxonomyItem): void { if (window.confirm(this.text('confirmDelete'))) this.run(this.data.deleteTaxonomy(kind, item.public_id), this.text('deleted')); }

  private run(request: Observable<unknown>, message: string): void {
    this.saving.set(true); request.pipe(finalize(() => this.saving.set(false))).subscribe({
      next: () => { this.snackBar.open(message, this.text('close'), { duration: 3000 }); this.reloadAll(); },
      error: (error: unknown) => this.snackBar.open(authErrorMessage(error, this.text('operationError')), this.text('close'), { duration: 5000 }),
    });
  }
}
