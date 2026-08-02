import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Login } from './login';

describe('Login', () => {
  let fixture: ComponentFixture<Login>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Login],
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

  it('should show the integration notice for a valid local form', () => {
    fixture.componentInstance.form.setValue({
      email: 'admin@example.test',
      password: 'password-only-for-test',
      remember: false,
    });

    fixture.componentInstance.submit();

    expect(fixture.componentInstance.integrationNoticeVisible()).toBe(true);
  });
});
