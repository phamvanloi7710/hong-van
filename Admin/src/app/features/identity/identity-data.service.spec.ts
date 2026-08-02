import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { IdentityDataService } from './identity-data.service';
import { IdentityUser } from './identity.models';

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
});
