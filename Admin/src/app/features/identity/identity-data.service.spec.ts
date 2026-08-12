import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { IdentityDataService } from './identity-data.service';
import {
  IdentityPermission,
  IdentityRole,
  IdentityUser,
  RolePayload,
  UserPayload,
} from './identity.models';

const user: IdentityUser = {
  public_id: '01JIDENTITYUSER00000000000',
  name: 'Quản trị viên',
  email: 'admin@example.test',
  email_verified_at: null,
  is_active: true,
  locked_at: null,
  roles: [],
  permission_overrides: [],
  permissions: ['users.view'],
};

const permission: IdentityPermission = {
  public_id: '01JIDENTITYPERMISSION00000',
  key: 'roles.view',
  module: 'roles',
  action: 'view',
  name: 'Xem vai trò',
  labels: { vi: 'Xem vai trò', en: 'View roles', zh: '查看角色' },
  description: null,
  is_system: true,
};

const role: IdentityRole = {
  public_id: '01JIDENTITYROLE00000000000',
  name: 'Quản lý vai trò',
  slug: 'role_manager',
  description: null,
  is_system: false,
  users_count: 0,
  permissions: [permission],
};

function envelope<T>(data: T): ApiEnvelope<T> {
  return {
    success: true,
    data,
    meta: { request_id: '01JREQUEST0000000000000000' },
    message: null,
  };
}

describe('IdentityDataService', () => {
  it('uses the versioned identity endpoint and allowlisted name filter', () => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    const service = TestBed.inject(IdentityDataService);
    const http = TestBed.inject(HttpTestingController);
    let users: readonly IdentityUser[] = [];

    service.listUsers('Hồng Vân').subscribe((result) => (users = result));

    const request = http.expectOne(
      (candidate) =>
        candidate.url === '/api/admin/v1/identity/users' &&
        candidate.params.get('filter[name]') === 'Hồng Vân',
    );
    expect(request.request.method).toBe('GET');
    request.flush(envelope([user]));
    expect(users).toEqual([user]);
    http.verify();
  });

  it('uses typed role list and CRUD endpoints', () => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    const service = TestBed.inject(IdentityDataService);
    const http = TestBed.inject(HttpTestingController);
    const payload: RolePayload = {
      name: role.name,
      slug: role.slug,
      description: role.description,
      permission_ids: [permission.public_id],
    };
    let listedRoles: readonly IdentityRole[] = [];
    let createdRole: IdentityRole | null = null;
    let updatedRole: IdentityRole | null = null;
    let deleted = false;

    service.listRoles().subscribe((result) => (listedRoles = result));
    const listRequest = http.expectOne(
      (candidate) =>
        candidate.url === '/api/admin/v1/identity/roles' &&
        candidate.params.get('per_page') === '100',
    );
    expect(listRequest.request.method).toBe('GET');
    listRequest.flush(envelope([role]));

    service.createRole(payload).subscribe((result) => (createdRole = result));
    const createRequest = http.expectOne('/api/admin/v1/identity/roles');
    expect(createRequest.request.method).toBe('POST');
    expect(createRequest.request.body).toEqual(payload);
    createRequest.flush(envelope(role));

    service.updateRole(role.public_id, payload).subscribe((result) => (updatedRole = result));
    const updateRequest = http.expectOne(`/api/admin/v1/identity/roles/${role.public_id}`);
    expect(updateRequest.request.method).toBe('PUT');
    expect(updateRequest.request.body).toEqual(payload);
    updateRequest.flush(envelope(role));

    service.deleteRole(role.public_id).subscribe(() => (deleted = true));
    const deleteRequest = http.expectOne(`/api/admin/v1/identity/roles/${role.public_id}`);
    expect(deleteRequest.request.method).toBe('DELETE');
    deleteRequest.flush(envelope(null));

    expect(listedRoles).toEqual([role]);
    expect(createdRole).toEqual(role);
    expect(updatedRole).toEqual(role);
    expect(deleted).toBe(true);
    http.verify();
  });

  it('uses typed user CRUD, status, and session endpoints', () => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    const service = TestBed.inject(IdentityDataService);
    const http = TestBed.inject(HttpTestingController);
    const payload: UserPayload = {
      name: user.name,
      email: user.email,
      password: 'SecurePassword123!',
      password_confirmation: 'SecurePassword123!',
      is_active: true,
      role_ids: [],
      permission_overrides: [],
    };
    let createdUser: IdentityUser | null = null;
    let updatedUser: IdentityUser | null = null;
    let lockedUser: IdentityUser | null = null;
    let activatedUser: IdentityUser | null = null;
    let sessionsReset = false;
    let deleted = false;

    service.createUser(payload).subscribe((result) => (createdUser = result));
    const createRequest = http.expectOne('/api/admin/v1/identity/users');
    expect(createRequest.request.method).toBe('POST');
    expect(createRequest.request.body).toEqual(payload);
    createRequest.flush(envelope(user));

    service.updateUser(user.public_id, payload).subscribe((result) => (updatedUser = result));
    const updateRequest = http.expectOne(`/api/admin/v1/identity/users/${user.public_id}`);
    expect(updateRequest.request.method).toBe('PUT');
    expect(updateRequest.request.body).toEqual(payload);
    updateRequest.flush(envelope(user));

    service.lockUser(user.public_id).subscribe((result) => (lockedUser = result));
    const lockRequest = http.expectOne(`/api/admin/v1/identity/users/${user.public_id}/lock`);
    expect(lockRequest.request.method).toBe('POST');
    expect(lockRequest.request.body).toEqual({});
    lockRequest.flush(envelope(user));

    service.activateUser(user.public_id).subscribe((result) => (activatedUser = result));
    const activateRequest = http.expectOne(
      `/api/admin/v1/identity/users/${user.public_id}/activate`,
    );
    expect(activateRequest.request.method).toBe('POST');
    expect(activateRequest.request.body).toEqual({});
    activateRequest.flush(envelope(user));

    service.resetUserSessions(user.public_id).subscribe(() => (sessionsReset = true));
    const resetRequest = http.expectOne(
      `/api/admin/v1/identity/users/${user.public_id}/reset-sessions`,
    );
    expect(resetRequest.request.method).toBe('POST');
    expect(resetRequest.request.body).toEqual({});
    resetRequest.flush(envelope(null));

    service.deleteUser(user.public_id).subscribe(() => (deleted = true));
    const deleteRequest = http.expectOne(`/api/admin/v1/identity/users/${user.public_id}`);
    expect(deleteRequest.request.method).toBe('DELETE');
    deleteRequest.flush(envelope(null));

    expect(createdUser).toEqual(user);
    expect(updatedUser).toEqual(user);
    expect(lockedUser).toEqual(user);
    expect(activatedUser).toEqual(user);
    expect(sessionsReset).toBe(true);
    expect(deleted).toBe(true);
    http.verify();
  });
});
