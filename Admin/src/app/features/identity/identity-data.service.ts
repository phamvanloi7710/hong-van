import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { environment } from '../../../environments/environment';
import {
  IdentityPermission,
  IdentityRole,
  IdentityUser,
  PermissionPayload,
  RolePayload,
  UserPayload,
} from './identity.models';

@Injectable({ providedIn: 'root' })
export class IdentityDataService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiBaseUrl}/identity`;

  listUsers(search = ''): Observable<readonly IdentityUser[]> {
    const params = search ? new HttpParams().set('filter[name]', search) : undefined;
    return this.http
      .get<ApiEnvelope<IdentityUser[]>>(`${this.baseUrl}/users`, { params })
      .pipe(map((response) => response.data));
  }

  listRoles(): Observable<readonly IdentityRole[]> {
    return this.http
      .get<ApiEnvelope<IdentityRole[]>>(`${this.baseUrl}/roles`, {
        params: new HttpParams().set('per_page', 100),
      })
      .pipe(map((response) => response.data));
  }

  listPermissions(): Observable<readonly IdentityPermission[]> {
    return this.http
      .get<ApiEnvelope<IdentityPermission[]>>(`${this.baseUrl}/permissions`, {
        params: new HttpParams().set('per_page', 100),
      })
      .pipe(map((response) => response.data));
  }

  createUser(payload: UserPayload): Observable<IdentityUser> {
    return this.unwrap(this.http.post<ApiEnvelope<IdentityUser>>(`${this.baseUrl}/users`, payload));
  }

  updateUser(publicId: string, payload: UserPayload): Observable<IdentityUser> {
    return this.unwrap(
      this.http.put<ApiEnvelope<IdentityUser>>(`${this.baseUrl}/users/${publicId}`, payload),
    );
  }

  lockUser(publicId: string): Observable<IdentityUser> {
    return this.unwrap(
      this.http.post<ApiEnvelope<IdentityUser>>(`${this.baseUrl}/users/${publicId}/lock`, {}),
    );
  }

  activateUser(publicId: string): Observable<IdentityUser> {
    return this.unwrap(
      this.http.post<ApiEnvelope<IdentityUser>>(`${this.baseUrl}/users/${publicId}/activate`, {}),
    );
  }

  resetUserSessions(publicId: string): Observable<void> {
    return this.http
      .post<ApiEnvelope<null>>(`${this.baseUrl}/users/${publicId}/reset-sessions`, {})
      .pipe(map(() => undefined));
  }

  deleteUser(publicId: string): Observable<void> {
    return this.http
      .delete<ApiEnvelope<null>>(`${this.baseUrl}/users/${publicId}`)
      .pipe(map(() => undefined));
  }

  createRole(payload: RolePayload): Observable<IdentityRole> {
    return this.unwrap(this.http.post<ApiEnvelope<IdentityRole>>(`${this.baseUrl}/roles`, payload));
  }

  updateRole(publicId: string, payload: RolePayload): Observable<IdentityRole> {
    return this.unwrap(
      this.http.put<ApiEnvelope<IdentityRole>>(`${this.baseUrl}/roles/${publicId}`, payload),
    );
  }

  deleteRole(publicId: string): Observable<void> {
    return this.http
      .delete<ApiEnvelope<null>>(`${this.baseUrl}/roles/${publicId}`)
      .pipe(map(() => undefined));
  }

  createPermission(payload: PermissionPayload): Observable<IdentityPermission> {
    return this.unwrap(
      this.http.post<ApiEnvelope<IdentityPermission>>(`${this.baseUrl}/permissions`, payload),
    );
  }

  updatePermission(
    publicId: string,
    payload: PermissionPayload,
  ): Observable<IdentityPermission> {
    return this.unwrap(
      this.http.put<ApiEnvelope<IdentityPermission>>(
        `${this.baseUrl}/permissions/${publicId}`,
        payload,
      ),
    );
  }

  deletePermission(publicId: string): Observable<void> {
    return this.http
      .delete<ApiEnvelope<null>>(`${this.baseUrl}/permissions/${publicId}`)
      .pipe(map(() => undefined));
  }

  private unwrap<T>(request: Observable<ApiEnvelope<T>>): Observable<T> {
    return request.pipe(map((response) => response.data));
  }
}
