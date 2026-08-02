import { ChangeDetectionStrategy, Component, computed, DestroyRef, ElementRef, HostListener, inject, signal, ViewChild } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatMenuModule } from '@angular/material/menu';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatTooltipModule } from '@angular/material/tooltip';
import { finalize, forkJoin, Observable } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { AuthStore } from '../../core/auth/auth.store';
import { I18nService } from '../../core/i18n/i18n.service';
import { MediaPickerContract, MediaPickerItem } from '../../core/media/media-picker.contract';
import { MediaDataService } from './media-data.service';
import { MediaFolderDialog, MediaFolderDialogData } from './media-folder-dialog';
import { MEDIA_TRANSLATIONS } from './media.i18n';
import { MediaImageEditorDialog } from './media-image-editor-dialog';
import { MediaFilters, MediaFolder, MediaItem, MediaPagination, MediaUploadState } from './media.models';
import { MediaPickerService } from './media-picker.service';

interface FolderRow {
  readonly folder: MediaFolder;
  readonly depth: number;
}

@Component({
  selector: 'hv-media-page',
  imports: [ReactiveFormsModule, MatButtonModule, MatCardModule, MatCheckboxModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatMenuModule, MatProgressBarModule, MatProgressSpinnerModule, MatSelectModule, MatTooltipModule],
  templateUrl: './media-page.html',
  styleUrl: './media-page.scss',
  providers: [MediaPickerService, { provide: MediaPickerContract, useExisting: MediaPickerService }],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MediaPage {
  private readonly data = inject(MediaDataService);
  private readonly picker = inject(MediaPickerContract);
  private readonly dialog = inject(MatDialog);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);
  private readonly destroyRef = inject(DestroyRef);
  readonly authStore = inject(AuthStore);

  @ViewChild('fileInput') private readonly fileInput?: ElementRef<HTMLInputElement>;

  readonly filters = new FormGroup({
    search: new FormControl('', { nonNullable: true }),
    type: new FormControl('', { nonNullable: true }),
    status: new FormControl('', { nonNullable: true }),
    visibility: new FormControl('', { nonNullable: true }),
    trashed: new FormControl<'only' | 'without'>('without', { nonNullable: true }),
    sort: new FormControl<MediaFilters['sort']>('-created_at', { nonNullable: true }),
  });
  readonly detailForm = new FormGroup({
    title: new FormControl('', { nonNullable: true }),
    alt_text: new FormControl('', { nonNullable: true }),
    caption: new FormControl('', { nonNullable: true }),
  });
  readonly moveDestination = new FormControl('root', { nonNullable: true });
  readonly items = signal<readonly MediaItem[]>([]);
  readonly folders = signal<readonly MediaFolder[]>([]);
  readonly pagination = signal<MediaPagination | null>(null);
  readonly uploads = signal<readonly MediaUploadState[]>([]);
  readonly picked = signal<readonly MediaPickerItem[]>([]);
  readonly selectedIds = signal<ReadonlySet<string>>(new Set());
  readonly currentFolderId = signal<string | null>(null);
  readonly detail = signal<MediaItem | null>(null);
  readonly viewMode = signal<'grid' | 'list'>('grid');
  readonly uploadPanelOpen = signal(false);
  readonly dragActive = signal(false);
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly error = signal<string | null>(null);

  readonly selectedItems = computed(() => this.items().filter((item) => this.selectedIds().has(item.public_id)));
  readonly allSelected = computed(() => this.items().length > 0 && this.selectedIds().size === this.items().length);
  readonly currentFolder = computed(() => this.folders().find((folder) => folder.public_id === this.currentFolderId()) ?? null);
  readonly folderRows = computed(() => this.flattenFolders());
  readonly breadcrumbs = computed(() => {
    const chain: MediaFolder[] = [];
    const folders = this.folders();
    let current = this.currentFolder();
    const visited = new Set<string>();
    while (current && !visited.has(current.public_id)) {
      visited.add(current.public_id);
      chain.unshift(current);
      current = folders.find((folder) => folder.public_id === current?.parent_id) ?? null;
    }
    return chain;
  });

  private lastSelectedId: string | null = null;

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
          const visible = new Set(result.items.map((item) => item.public_id));
          this.selectedIds.update((selected) => new Set([...selected].filter((id) => visible.has(id))));
          if (this.detail() && !visible.has(this.detail()!.public_id)) this.detail.set(null);
        },
        error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
      });
  }

  reset(): void {
    this.filters.reset({ search: '', type: '', status: '', visibility: '', trashed: 'without', sort: '-created_at' });
    this.load();
  }

  navigateTo(folderId: string | null): void {
    this.currentFolderId.set(folderId);
    this.clearSelection();
    this.load();
  }

  navigateParent(): void {
    this.navigateTo(this.currentFolder()?.parent_id ?? null);
  }

  queueFiles(files: FileList | readonly File[] | null): void {
    if (!files || !this.authStore.hasPermission('media.create')) return;
    const queued = Array.from(files).map((file, index): MediaUploadState => ({
      id: `${file.name}-${file.lastModified}-${Date.now()}-${index}`,
      name: file.name,
      file,
      status: 'pending',
      progress: 0,
    }));
    this.uploads.update((items) => [...items, ...queued]);
    this.uploadPanelOpen.set(true);
  }

  fileInputChanged(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.queueFiles(input.files);
    input.value = '';
  }

  startUploads(): void {
    for (const upload of this.uploads().filter((item) => item.status === 'pending' || item.status === 'error')) {
      this.updateUpload(upload.id, { status: 'uploading', progress: 0, message: undefined });
      this.data.uploadWithProgress(upload.file, this.currentFolderId())
        .pipe(takeUntilDestroyed(this.destroyRef))
        .subscribe({
          next: (event) => {
            if (event.kind === 'progress') this.updateUpload(upload.id, { progress: event.progress });
            else {
              this.updateUpload(upload.id, { status: 'success', progress: 100 });
              this.load();
            }
          },
          error: (error: unknown) => this.updateUpload(upload.id, { status: 'error', message: authErrorMessage(error, this.text('uploadFailed')) }),
        });
    }
  }

  removeUpload(id: string): void {
    this.uploads.update((items) => items.filter((item) => item.id !== id || item.status === 'uploading'));
  }

  dragOver(event: DragEvent): void {
    event.preventDefault();
    if (this.authStore.hasPermission('media.create')) this.dragActive.set(true);
  }

  dragLeave(event: DragEvent): void {
    event.preventDefault();
    this.dragActive.set(false);
  }

  dropFiles(event: DragEvent): void {
    event.preventDefault();
    this.dragActive.set(false);
    this.queueFiles(event.dataTransfer?.files ?? null);
  }

  selectItem(item: MediaItem, event: MouseEvent): void {
    const ids = this.items().map((candidate) => candidate.public_id);
    const next = new Set(event.ctrlKey || event.metaKey || event.shiftKey ? this.selectedIds() : []);
    if (event.shiftKey && this.lastSelectedId && ids.includes(this.lastSelectedId)) {
      const start = ids.indexOf(this.lastSelectedId);
      const end = ids.indexOf(item.public_id);
      for (const id of ids.slice(Math.min(start, end), Math.max(start, end) + 1)) next.add(id);
    } else if ((event.ctrlKey || event.metaKey) && next.has(item.public_id)) {
      next.delete(item.public_id);
    } else {
      next.add(item.public_id);
    }
    this.lastSelectedId = item.public_id;
    this.selectedIds.set(next);
    this.loadDetail(item.public_id);
  }

  selectItemFromKeyboard(item: MediaItem, event: Event): void {
    event.preventDefault();
    this.selectedIds.set(new Set([item.public_id]));
    this.lastSelectedId = item.public_id;
    this.loadDetail(item.public_id);
  }

  toggleSelectAll(): void {
    this.selectedIds.set(this.allSelected() ? new Set() : new Set(this.items().map((item) => item.public_id)));
    if (this.allSelected() && this.items()[0]) this.loadDetail(this.items()[0].public_id);
    else this.detail.set(null);
  }

  clearSelection(): void {
    this.selectedIds.set(new Set());
    this.detail.set(null);
    this.lastSelectedId = null;
  }

  openPicker(): void {
    this.picker.open({ multiple: true, acceptedMimeTypes: ['image/*', 'application/pdf'], selectedIds: this.picked().map((item) => item.public_id) })
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe((result) => {
        if (result) {
          this.picked.set(result.items);
          this.snackBar.open(this.text('pickedDone'), this.text('close'), { duration: 2500 });
        }
      });
  }

  createFolder(): void {
    this.openFolderDialog({ mode: 'create' }).subscribe((name) => {
      if (name) this.run(this.data.createFolder(name, this.currentFolderId()), this.text('folderCreated'));
    });
  }

  renameCurrentFolder(): void {
    const folder = this.currentFolder();
    if (!folder || folder.is_locked) return;
    this.openFolderDialog({ mode: 'rename', name: folder.name }).subscribe((name) => {
      if (name) this.run(this.data.renameFolder(folder.public_id, name), this.text('folderRenamed'));
    });
  }

  toggleCurrentFolderLock(): void {
    const folder = this.currentFolder();
    if (folder) this.run(this.data.setFolderLock(folder.public_id, !folder.is_locked), this.text('lockUpdated'));
  }

  saveMetadata(): void {
    const item = this.detail();
    if (!item || item.is_locked) return;
    const value = this.detailForm.getRawValue();
    this.saving.set(true);
    this.data.update(item.public_id, {
      title: value.title.trim() || null,
      alt_text: value.alt_text.trim() || null,
      caption: value.caption.trim() || null,
    }).pipe(finalize(() => this.saving.set(false)), takeUntilDestroyed(this.destroyRef)).subscribe({
      next: (updated) => {
        this.replaceItem(updated);
        this.detail.set(updated);
        this.snackBar.open(this.text('metadataSaved'), this.text('close'), { duration: 2500 });
      },
      error: (error: unknown) => this.showError(error),
    });
  }

  moveSelected(): void {
    const destination = this.moveDestination.value === 'root' ? null : this.moveDestination.value;
    this.runBulk(this.selectedItems().map((item) => this.data.move(item.public_id, destination)), this.text('movedDone'));
  }

  setSelectedLock(locked: boolean): void {
    this.runBulk(this.selectedItems().map((item) => this.data.setLock(item.public_id, locked)), this.text('lockUpdated'));
  }

  setSelectedVisibility(visibility: 'private' | 'public'): void {
    this.runBulk(this.selectedItems().map((item) => this.data.setVisibility(item.public_id, visibility)), this.text('visibilityUpdated'));
  }

  trashSelected(): void {
    const items = this.selectedItems().filter((item) => !item.deleted_at && item.can_delete && !item.is_locked);
    if (items.length && confirm(this.interpolate('confirmTrashMany', { count: items.length.toString() }))) {
      this.runBulk(items.map((item) => this.data.trash(item.public_id)), this.text('trashedDone'));
    }
  }

  restoreSelected(): void {
    const items = this.selectedItems().filter((item) => !!item.deleted_at);
    this.runBulk(items.map((item) => this.data.restore(item.public_id)), this.text('restoredDone'));
  }

  deletePermanently(item: MediaItem): void {
    if (!item.can_delete) {
      this.snackBar.open(this.text('inUse'), this.text('close'), { duration: 5000 });
      return;
    }
    if (confirm(this.text('confirmDelete'))) this.run(this.data.delete(item.public_id), this.text('deletedDone'));
  }

  retry(item: MediaItem): void {
    this.run(this.data.retry(item.public_id), this.text('retryQueued'));
  }

  openEditor(): void {
    const item = this.detail();
    if (!item || !item.mime_type.startsWith('image/') || item.is_locked) return;
    this.dialog.open<MediaImageEditorDialog, { item: MediaItem; source: string }, File>(MediaImageEditorDialog, {
      data: { item, source: this.thumbnail(item) },
      width: '1180px',
      maxWidth: '96vw',
      maxHeight: '96vh',
    }).afterClosed().subscribe((file) => {
      if (file) {
        this.queueFiles([file]);
        this.startUploads();
      }
    });
  }

  openPreview(item = this.detail()): void {
    if (item) window.open(item.content_url, '_blank', 'noopener,noreferrer');
  }

  downloadSelected(): void {
    for (const item of this.selectedItems()) {
      const anchor = document.createElement('a');
      anchor.href = item.content_url;
      anchor.download = item.normalized_filename;
      anchor.rel = 'noopener';
      anchor.click();
    }
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

  @HostListener('document:keydown', ['$event'])
  handleShortcut(event: KeyboardEvent): void {
    if (this.isTypingTarget(event.target) || document.querySelector('.cdk-overlay-pane')) return;
    const modifier = event.ctrlKey || event.metaKey;
    const key = event.key.toLowerCase();
    if (modifier && key === 'a') {
      event.preventDefault();
      this.toggleSelectAll();
    } else if (key === 'delete') {
      event.preventDefault();
      this.trashSelected();
    } else if (key === 'escape') {
      this.clearSelection();
      this.uploadPanelOpen.set(false);
    } else if (key === 'u' && this.authStore.hasPermission('media.create')) {
      this.uploadPanelOpen.update((open) => !open);
    } else if (key === 'g') {
      this.viewMode.set('grid');
    } else if (key === 'l') {
      this.viewMode.set('list');
    } else if (key === 'enter') {
      this.openPreview();
    } else if (key.startsWith('arrow')) {
      this.moveKeyboardSelection(key === 'arrowleft' || key === 'arrowup' ? -1 : 1);
    }
  }

  private normalizedFilters(): MediaFilters {
    const value = this.filters.getRawValue();
    return {
      search: value.search.trim() || undefined,
      status: value.status || undefined,
      mime_type: value.type || undefined,
      folder_id: this.currentFolderId() ?? 'root',
      visibility: value.visibility === 'private' || value.visibility === 'public' ? value.visibility : undefined,
      trashed: value.trashed,
      sort: value.sort,
    };
  }

  private flattenFolders(): readonly FolderRow[] {
    const folders = this.folders();
    const rows: FolderRow[] = [];
    const visited = new Set<string>();
    const append = (parentId: string | null, depth: number): void => {
      for (const folder of folders.filter((candidate) => candidate.parent_id === parentId)) {
        if (visited.has(folder.public_id)) continue;
        visited.add(folder.public_id);
        rows.push({ folder, depth });
        append(folder.public_id, depth + 1);
      }
    };
    append(null, 0);
    return rows;
  }

  private loadDetail(publicId: string): void {
    this.data.show(publicId).pipe(takeUntilDestroyed(this.destroyRef)).subscribe({
      next: (item) => {
        if (!this.selectedIds().has(publicId)) return;
        this.detail.set(item);
        this.detailForm.setValue({ title: item.title ?? '', alt_text: item.alt_text ?? '', caption: item.caption ?? '' });
      },
      error: (error: unknown) => this.showError(error),
    });
  }

  private replaceItem(updated: MediaItem): void {
    this.items.update((items) => items.map((item) => item.public_id === updated.public_id ? updated : item));
  }

  private updateUpload(id: string, patch: Partial<Omit<MediaUploadState, 'id' | 'name' | 'file'>>): void {
    this.uploads.update((items) => items.map((item) => item.id === id ? { ...item, ...patch } : item));
  }

  private run(request: Observable<unknown>, message: string): void {
    request.pipe(takeUntilDestroyed(this.destroyRef)).subscribe({
      next: () => {
        this.snackBar.open(message, this.text('close'), { duration: 3000 });
        this.load();
      },
      error: (error: unknown) => this.showError(error),
    });
  }

  private runBulk(requests: readonly Observable<unknown>[], message: string): void {
    if (!requests.length) return;
    forkJoin(requests).pipe(takeUntilDestroyed(this.destroyRef)).subscribe({
      next: () => {
        this.snackBar.open(message, this.text('close'), { duration: 3000 });
        this.clearSelection();
        this.load();
      },
      error: (error: unknown) => this.showError(error),
    });
  }

  private openFolderDialog(data: MediaFolderDialogData): Observable<string | undefined> {
    return this.dialog.open<MediaFolderDialog, MediaFolderDialogData, string>(MediaFolderDialog, { data, width: '480px' }).afterClosed();
  }

  private showError(error: unknown): void {
    this.snackBar.open(authErrorMessage(error, this.text('operationError')), this.text('close'), { duration: 5000 });
  }

  private moveKeyboardSelection(delta: number): void {
    const items = this.items();
    if (!items.length) return;
    const current = this.detail();
    const currentIndex = current ? items.findIndex((item) => item.public_id === current.public_id) : -1;
    const next = items[Math.max(0, Math.min(items.length - 1, currentIndex + delta))];
    this.selectedIds.set(new Set([next.public_id]));
    this.lastSelectedId = next.public_id;
    this.loadDetail(next.public_id);
  }

  private isTypingTarget(target: EventTarget | null): boolean {
    return target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement;
  }
}
