import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatTableModule } from '@angular/material/table';
import { MatTabsModule } from '@angular/material/tabs';
import { filter, finalize, forkJoin, Observable, of, switchMap } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { HasPermissionDirective } from '../../core/auth/has-permission.directive';
import { AuthStore } from '../../core/auth/auth.store';
import { I18nService } from '../../core/i18n/i18n.service';
import { TranslationPipe } from '../../core/i18n/translation.pipe';
import { IdentityDataService } from './identity-data.service';
import {
  IdentityPermission,
  IdentityRole,
  IdentityUser,
  PermissionPayload,
  RolePayload,
  UserPayload,
} from './identity.models';
import { PermissionFormDialog } from './permission-form-dialog';
import { RoleFormDialog, RoleDialogData } from './role-form-dialog';
import { UserDialogData, UserFormDialog } from './user-form-dialog';

@Component({
  selector: 'hv-identity-page',
  imports: [
    HasPermissionDirective,
    MatButtonModule,
    MatCardModule,
    MatChipsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatIconModule,
    MatInputModule,
    MatProgressSpinnerModule,
    MatTableModule,
    MatTabsModule,
    TranslationPipe,
  ],
  templateUrl: './identity-page.html',
  styleUrl: './identity-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class IdentityPage {
  private readonly data = inject(IdentityDataService);
  private readonly dialog = inject(MatDialog);
  private readonly snackBar = inject(MatSnackBar);
  private readonly i18n = inject(I18nService);
  readonly authStore = inject(AuthStore);

  readonly users = signal<readonly IdentityUser[]>([]);
  readonly roles = signal<readonly IdentityRole[]>([]);
  readonly permissions = signal<readonly IdentityPermission[]>([]);
  readonly loading = signal(true);
  readonly error = signal<string | null>(null);
  readonly userColumns = ['name', 'roles', 'status', 'actions'];
  readonly roleColumns = ['name', 'permissions', 'users', 'actions'];
  readonly permissionColumns = ['key', 'name', 'roles', 'actions'];

  constructor() {
    this.reload();
  }

  reload(search = ''): void {
    this.loading.set(true);
    this.error.set(null);

    forkJoin({
      users: this.data.listUsers(search),
      roles: this.authStore.hasPermission('roles.view') ? this.data.listRoles() : of([]),
      permissions: this.authStore.hasPermission('permissions.view')
        ? this.data.listPermissions()
        : of([]),
    })
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({
        next: ({ users, roles, permissions }) => {
          this.users.set(users);
          this.roles.set(roles);
          this.permissions.set(permissions);
        },
        error: (error: unknown) =>
          this.error.set(authErrorMessage(error, this.i18n.t('identity.loadError'))),
      });
  }

  search(value: string): void {
    this.reload(value.trim());
  }

  openUser(user: IdentityUser | null = null): void {
    const canManageRoles = !this.isSelfUser(user) && this.authStore.hasPermission('roles.update');
    const canManageOverrides =
      !this.isSelfUser(user) && this.authStore.hasPermission('permissions.update');
    const data: UserDialogData = {
      user,
      roles: this.roles(),
      permissions: this.permissions(),
      canManageRoles,
      canManageOverrides,
    };
    this.dialog
      .open<UserFormDialog, UserDialogData, UserPayload>(UserFormDialog, { data, width: '760px' })
      .afterClosed()
      .pipe(
        filter((payload): payload is UserPayload => payload !== undefined),
        switchMap((payload) =>
          user ? this.data.updateUser(user.public_id, payload) : this.data.createUser(payload),
        ),
      )
      .subscribe({
        next: () => this.complete(this.i18n.t('identity.savedUser')),
        error: (error: unknown) => this.fail(error),
      });
  }

  openRole(role: IdentityRole | null = null): void {
    const data: RoleDialogData = { role, permissions: this.permissions() };
    this.dialog
      .open<RoleFormDialog, RoleDialogData, RolePayload>(RoleFormDialog, { data, width: '720px' })
      .afterClosed()
      .pipe(
        filter((payload): payload is RolePayload => payload !== undefined),
        switchMap((payload) =>
          role ? this.data.updateRole(role.public_id, payload) : this.data.createRole(payload),
        ),
      )
      .subscribe({
        next: () => this.complete(this.i18n.t('identity.savedRole')),
        error: (error: unknown) => this.fail(error),
      });
  }

  openPermission(permission: IdentityPermission | null = null): void {
    this.dialog
      .open<PermissionFormDialog, IdentityPermission | null, PermissionPayload>(
        PermissionFormDialog,
        { data: permission, width: '680px' },
      )
      .afterClosed()
      .pipe(
        filter((payload): payload is PermissionPayload => payload !== undefined),
        switchMap((payload) =>
          permission
            ? this.data.updatePermission(permission.public_id, payload)
            : this.data.createPermission(payload),
        ),
      )
      .subscribe({
        next: () => this.complete(this.i18n.t('identity.savedPermission')),
        error: (error: unknown) => this.fail(error),
      });
  }

  lock(user: IdentityUser): void {
    this.run(this.data.lockUser(user.public_id), this.i18n.t('identity.lockedUser'));
  }

  activate(user: IdentityUser): void {
    this.run(this.data.activateUser(user.public_id), this.i18n.t('identity.activatedUser'));
  }

  resetSessions(user: IdentityUser): void {
    this.run(this.data.resetUserSessions(user.public_id), this.i18n.t('identity.sessionsRevoked'));
  }

  deleteUser(user: IdentityUser): void {
    if (confirm(this.i18n.t('identity.confirmDeleteUser', { name: user.name }))) {
      this.run(this.data.deleteUser(user.public_id), this.i18n.t('identity.deletedUser'));
    }
  }

  deleteRole(role: IdentityRole): void {
    if (confirm(this.i18n.t('identity.confirmDeleteRole', { name: role.name }))) {
      this.run(this.data.deleteRole(role.public_id), this.i18n.t('identity.deletedRole'));
    }
  }

  deletePermission(permission: IdentityPermission): void {
    if (confirm(this.i18n.t('identity.confirmDeletePermission', { name: permission.key }))) {
      this.run(this.data.deletePermission(permission.public_id), this.i18n.t('identity.deletedPermission'));
    }
  }

  isSelf(user: IdentityUser): boolean {
    return user.public_id === this.authStore.user()?.public_id;
  }

  private isSelfUser(user: IdentityUser | null): boolean {
    return user !== null && this.isSelf(user);
  }

  private run(request: Observable<unknown>, message: string): void {
    request.subscribe({
      next: () => this.complete(message),
      error: (error: unknown) => this.fail(error),
    });
  }

  private complete(message: string): void {
    this.snackBar.open(message, this.i18n.t('common.close'), { duration: 3000 });
    this.reload();
  }

  private fail(error: unknown): void {
    this.snackBar.open(authErrorMessage(error, this.i18n.t('identity.operationFailed')), this.i18n.t('common.close'), {
      duration: 5000,
    });
  }
}
