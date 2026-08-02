import { ChangeDetectionStrategy, Component, DestroyRef, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar } from '@angular/material/snack-bar';
import { finalize, forkJoin, Observable } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { AuthStore } from '../../core/auth/auth.store';
import { I18nService } from '../../core/i18n/i18n.service';
import { MediaPickerContract, MediaPickerItem } from '../../core/media/media-picker.contract';
import { MediaDataService } from './media-data.service';
import { MEDIA_TRANSLATIONS } from './media.i18n';
import { MediaFilters, MediaFolder, MediaItem, MediaPagination, MediaUploadState } from './media.models';
import { MediaPickerService } from './media-picker.service';

@Component({
  selector: 'hv-media-page',
  imports: [ReactiveFormsModule, MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule, MatSelectModule],
  templateUrl: './media-page.html',
  styleUrl: './media-page.scss',
  providers: [MediaPickerService, { provide: MediaPickerContract, useExisting: MediaPickerService }],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MediaPage {
  private readonly data = inject(MediaDataService);
  private readonly picker = inject(MediaPickerContract);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);
  private readonly destroyRef = inject(DestroyRef);
  readonly authStore = inject(AuthStore);

  readonly filters = new FormGroup({
    search: new FormControl('', { nonNullable: true }),
    status: new FormControl('', { nonNullable: true }),
    folder_id: new FormControl('', { nonNullable: true }),
    trashed: new FormControl<'only' | 'without'>('without', { nonNullable: true }),
  });
  readonly items = signal<readonly MediaItem[]>([]);
  readonly folders = signal<readonly MediaFolder[]>([]);
  readonly pagination = signal<MediaPagination | null>(null);
  readonly uploads = signal<readonly MediaUploadState[]>([]);
  readonly picked = signal<readonly MediaPickerItem[]>([]);
  readonly loading = signal(true);
  readonly error = signal<string | null>(null);

  constructor() {
    this.load();
  }

  text(key: string): string {
    return MEDIA_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  interpolate(key: string, parameters: Readonly<Record<string, string>>): string {
    return Object.entries(parameters).reduce((value, [name, replacement]) => value.replaceAll(`{${name}}`, replacement), this.text(key));
  }

  load(page = 1): void {
    this.loading.set(true);
    this.error.set(null);
    forkJoin({ page: this.data.list(this.normalizedFilters(), page), folders: this.data.folders() })
      .pipe(finalize(() => this.loading.set(false)), takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: ({ page: result, folders }) => {
          this.items.set(result.items);
          this.pagination.set(result.pagination);
          this.folders.set(folders);
        },
        error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
      });
  }

  reset(): void {
    this.filters.reset({ search: '', status: '', folder_id: '', trashed: 'without' });
    this.load();
  }

  uploadFiles(event: Event): void {
    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files ?? []);
    input.value = '';

    files.forEach((file, index) => {
      const id = `${file.name}-${file.lastModified}-${index}`;
      this.uploads.update((items) => [...items, { id, name: file.name, status: 'uploading' }]);
      this.data.upload(file, this.filters.controls.folder_id.value || null)
        .pipe(takeUntilDestroyed(this.destroyRef))
        .subscribe({
          next: () => {
            this.updateUpload(id, 'success');
            this.snackBar.open(this.text('uploadSuccess'), this.text('cancel'), { duration: 2500 });
            this.load();
          },
          error: (error: unknown) => this.updateUpload(id, 'error', authErrorMessage(error, this.text('uploadFailed'))),
        });
    });
  }

  openPicker(): void {
    this.picker.open({ multiple: true, acceptedMimeTypes: ['image/*', 'application/pdf'], selectedIds: this.picked().map((item) => item.public_id) })
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe((result) => {
        if (result) {
          this.picked.set(result.items);
          this.snackBar.open(this.text('pickedDone'), this.text('cancel'), { duration: 2500 });
        }
      });
  }

  trash(item: MediaItem): void {
    if (!item.can_delete) {
      this.snackBar.open(this.text('inUse'), this.text('cancel'), { duration: 5000 });
      return;
    }
    if (confirm(this.text('confirmTrash'))) this.run(this.data.trash(item.public_id), this.text('trashedDone'));
  }

  restore(item: MediaItem): void {
    this.run(this.data.restore(item.public_id), this.text('restoredDone'));
  }

  deletePermanently(item: MediaItem): void {
    if (!item.can_delete) {
      this.snackBar.open(this.text('inUse'), this.text('cancel'), { duration: 5000 });
      return;
    }
    if (confirm(this.text('confirmDelete'))) this.run(this.data.delete(item.public_id), this.text('deletedDone'));
  }

  retry(item: MediaItem): void {
    this.run(this.data.retry(item.public_id), this.text('retryQueued'));
  }

  previous(): void {
    const page = this.pagination()?.page ?? 1;
    if (page > 1) this.load(page - 1);
  }

  next(): void {
    const pagination = this.pagination();
    if (pagination && pagination.page < pagination.last_page) this.load(pagination.page + 1);
  }

  thumbnail(item: MediaItem): string {
    return item.variants.find((variant) => variant.key.startsWith('thumbnail_'))?.content_url ?? item.content_url;
  }

  statusLabel(status: MediaItem['status']): string {
    return this.text(status in MEDIA_TRANSLATIONS[this.i18n.locale()] ? status : 'unknown');
  }

  fileSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / 1024 / 1024).toFixed(2)} MB`;
  }

  pickedNames(): string {
    return this.picked().map((item) => item.original_filename).join(', ');
  }

  private normalizedFilters(): MediaFilters {
    const value = this.filters.getRawValue();
    return {
      search: value.search.trim() || undefined,
      status: value.status || undefined,
      folder_id: value.folder_id || undefined,
      trashed: value.trashed,
    };
  }

  private updateUpload(id: string, status: MediaUploadState['status'], message?: string): void {
    this.uploads.update((items) => items.map((item) => item.id === id ? { ...item, status, message } : item));
  }

  private run(request: Observable<unknown>, message: string): void {
    request.pipe(takeUntilDestroyed(this.destroyRef)).subscribe({
      next: () => {
        this.snackBar.open(message, this.text('cancel'), { duration: 3000 });
        this.load();
      },
      error: (error: unknown) => this.snackBar.open(authErrorMessage(error, this.text('operationError')), this.text('cancel'), { duration: 5000 }),
    });
  }
}
