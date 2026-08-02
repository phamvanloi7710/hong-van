import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';

import { IdentityPermission, IdentityRole, IdentityUser, UserPayload } from './identity.models';
import { TranslationPipe } from '../../core/i18n/translation.pipe';

export interface UserDialogData {
  readonly user: IdentityUser | null;
  readonly roles: readonly IdentityRole[];
  readonly permissions: readonly IdentityPermission[];
  readonly canManageRoles: boolean;
  readonly canManageOverrides: boolean;
}

@Component({
  selector: 'hv-user-form-dialog',
  imports: [
    MatButtonModule,
    MatCheckboxModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    ReactiveFormsModule,
    TranslationPipe,
  ],
  template: `
    <h2 mat-dialog-title>{{ data.user ? ('identity.updateUser' | hvT) : ('identity.createUser' | hvT) }}</h2>
    <mat-dialog-content>
      <form class="identity-form" [formGroup]="form">
        <mat-form-field appearance="outline">
          <mat-label>{{ 'identity.fullName' | hvT }}</mat-label>
          <input matInput formControlName="name" />
        </mat-form-field>
        <mat-form-field appearance="outline">
          <mat-label>{{ 'auth.email' | hvT }}</mat-label>
          <input matInput type="email" formControlName="email" />
        </mat-form-field>
        <mat-form-field appearance="outline">
          <mat-label>{{ data.user ? ('identity.newPasswordOptional' | hvT) : ('auth.password' | hvT) }}</mat-label>
          <input matInput type="password" formControlName="password" />
        </mat-form-field>
        <mat-form-field appearance="outline">
          <mat-label>{{ 'auth.passwordConfirmation' | hvT }}</mat-label>
          <input matInput type="password" formControlName="password_confirmation" />
        </mat-form-field>
        @if (data.canManageRoles) {
          <mat-form-field appearance="outline">
            <mat-label>{{ 'identity.roles' | hvT }}</mat-label>
            <mat-select formControlName="role_ids" multiple>
              @for (role of data.roles; track role.public_id) {
                <mat-option [value]="role.public_id">{{ role.name }}</mat-option>
              }
            </mat-select>
          </mat-form-field>
        }
        @if (data.canManageOverrides) {
          <mat-form-field appearance="outline">
            <mat-label>{{ 'identity.allowPermissions' | hvT }}</mat-label>
            <mat-select formControlName="allow_permissions" multiple>
              @for (permission of data.permissions; track permission.public_id) {
                <mat-option [value]="permission.public_id">{{ permission.key }}</mat-option>
              }
            </mat-select>
          </mat-form-field>
          <mat-form-field appearance="outline">
            <mat-label>{{ 'identity.denyPermissions' | hvT }}</mat-label>
            <mat-select formControlName="deny_permissions" multiple>
              @for (permission of data.permissions; track permission.public_id) {
                <mat-option [value]="permission.public_id">{{ permission.key }}</mat-option>
              }
            </mat-select>
          </mat-form-field>
        }
        @if (!data.user) {
          <mat-checkbox formControlName="is_active">{{ 'identity.allowLogin' | hvT }}</mat-checkbox>
        }
      </form>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button type="button" mat-dialog-close>{{ 'common.cancel' | hvT }}</button>
      <button mat-flat-button type="button" [disabled]="form.invalid" (click)="save()">{{ 'common.save' | hvT }}</button>
    </mat-dialog-actions>
  `,
  styles: `
    .identity-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; padding-top: 4px; }
    mat-checkbox { align-self: center; }
    @media (max-width: 680px) { .identity-form { grid-template-columns: 1fr; } }
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class UserFormDialog {
  readonly data = inject<UserDialogData>(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<UserFormDialog, UserPayload>);
  private readonly formBuilder = inject(FormBuilder);

  private readonly allowed = this.data.user?.permission_overrides
    .filter((item) => item.is_allowed)
    .map((item) => item.permission_id) ?? [];
  private readonly denied = this.data.user?.permission_overrides
    .filter((item) => !item.is_allowed)
    .map((item) => item.permission_id) ?? [];

  readonly form = this.formBuilder.nonNullable.group({
    name: [this.data.user?.name ?? '', [Validators.required, Validators.maxLength(255)]],
    email: [this.data.user?.email ?? '', [Validators.required, Validators.email]],
    password: ['', this.data.user ? [Validators.minLength(12)] : [Validators.required, Validators.minLength(12)]],
    password_confirmation: [''],
    is_active: [this.data.user?.is_active ?? true],
    role_ids: [this.data.user?.roles.map((role) => role.public_id) ?? [] as string[]],
    allow_permissions: [this.allowed],
    deny_permissions: [this.denied],
  });

  save(): void {
    if (this.form.invalid) {
      return;
    }

    const value = this.form.getRawValue();
    if (value.password !== value.password_confirmation) {
      this.form.controls.password_confirmation.setErrors({ mismatch: true });
      return;
    }

    const denied = new Set(value.deny_permissions);
    const permissionOverrides = [
      ...value.allow_permissions
        .filter((permissionId) => !denied.has(permissionId))
        .map((permissionId) => ({ permission_id: permissionId, is_allowed: true })),
      ...value.deny_permissions.map((permissionId) => ({
        permission_id: permissionId,
        is_allowed: false,
      })),
    ];

    this.dialogRef.close({
      name: value.name.trim(),
      email: value.email.trim(),
      ...(value.password
        ? { password: value.password, password_confirmation: value.password_confirmation }
        : {}),
      ...(!this.data.user ? { is_active: value.is_active } : {}),
      ...(this.data.canManageRoles ? { role_ids: value.role_ids } : {}),
      ...(this.data.canManageOverrides ? { permission_overrides: permissionOverrides } : {}),
    });
  }
}
