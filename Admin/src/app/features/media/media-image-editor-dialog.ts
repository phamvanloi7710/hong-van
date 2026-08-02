import { AfterViewInit, ChangeDetectionStrategy, Component, ElementRef, inject, signal, ViewChild } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSliderModule } from '@angular/material/slider';

import { I18nService } from '../../core/i18n/i18n.service';
import { MEDIA_TRANSLATIONS } from './media.i18n';
import { MediaItem } from './media.models';

interface EditorDialogData {
  readonly item: MediaItem;
  readonly source: string;
}

interface CropRect {
  readonly x: number;
  readonly y: number;
  readonly width: number;
  readonly height: number;
}

@Component({
  selector: 'hv-media-image-editor-dialog',
  imports: [MatButtonModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSliderModule, ReactiveFormsModule],
  templateUrl: './media-image-editor-dialog.html',
  styleUrl: './media-image-editor-dialog.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MediaImageEditorDialog implements AfterViewInit {
  readonly dialogData = inject<EditorDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<MediaImageEditorDialog, File>);
  private readonly i18n = inject(I18nService);

  @ViewChild('canvas', { static: true }) private readonly canvasRef!: ElementRef<HTMLCanvasElement>;

  readonly width = new FormControl(0, { nonNullable: true });
  readonly height = new FormControl(0, { nonNullable: true });
  readonly brightness = new FormControl(100, { nonNullable: true });
  readonly contrast = new FormControl(100, { nonNullable: true });
  readonly grayscale = new FormControl(0, { nonNullable: true });
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly error = signal<string | null>(null);
  readonly crop = signal<CropRect | null>(null);

  private sourceCanvas = document.createElement('canvas');
  private cropStart: { readonly x: number; readonly y: number } | null = null;
  private flipX = 1;
  private flipY = 1;

  ngAfterViewInit(): void {
    const image = new Image();
    image.onload = () => {
      this.sourceCanvas.width = image.naturalWidth;
      this.sourceCanvas.height = image.naturalHeight;
      this.sourceCanvas.getContext('2d')?.drawImage(image, 0, 0);
      this.width.setValue(image.naturalWidth);
      this.height.setValue(image.naturalHeight);
      this.loading.set(false);
      this.render();
    };
    image.onerror = () => {
      this.loading.set(false);
      this.error.set(this.text('editorLoadError'));
    };
    image.src = this.dialogData.source;
  }

  text(key: string): string {
    return MEDIA_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  rotate(clockwise: boolean): void {
    const next = document.createElement('canvas');
    next.width = this.sourceCanvas.height;
    next.height = this.sourceCanvas.width;
    const context = next.getContext('2d');
    if (!context) return;
    context.translate(next.width / 2, next.height / 2);
    context.rotate(clockwise ? Math.PI / 2 : -Math.PI / 2);
    context.drawImage(this.sourceCanvas, -this.sourceCanvas.width / 2, -this.sourceCanvas.height / 2);
    this.sourceCanvas = next;
    this.width.setValue(next.width);
    this.height.setValue(next.height);
    this.crop.set(null);
    this.render();
  }

  flip(horizontal: boolean): void {
    if (horizontal) this.flipX *= -1;
    else this.flipY *= -1;
    this.render();
  }

  reset(): void {
    this.dialogRef.close();
  }

  resize(): void {
    const width = Math.max(1, Math.min(8000, Math.round(this.width.value)));
    const height = Math.max(1, Math.min(8000, Math.round(this.height.value)));
    const next = document.createElement('canvas');
    next.width = width;
    next.height = height;
    next.getContext('2d')?.drawImage(this.sourceCanvas, 0, 0, width, height);
    this.sourceCanvas = next;
    this.width.setValue(width);
    this.height.setValue(height);
    this.crop.set(null);
    this.render();
  }

  beginCrop(event: PointerEvent): void {
    const point = this.canvasPoint(event);
    this.cropStart = point;
    this.crop.set({ ...point, width: 0, height: 0 });
    this.canvasRef.nativeElement.setPointerCapture(event.pointerId);
  }

  updateCrop(event: PointerEvent): void {
    if (!this.cropStart) return;
    const point = this.canvasPoint(event);
    this.crop.set({
      x: Math.min(this.cropStart.x, point.x),
      y: Math.min(this.cropStart.y, point.y),
      width: Math.abs(point.x - this.cropStart.x),
      height: Math.abs(point.y - this.cropStart.y),
    });
    this.render();
  }

  finishCrop(event: PointerEvent): void {
    if (this.canvasRef.nativeElement.hasPointerCapture(event.pointerId)) this.canvasRef.nativeElement.releasePointerCapture(event.pointerId);
    this.cropStart = null;
    const crop = this.crop();
    if (crop && (crop.width < 4 || crop.height < 4)) this.crop.set(null);
    this.render();
  }

  applyCrop(): void {
    const crop = this.crop();
    if (!crop) return;
    const next = document.createElement('canvas');
    next.width = Math.max(1, Math.round(crop.width));
    next.height = Math.max(1, Math.round(crop.height));
    next.getContext('2d')?.drawImage(this.canvasRef.nativeElement, crop.x, crop.y, crop.width, crop.height, 0, 0, next.width, next.height);
    this.sourceCanvas = next;
    this.width.setValue(next.width);
    this.height.setValue(next.height);
    this.flipX = 1;
    this.flipY = 1;
    this.crop.set(null);
    this.render();
  }

  render(): void {
    if (this.loading()) return;
    const canvas = this.canvasRef.nativeElement;
    canvas.width = this.sourceCanvas.width;
    canvas.height = this.sourceCanvas.height;
    const context = canvas.getContext('2d');
    if (!context) return;
    context.clearRect(0, 0, canvas.width, canvas.height);
    context.save();
    context.filter = `brightness(${this.brightness.value}%) contrast(${this.contrast.value}%) grayscale(${this.grayscale.value}%)`;
    context.translate(this.flipX < 0 ? canvas.width : 0, this.flipY < 0 ? canvas.height : 0);
    context.scale(this.flipX, this.flipY);
    context.drawImage(this.sourceCanvas, 0, 0);
    context.restore();
    const crop = this.crop();
    if (crop) {
      context.save();
      context.strokeStyle = '#fff';
      context.lineWidth = Math.max(2, canvas.width / 500);
      context.setLineDash([10, 6]);
      context.strokeRect(crop.x, crop.y, crop.width, crop.height);
      context.restore();
    }
  }

  save(): void {
    if (this.saving() || this.loading()) return;
    this.saving.set(true);
    this.render();
    const mime = this.dialogData.item.mime_type === 'image/jpeg' ? 'image/jpeg' : 'image/png';
    this.canvasRef.nativeElement.toBlob((blob) => {
      this.saving.set(false);
      if (!blob) {
        this.error.set(this.text('editorSaveError'));
        return;
      }
      const stem = this.dialogData.item.original_filename.replace(/\.[^.]+$/, '');
      this.dialogRef.close(new File([blob], `${stem}-edited.${mime === 'image/jpeg' ? 'jpg' : 'png'}`, { type: mime }));
    }, mime, 0.92);
  }

  private canvasPoint(event: PointerEvent): { readonly x: number; readonly y: number } {
    const canvas = this.canvasRef.nativeElement;
    const rect = canvas.getBoundingClientRect();
    return {
      x: Math.max(0, Math.min(canvas.width, (event.clientX - rect.left) * (canvas.width / rect.width))),
      y: Math.max(0, Math.min(canvas.height, (event.clientY - rect.top) * (canvas.height / rect.height))),
    };
  }
}
