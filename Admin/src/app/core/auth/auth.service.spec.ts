import {
  provideHttpClient,
  withInterceptors,
  withXsrfConfiguration,
} from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { provideRouter, Router } from '@angular/router';

import { AdminUser, ApiEnvelope } from './auth.models';
import { sessionCredentialsInterceptor, unauthorizedInterceptor } from './auth.interceptor';
import { AuthService } from './auth.service';
import { AdminPreferencesDto } from '../preferences/admin-preferences.model';

const adminUser: AdminUser = {
  public_id: '01JADMINUSER00000000000000',
  name: 'Quản trị Hồng Vân',
  email: 'admin@example.test',
  email_verified_at: '2026-08-02T00:00:00+00:00',
  is_active: true,
  locked_at: null,
  roles: ['super_admin'],
  permissions: ['users.view'],
};

const adminPreferences: AdminPreferencesDto = {
  theme: {
    fixed_header: true,
    fixed_sidenav: true,
    fixed_footer: false,
    sidenav_opened: true,
    sidenav_pinned: true,
    menu_orientation: 'vertical',
    menu_density: 'default',
    skin: 'indigo-light',
    rtl: false,
  },
  locale: 'vi',
  favorite_menu_ids: [],
};

function envelope<T>(data: T, message: string | null = null): ApiEnvelope<T> {
  return {
    success: true,
    data,
    meta: { request_id: '01JREQUEST0000000000000000' },
    message,
  };
}

describe('AuthService', () => {
  let service: AuthService;
  let httpTesting: HttpTestingController;
  let router: Router;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [
        provideHttpClient(
          withXsrfConfiguration({ cookieName: 'XSRF-TOKEN', headerName: 'X-XSRF-TOKEN' }),
          withInterceptors([sessionCredentialsInterceptor, unauthorizedInterceptor]),
        ),
        provideHttpClientTesting(),
        provideRouter([]),
      ],
    });

    service = TestBed.inject(AuthService);
    httpTesting = TestBed.inject(HttpTestingController);
    router = TestBed.inject(Router);
  });

  afterEach(() => {
    httpTesting.verify();
    document.cookie = 'XSRF-TOKEN=; Max-Age=0; path=/';
  });

  it('bootstraps an existing cookie session', () => {
    let resolvedUser: AdminUser | null | undefined;

    service.bootstrap().subscribe((user) => (resolvedUser = user));

    const request = httpTesting.expectOne('/api/admin/v1/auth/me');
    expect(request.request.withCredentials).toBe(true);
    request.flush(envelope(adminUser));
    expect(resolvedUser).toBeUndefined();

    httpTesting.expectOne('/api/admin/v1/preferences').flush(envelope(adminPreferences));

    expect(resolvedUser).toEqual(adminUser);
    expect(service.store.authenticated()).toBe(true);
  });

  it('gets the CSRF cookie before login without persisting a bearer token', () => {
    document.cookie = 'XSRF-TOKEN=csrf-test; path=/';
    const localStorageSpy = vi.spyOn(Storage.prototype, 'setItem');
    let resolvedUser: AdminUser | undefined;

    service
      .login({
        email: adminUser.email,
        password: 'safe-password-for-test',
        remember: false,
      })
      .subscribe((user) => (resolvedUser = user));

    const csrfRequest = httpTesting.expectOne('/sanctum/csrf-cookie');
    expect(csrfRequest.request.withCredentials).toBe(true);
    csrfRequest.flush(null);

    const loginRequest = httpTesting.expectOne('/api/admin/v1/auth/login');
    expect(loginRequest.request.withCredentials).toBe(true);
    expect(loginRequest.request.headers.get('X-XSRF-TOKEN')).toBe('csrf-test');
    loginRequest.flush(envelope(adminUser, 'Đăng nhập thành công.'));
    httpTesting.expectOne('/api/admin/v1/preferences').flush(envelope(adminPreferences));

    expect(resolvedUser).toEqual(adminUser);
    expect(service.store.authenticated()).toBe(true);
    expect(
      localStorageSpy.mock.calls.some(([key]) => String(key).toLowerCase().includes('token')),
    ).toBe(false);
  });

  it('clears auth state and redirects when session bootstrap returns 401', () => {
    const navigateSpy = vi.spyOn(router, 'navigate').mockResolvedValue(true);

    service.bootstrap().subscribe();
    httpTesting.expectOne('/api/admin/v1/auth/me').flush(
      { success: false, message: 'Chưa xác thực.' },
      { status: 401, statusText: 'Unauthorized' },
    );

    expect(service.store.status()).toBe('anonymous');
    expect(navigateSpy).toHaveBeenCalledWith(['/login'], { queryParams: undefined });
  });
});
