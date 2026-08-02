import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';

import { I18nService } from '../../core/i18n/i18n.service';
import { DIRECTORY_TRANSLATIONS } from './settings.i18n';
import { Branch, BranchPayload, ContactChannel, ContactChannelPayload, SocialLink, SocialLinkPayload } from './settings.models';

type DirectoryKind = 'branch' | 'contact' | 'social';
type DirectoryRecord = Branch | ContactChannel | SocialLink;
export type DirectoryPayload = BranchPayload | ContactChannelPayload | SocialLinkPayload;

export interface DirectoryDialogData {
  readonly kind: DirectoryKind;
  readonly record: DirectoryRecord | null;
}

@Component({
  selector: 'hv-directory-form-dialog',
  imports: [MatButtonModule, MatCheckboxModule, MatDialogModule, MatFormFieldModule, MatInputModule, ReactiveFormsModule],
  template: `
    <h2 mat-dialog-title>{{ text(data.record ? 'update' : 'create') }} {{ text(data.kind) }}</h2>
    <mat-dialog-content>
      <form class="dialog-grid" [formGroup]="form">
        @for (field of fields; track field.key) {
          <mat-form-field appearance="outline" [class.full]="field.full">
            <mat-label>{{ text(field.label) }}</mat-label>
            @if (field.multiline) { <textarea matInput rows="3" [formControlName]="field.key"></textarea> }
            @else { <input matInput [type]="field.type ?? 'text'" [formControlName]="field.key" /> }
          </mat-form-field>
        }
        <div class="checks">
          @if (data.kind === 'branch') { <mat-checkbox formControlName="is_head_office">{{ text('head') }}</mat-checkbox> }
          @if (data.kind === 'contact') { <mat-checkbox formControlName="is_primary">{{ text('primary') }}</mat-checkbox> }
          <mat-checkbox formControlName="is_active">{{ text('active') }}</mat-checkbox>
        </div>
      </form>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button mat-dialog-close>{{ text('cancel') }}</button>
      <button mat-flat-button [disabled]="form.invalid" (click)="save()">{{ text('save') }}</button>
    </mat-dialog-actions>
  `,
  styles: `.dialog-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding-top:8px}.full,.checks{grid-column:1/-1}.checks{display:flex;gap:20px}@media(max-width:600px){.dialog-grid{grid-template-columns:1fr}.full,.checks{grid-column:auto}}`,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DirectoryFormDialog {
  readonly data = inject<DirectoryDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<DirectoryFormDialog, DirectoryPayload>);
  private readonly i18n = inject(I18nService);

  readonly fields = this.fieldsFor(this.data.kind);
  readonly form = this.createForm();

  text(key: string): string {
    return DIRECTORY_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  save(): void {
    if (this.form.invalid) return;
    this.dialogRef.close(this.form.getRawValue() as DirectoryPayload);
  }

  private createForm(): FormGroup {
    const record = this.data.record as unknown as Record<string, unknown> | null;
    const controls: Record<string, FormControl> = {};
    for (const field of this.fields) {
      const value = record?.[field.key] ?? (field.key === 'sort_order' ? 0 : '');
      controls[field.key] = new FormControl(value, field.required ? Validators.required : []);
    }
    controls['is_active'] = new FormControl(record?.['is_active'] ?? true, { nonNullable: true });
    if (this.data.kind === 'branch') controls['is_head_office'] = new FormControl(record?.['is_head_office'] ?? false, { nonNullable: true });
    if (this.data.kind === 'contact') controls['is_primary'] = new FormControl(record?.['is_primary'] ?? false, { nonNullable: true });
    return new FormGroup(controls);
  }

  private fieldsFor(kind: DirectoryKind): readonly { key: string; label: string; required?: boolean; full?: boolean; multiline?: boolean; type?: string }[] {
    if (kind === 'branch') return [
      { key: 'name', label: 'name', required: true }, { key: 'code', label: 'code' },
      { key: 'address', label: 'address', full: true, multiline: true }, { key: 'phone', label: 'phone' },
      { key: 'email', label: 'email', type: 'email' }, { key: 'sort_order', label: 'order', required: true, type: 'number' },
    ];
    if (kind === 'social') return [
      { key: 'platform', label: 'platform', required: true }, { key: 'label', label: 'label', required: true },
      { key: 'url', label: 'url', required: true, full: true, type: 'url' }, { key: 'icon', label: 'icon' },
      { key: 'sort_order', label: 'order', required: true, type: 'number' },
    ];
    return [
      { key: 'type', label: 'type', required: true }, { key: 'label', label: 'label', required: true },
      { key: 'value', label: 'value', required: true }, { key: 'href', label: 'href' },
      { key: 'availability_note', label: 'note', full: true }, { key: 'sort_order', label: 'order', required: true, type: 'number' },
    ];
  }
}
