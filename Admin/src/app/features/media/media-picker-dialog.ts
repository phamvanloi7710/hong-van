import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { finalize } from 'rxjs';

import { MediaPickerOptions, MediaPickerResult } from '../../core/media/media-picker.contract';
import { I18nService } from '../../core/i18n/i18n.service';
import { MediaDataService } from './media-data.service';
import { MEDIA_TRANSLATIONS } from './media.i18n';
import { MediaItem } from './media.models';

@Component({
  selector: 'hv-media-picker-dialog',
  imports: [ReactiveFormsModule, MatButtonModule, MatCardModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule],
  template: `
    <h2 mat-dialog-title>{{ text('pickerTitle') }}</h2>
    <mat-dialog-content>
      <p class="description">{{ text('pickerDescription') }}</p>
      <form class="picker-search" (ngSubmit)="load()">
        <mat-form-field appearance="outline">
          <mat-label>{{ text('search') }}</mat-label>
          <input matInput [formControl]="search" [placeholder]="text('searchHint')" />
        </mat-form-field>
        <button mat-flat-button type="submit"><mat-icon>search</mat-icon>{{ text('apply') }}</button>
      </form>

      @if (loading()) {
        <div class="state"><mat-spinner diameter="42" /></div>
      } @else if (error()) {
        <div class="state error"><mat-icon>error_outline</mat-icon>{{ error() }}</div>
      } @else if (items().length === 0) {
        <div class="state"><mat-icon>perm_media</mat-icon>{{ text('pickerEmpty') }}</div>
      } @else {
        <div class="picker-grid">
          @for (item of items(); track item.public_id) {
            <button class="picker-item" type="button" [class.selected]="isSelected(item)" (click)="toggle(item)">
              <span class="preview">
                @if (item.mime_type.startsWith('image/')) { <img [src]="thumbnail(item)" [alt]="item.alt_text ?? item.original_filename" /> }
                @else { <mat-icon>description</mat-icon> }
              </span>
              <strong>{{ item.title || item.original_filename }}</strong>
              <span>{{ item.mime_type }}</span>
              @if (isSelected(item)) { <mat-icon class="selected-icon">check_circle</mat-icon> }
            </button>
          }
        </div>
      }
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <span class="selection-count">{{ interpolate('selectedCount', { count: selectedIds().size.toString() }) }}</span>
      <button mat-button type="button" mat-dialog-close>{{ text('cancel') }}</button>
      <button mat-flat-button type="button" [disabled]="selectedIds().size === 0" (click)="confirm()">{{ text('choose') }}</button>
    </mat-dialog-actions>
  `,
  styles: `
    .description{margin:0 0 14px;color:var(--hv-theme-muted)}.picker-search{display:grid;grid-template-columns:1fr auto;gap:10px;align-items:start}.picker-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.picker-item{position:relative;display:flex;min-width:0;flex-direction:column;gap:5px;padding:8px;border:2px solid transparent;border-radius:6px;background:color-mix(in srgb,var(--hv-theme-text) 4%,var(--hv-theme-surface));color:inherit;text-align:left;cursor:pointer}.picker-item.selected{border-color:var(--hv-theme-primary)}.picker-item strong,.picker-item>span{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.picker-item>span{font-size:12px;color:var(--hv-theme-muted)}.preview{display:grid!important;width:100%;aspect-ratio:4/3;place-items:center;overflow:hidden;border-radius:4px;background:color-mix(in srgb,var(--hv-theme-text) 7%,transparent)}.preview img{width:100%;height:100%;object-fit:cover}.preview mat-icon{font-size:42px;width:42px;height:42px}.selected-icon{position:absolute;right:12px;top:12px;color:var(--hv-theme-primary);background:var(--hv-theme-surface);border-radius:50%}.state{min-height:260px;display:flex;align-items:center;justify-content:center;gap:10px}.state.error{color:var(--mat-sys-error)}.selection-count{margin-right:auto}@media(max-width:760px){.picker-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.picker-search{grid-template-columns:1fr}.selection-count{width:100%;margin-bottom:6px}}
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MediaPickerDialog {
  readonly options = inject<MediaPickerOptions>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<MediaPickerDialog, MediaPickerResult>);
  private readonly data = inject(MediaDataService);
  private readonly i18n = inject(I18nService);

  readonly search = new FormControl('', { nonNullable: true });
  readonly items = signal<readonly MediaItem[]>([]);
  readonly selectedIds = signal(new Set(this.options.selectedIds ?? []));
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

  load(): void {
    this.loading.set(true);
    this.error.set(null);
    this.data.list({ search: this.search.value.trim(), status: 'ready', trashed: 'without' }, 1, 100)
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({
        next: (result) => this.items.set(result.items.filter((item) => this.accepts(item))),
        error: () => this.error.set(this.text('pickerLoadError')),
      });
  }

  toggle(item: MediaItem): void {
    this.selectedIds.update((current) => {
      const next = new Set(current);
      if (next.has(item.public_id)) next.delete(item.public_id);
      else {
        if (!this.options.multiple) next.clear();
        next.add(item.public_id);
      }
      return next;
    });
  }

  isSelected(item: MediaItem): boolean {
    return this.selectedIds().has(item.public_id);
  }

  thumbnail(item: MediaItem): string {
    return item.variants.find((variant) => variant.key.startsWith('thumbnail_'))?.content_url ?? item.content_url;
  }

  confirm(): void {
    const selected = this.items().filter((item) => this.selectedIds().has(item.public_id));
    this.dialogRef.close({ items: selected });
  }

  private accepts(item: MediaItem): boolean {
    const accepted = this.options.acceptedMimeTypes;
    return !accepted?.length || accepted.some((mime) => mime.endsWith('/*') ? item.mime_type.startsWith(mime.slice(0, -1)) : item.mime_type === mime);
  }
}
