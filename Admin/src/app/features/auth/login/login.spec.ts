import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap, provideRouter, Router } from '@angular/router';
import { of } from 'rxjs';

import { AdminUser } from '../../../core/auth/auth.models';
import { AuthService } from '../../../core/auth/auth.service';
import { Login } from './login';

const adminUser: AdminUser = {
  public_id: '01JADMINUSER00000000000000',
  name: 'Quản trị Hồng Vân',
  email: 'admin@example.test',
  email_verified_at: null,
  is_active: true,
  locked_at: null,
  roles: ['super_admin'],
  permissions: ['users.view'],
};

describe('Login', () => {
  let fixture: ComponentFixture<Login>;
  const login = vi.fn(() => of(adminUser));

  beforeEach(async () => {
    login.mockClear();

    await TestBed.configureTestingModule({
      imports: [Login],
      providers: [
        provideRouter([]),
        { provide: AuthService, useValue: { login } },
        {
          provide: ActivatedRoute,
          useValue: { snapshot: { queryParamMap: convertToParamMap({}) } },
        },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(Login);
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(fixture.componentInstance).toBeTruthy();
  });

  it('should keep the empty form invalid', () => {
    fixture.componentInstance.submit();
    expect(fixture.componentInstance.form.invalid).toBe(true);
  });

  it('should submit valid credentials through the auth service', () => {
    const router = TestBed.inject(Router);
    vi.spyOn(router, 'navigateByUrl').mockResolvedValue(true);

    fixture.componentInstance.form.setValue({
      email: 'admin@example.test',
      password: 'password-only-for-test',
      remember: false,
    });

    fixture.componentInstance.submit();

    expect(login).toHaveBeenCalledWith({
      email: 'admin@example.test',
      password: 'password-only-for-test',
      remember: false,
    });
    expect(router.navigateByUrl).toHaveBeenCalledWith('/dashboard');
  });
});
