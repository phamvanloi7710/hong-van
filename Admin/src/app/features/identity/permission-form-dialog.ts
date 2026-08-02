import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';

import { IdentityPermission, PermissionPayload } from './identity.models';
import { TranslationPipe } from '../../core/i18n/translation.pipe';

@Component({
  selector: 'hv-permission-form-dialog',
  imports: [MatButtonModule, MatDialogModule, MatFormFieldModule, MatInputModule, MatSelectModule, ReactiveFormsModule, TranslationPipe],
  template: `
    <h2 mat-dialog-title>{{ data ? ('identity.updatePermission' | hvT) : ('identity.createPermission' | hvT) }}</h2>
    <mat-dialog-content>
      <form class="identity-form" [formGroup]="form">
        <mat-form-field appearance="outline"><mat-label>{{ 'identity.module' | hvT }}</mat-label><input matInput formControlName="module" /></mat-form-field>
        <mat-form-field appearance="outline">
          <mat-label>{{ 'identity.action' | hvT }}</mat-label>
          <mat-select formControlName="action">
            @for (action of actions; track action) { <mat-option [value]="action">{{ action }}</mat-option> }
          </mat-select>
        </mat-form-field>
        <mat-form-field appearance="outline" class="wide"><mat-label>{{ 'identity.displayName' | hvT }}</mat-label><input matInput formControlName="name" /></mat-form-field>
        <mat-form-field appearance="outline" class="wide"><mat-label>{{ 'identity.descriptionField' | hvT }}</mat-label><textarea matInput formControlName="description"></textarea></mat-form-field>
      </form>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button mat-dialog-close>{{ 'common.cancel' | hvT }}</button>
      <button mat-flat-button [disabled]="form.invalid" (click)="save()">{{ 'common.save' | hvT }}</button>
    </mat-dialog-actions>
  `,
  styles: `.identity-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; padding-top: 4px; } .wide { grid-column: 1 / -1; } @media (max-width: 680px) { .identity-form { grid-template-columns: 1fr; } }`,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PermissionFormDialog {
  readonly data = inject<IdentityPermission | null>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<PermissionFormDialog, PermissionPayload>);
  private readonly formBuilder = inject(FormBuilder);
  readonly actions = ['view', 'create', 'update', 'delete', 'restore', 'publish', 'export', 'manage_settings'] as const;

  readonly form = this.formBuilder.nonNullable.group({
    module: [this.data?.module ?? '', [Validators.required, Validators.pattern(/^[a-z][a-z0-9_]*$/)]],
    action: [this.data?.action ?? 'view', Validators.required],
    name: [this.data?.name ?? '', [Validators.required, Validators.maxLength(160)]],
    description: [this.data?.description ?? ''],
  });

  save(): void {
    if (this.form.invalid) return;
    const value = this.form.getRawValue();
    this.dialogRef.close({ ...value, description: value.description.trim() || null });
  }
}
