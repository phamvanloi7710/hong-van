import { TestBed } from '@angular/core/testing';

import { AdminUser } from './auth.models';
import { AuthStore } from './auth.store';

const user: AdminUser = {
  public_id: '01JADMINUSER00000000000000',
  name: 'Quản trị Hồng Vân',
  email: 'admin@example.test',
  email_verified_at: null,
  is_active: true,
  locked_at: null,
  roles: ['content_editor'],
  permissions: ['users.view', 'roles.view'],
};

describe('AuthStore permissions', () => {
  it('reflects the refreshed permission list from the authenticated profile', () => {
    const store = TestBed.inject(AuthStore);

    store.markAuthenticated(user);
    expect(store.hasPermission('users.view')).toBe(true);
    expect(store.hasPermission('users.update')).toBe(false);

    store.markAuthenticated({ ...user, permissions: ['users.update'] });
    expect(store.hasPermission('users.view')).toBe(false);
    expect(store.hasPermission('users.update')).toBe(true);
  });
});
