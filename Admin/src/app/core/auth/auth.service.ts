import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { catchError, map, Observable, of, shareReplay, switchMap, tap, throwError } from 'rxjs';

import { environment } from '../../../environments/environment';
import { authErrorMessage } from './auth-error';
import {
  AdminUser,
  ApiEnvelope,
  LoginCredentials,
  ResetPasswordPayload,
} from './auth.models';
import { AuthStore } from './auth.store';
import { AdminPreferencesStore } from '../preferences/admin-preferences.store';
import { I18nService } from '../i18n/i18n.service';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly http = inject(HttpClient);
  private readonly preferences = inject(AdminPreferencesStore);
  private readonly i18n = inject(I18nService);
  private readonly apiBaseUrl = `${environment.apiBaseUrl}/auth`;
  private bootstrapRequest?: Observable<AdminUser | null>;

  readonly store = inject(AuthStore);

  bootstrap(): Observable<AdminUser | null> {
    if (this.store.authenticated()) {
      return of(this.store.user());
    }

    if (this.store.status() === 'anonymous') {
      return of(null);
    }

    if (this.bootstrapRequest) {
      return this.bootstrapRequest;
    }

    this.store.markLoading();
    this.bootstrapRequest = this.http.get<ApiEnvelope<AdminUser>>(`${this.apiBaseUrl}/me`).pipe(
      map((response) => response.data),
      tap((user) => this.store.markAuthenticated(user)),
      switchMap((user) =>
        this.preferences.initialize(user.public_id).pipe(
          map(() => user),
        ),
      ),
      catchError((error: unknown) => {
        if (error instanceof HttpErrorResponse && error.status === 401) {
          this.store.markAnonymous();
        } else {
          this.store.markError(
            authErrorMessage(error, this.i18n.t('auth.sessionError')),
          );
        }

        return of(null);
      }),
      shareReplay({ bufferSize: 1, refCount: false }),
    );

    return this.bootstrapRequest;
  }

  login(credentials: LoginCredentials): Observable<AdminUser> {
    this.store.markLoading();

    return this.csrfCookie().pipe(
      switchMap(() =>
        this.http.post<ApiEnvelope<AdminUser>>(`${this.apiBaseUrl}/login`, credentials),
      ),
      map((response) => response.data),
      tap((user) => this.store.markAuthenticated(user)),
      switchMap((user) =>
        this.preferences.initialize(user.public_id).pipe(
          map(() => user),
        ),
      ),
      catchError((error: unknown) => {
        this.store.markError(authErrorMessage(error, this.i18n.t('auth.loginError')));
        return throwError(() => error);
      }),
    );
  }

  logout(): Observable<void> {
    return this.csrfCookie().pipe(
      switchMap(() => this.http.post<ApiEnvelope<null>>(`${this.apiBaseUrl}/logout`, {})),
      tap(() => {
        this.store.markAnonymous();
        this.preferences.clear();
      }),
      map(() => undefined),
    );
  }

  forgotPassword(email: string): Observable<string> {
    return this.csrfCookie().pipe(
      switchMap(() =>
        this.http.post<ApiEnvelope<null>>(`${this.apiBaseUrl}/forgot-password`, { email }),
      ),
      map(
        (response) =>
          response.message ??
          this.i18n.t('auth.forgotDefault'),
      ),
    );
  }

  resetPassword(payload: ResetPasswordPayload): Observable<string> {
    return this.csrfCookie().pipe(
      switchMap(() =>
        this.http.post<ApiEnvelope<null>>(`${this.apiBaseUrl}/reset-password`, payload),
      ),
      tap(() => this.store.markAnonymous()),
      map((response) => response.message ?? this.i18n.t('auth.resetSuccess')),
    );
  }

  private csrfCookie(): Observable<void> {
    return this.http.get<void>('/sanctum/csrf-cookie');
  }
}
