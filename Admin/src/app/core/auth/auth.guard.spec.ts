import { TestBed } from '@angular/core/testing';
import { ActivatedRouteSnapshot, Router, RouterStateSnapshot, UrlTree } from '@angular/router';
import { firstValueFrom, Observable, of } from 'rxjs';

import { AdminUser } from './auth.models';
import { permissionGuard } from './auth.guard';
import { AuthService } from './auth.service';

const user: AdminUser = {
  public_id: '01JADMINUSER00000000000000',
  name: 'Admin',
  email: 'admin@example.test',
  email_verified_at: null,
  is_active: true,
  locked_at: null,
  roles: ['content_editor'],
  permissions: [],
};

describe('permissionGuard', () => {
  const authenticated = vi.fn<() => boolean>();
  const hasPermission = vi.fn<(permission: string) => boolean>();
  const bootstrap = vi.fn<() => Observable<AdminUser | null>>();
  const createUrlTree = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
    TestBed.configureTestingModule({
      providers: [
        {
          provide: AuthService,
          useValue: { store: { authenticated, hasPermission }, bootstrap },
        },
        { provide: Router, useValue: { createUrlTree } },
      ],
    });
  });

  it('allows an authenticated route only when the UI permission is present', () => {
    authenticated.mockReturnValue(true);
    hasPermission.mockReturnValue(true);

    expect(runGuard()).toBe(true);
    expect(hasPermission).toHaveBeenCalledWith('users.view');
  });

  it('redirects an authenticated user when the UI permission is absent', () => {
    const deniedTree = {} as UrlTree;
    authenticated.mockReturnValue(true);
    hasPermission.mockReturnValue(false);
    createUrlTree.mockReturnValue(deniedTree);

    expect(runGuard()).toBe(deniedTree);
    expect(createUrlTree).toHaveBeenCalledWith(['/dashboard'], {
      queryParams: { denied: '/identity' },
    });
  });

  it('bootstraps the session before deciding an initially anonymous route', async () => {
    const deniedTree = {} as UrlTree;
    authenticated.mockReturnValue(false);
    hasPermission.mockReturnValue(false);
    bootstrap.mockReturnValue(of(user));
    createUrlTree.mockReturnValue(deniedTree);

    const result = runGuard() as Observable<boolean | UrlTree>;

    expect(await firstValueFrom(result)).toBe(deniedTree);
    expect(bootstrap).toHaveBeenCalledOnce();
  });

  function runGuard(): ReturnType<ReturnType<typeof permissionGuard>> {
    return TestBed.runInInjectionContext(() =>
      permissionGuard('users.view')(
        {} as ActivatedRouteSnapshot,
        { url: '/identity' } as RouterStateSnapshot,
      ),
    );
  }
});
