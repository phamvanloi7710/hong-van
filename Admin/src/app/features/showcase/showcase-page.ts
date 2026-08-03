import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { NgTemplateOutlet } from '@angular/common';
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
import { ShowcaseDataService } from './showcase-data.service';
import { ShowcaseDialog } from './showcase-dialog';
import { SHOWCASE_TRANSLATIONS } from './showcase.i18n';
import { ShowcaseFilters, ShowcaseItem, ShowcaseKind } from './showcase.models';

@Component({
  selector: 'hv-showcase-page',
  imports: [NgTemplateOutlet, ReactiveFormsModule, MatButtonModule, MatCardModule, MatChipsModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule, MatSelectModule, MatTabsModule, MatTooltipModule],
  templateUrl: './showcase-page.html',
  styleUrl: './showcase-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ShowcasePage {
  private readonly data = inject(ShowcaseDataService); private readonly dialog = inject(MatDialog); private readonly fb = inject(NonNullableFormBuilder); private readonly i18n = inject(I18nService); private readonly snack = inject(MatSnackBar);
  readonly authStore = inject(AuthStore); readonly loading = signal(true); readonly saving = signal(false); readonly error = signal<string | null>(null);
  readonly galleries = signal<readonly ShowcaseItem[]>([]); readonly galleryItems = signal<readonly ShowcaseItem[]>([]); readonly partners = signal<readonly ShowcaseItem[]>([]); readonly certifications = signal<readonly ShowcaseItem[]>([]); readonly projects = signal<readonly ShowcaseItem[]>([]);
  readonly filters = this.fb.group({ search: '', status: '', trashed: 'without' }); readonly statuses = ['draft', 'published', 'archived'] as const;
  constructor() { this.reload(); }
  text(key: string): string { return SHOWCASE_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  localized(item: ShowcaseItem): string { const translation = item.translations.find((t) => t.locale === this.i18n.locale()) ?? item.translations.find((t) => t.locale === 'vi') ?? item.translations[0]; return translation?.name ?? translation?.title ?? '—'; }
  icon(kind: ShowcaseKind): string { return kind === 'galleries' ? 'photo_library' : kind === 'gallery-items' ? 'image' : kind === 'partners' ? 'handshake' : kind === 'certifications' ? 'workspace_premium' : 'business_center'; }
  reload(): void {
    this.loading.set(true); this.error.set(null); const filters = this.filters.getRawValue() as ShowcaseFilters;
    forkJoin({ galleries: this.data.list('galleries', filters), items: this.data.list('gallery-items', filters), partners: this.data.list('partners', filters), certifications: this.data.list('certifications', filters), projects: this.data.list('projects', filters) }).pipe(finalize(() => this.loading.set(false))).subscribe({ next: (result) => { this.galleries.set(result.galleries.items); this.galleryItems.set(result.items.items); this.partners.set(result.partners.items); this.certifications.set(result.certifications.items); this.projects.set(result.projects.items); }, error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))) });
  }
  clear(): void { this.filters.reset({ search: '', status: '', trashed: 'without' }); this.reload(); }
  open(kind: ShowcaseKind, item: ShowcaseItem | null = null): void { this.dialog.open(ShowcaseDialog, { data: { kind, item, galleries: this.galleries().filter((gallery) => !gallery.deleted_at) }, width: '1120px', maxWidth: '98vw', maxHeight: '96vh', disableClose: true }).afterClosed().subscribe((payload) => { if (payload) this.run(this.data.save(kind, item?.public_id ?? null, payload), this.text(item ? 'updated' : 'created')); }); }
  publish(kind: ShowcaseKind, item: ShowcaseItem): void { this.run(this.data.publish(kind, item.public_id), this.text('publishedMessage')); }
  archive(kind: ShowcaseKind, item: ShowcaseItem): void { this.run(this.data.archive(kind, item.public_id), this.text('archivedMessage')); }
  restore(kind: ShowcaseKind, item: ShowcaseItem): void { this.run(this.data.restore(kind, item.public_id), this.text('restoredMessage')); }
  remove(kind: ShowcaseKind, item: ShowcaseItem): void { if (window.confirm(this.text('confirmDelete'))) this.run(this.data.delete(kind, item.public_id), this.text('deleted')); }
  private run(request: Observable<unknown>, message: string): void { this.saving.set(true); request.pipe(finalize(() => this.saving.set(false))).subscribe({ next: () => { this.snack.open(message, this.text('close'), { duration: 3000 }); this.reload(); }, error: (error: unknown) => this.snack.open(authErrorMessage(error, this.text('operationError')), this.text('close'), { duration: 5000 }) }); }
}
