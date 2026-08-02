import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';

import { IdentityPermission, IdentityRole, RolePayload } from './identity.models';
import { TranslationPipe } from '../../core/i18n/translation.pipe';

export interface RoleDialogData {
  readonly role: IdentityRole | null;
  readonly permissions: readonly IdentityPermission[];
}

@Component({
  selector: 'hv-role-form-dialog',
  imports: [MatButtonModule, MatDialogModule, MatFormFieldModule, MatInputModule, MatSelectModule, ReactiveFormsModule, TranslationPipe],
  template: `
    <h2 mat-dialog-title>{{ data.role ? ('identity.updateRole' | hvT) : ('identity.createRole' | hvT) }}</h2>
    <mat-dialog-content>
      <form class="identity-form" [formGroup]="form">
        <mat-form-field appearance="outline"><mat-label>{{ 'identity.name' | hvT }}</mat-label><input matInput formControlName="name" /></mat-form-field>
        <mat-form-field appearance="outline"><mat-label>{{ 'identity.slug' | hvT }}</mat-label><input matInput formControlName="slug" /></mat-form-field>
        <mat-form-field appearance="outline" class="wide"><mat-label>{{ 'identity.descriptionField' | hvT }}</mat-label><textarea matInput formControlName="description"></textarea></mat-form-field>
        <mat-form-field appearance="outline" class="wide">
          <mat-label>{{ 'identity.permissions' | hvT }}</mat-label>
          <mat-select formControlName="permission_ids" multiple>
            @for (permission of data.permissions; track permission.public_id) {
              <mat-option [value]="permission.public_id">{{ permission.key }} — {{ permission.name }}</mat-option>
            }
          </mat-select>
        </mat-form-field>
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
export class RoleFormDialog {
  readonly data = inject<RoleDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<RoleFormDialog, RolePayload>);
  private readonly formBuilder = inject(FormBuilder);

  readonly form = this.formBuilder.nonNullable.group({
    name: [this.data.role?.name ?? '', [Validators.required, Validators.maxLength(120)]],
    slug: [this.data.role?.slug ?? '', [Validators.required, Validators.pattern(/^[a-z][a-z0-9_]*$/)]],
    description: [this.data.role?.description ?? ''],
    permission_ids: [this.data.role?.permissions.map((permission) => permission.public_id) ?? [] as string[]],
  });

  save(): void {
    if (this.form.invalid) return;
    const value = this.form.getRawValue();
    this.dialogRef.close({ ...value, description: value.description.trim() || null });
  }
}
