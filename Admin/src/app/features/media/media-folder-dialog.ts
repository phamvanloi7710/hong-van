import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';

import { I18nService } from '../../core/i18n/i18n.service';
import { MEDIA_TRANSLATIONS } from './media.i18n';

export interface MediaFolderDialogData {
  readonly mode: 'create' | 'rename';
  readonly name?: string;
}

@Component({
  selector: 'hv-media-folder-dialog',
  imports: [MatButtonModule, MatDialogModule, MatFormFieldModule, MatInputModule, ReactiveFormsModule],
  template: `
    <h2 mat-dialog-title>{{ text(dialogData.mode === 'create' ? 'createFolder' : 'renameFolder') }}</h2>
    <mat-dialog-content>
      <mat-form-field appearance="outline" class="folder-field">
        <mat-label>{{ text('folderName') }}</mat-label>
        <input matInput maxlength="255" [formControl]="name" (keyup.enter)="save()" />
        @if (name.invalid && name.touched) { <mat-error>{{ text('folderNameRequired') }}</mat-error> }
      </mat-form-field>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button type="button" mat-dialog-close>{{ text('cancel') }}</button>
      <button mat-flat-button type="button" [disabled]="name.invalid" (click)="save()">{{ text('save') }}</button>
    </mat-dialog-actions>
  `,
  styles: `.folder-field{width:min(430px,72vw);margin-top:8px}`,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MediaFolderDialog {
  readonly dialogData = inject<MediaFolderDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<MediaFolderDialog, string>);
  private readonly i18n = inject(I18nService);
  readonly name = new FormControl(this.dialogData.name ?? '', { nonNullable: true, validators: [Validators.required, Validators.maxLength(255)] });

  text(key: string): string {
    return MEDIA_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  save(): void {
    this.name.markAsTouched();
    const value = this.name.value.trim();
    if (this.name.invalid || !value) return;
    this.dialogRef.close(value);
  }
}
