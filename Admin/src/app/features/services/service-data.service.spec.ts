import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ServiceDataService } from './service-data.service';

describe('ServiceDataService', () => {
  let service: ServiceDataService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({ providers: [ServiceDataService, provideHttpClient(), provideHttpClientTesting()] });
    service = TestBed.inject(ServiceDataService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('uses allowlisted service filters in the admin API request', () => {
    service.list({ search: 'logistics', status: 'published', service_type: 'general', category_id: '01ABCDEFGHJKMNPQRSTVWXYZ12' }).subscribe();
    const request = http.expectOne((candidate) => candidate.url.endsWith('/services'));
    expect(request.request.params.get('search')).toBe('logistics');
    expect(request.request.params.get('filter[status]')).toBe('published');
    expect(request.request.params.get('filter[service_type]')).toBe('general');
    request.flush({ data: [], meta: { request_id: 'test', pagination: { page: 1, last_page: 1, per_page: 50, total: 0 } } });
  });

  it('sends restore to the service restore endpoint', () => {
    service.restore('01ABCDEFGHJKMNPQRSTVWXYZ12').subscribe();
    const request = http.expectOne((candidate) => candidate.url.endsWith('/services/01ABCDEFGHJKMNPQRSTVWXYZ12/restore'));
    expect(request.request.method).toBe('POST');
    request.flush({ data: { public_id: '01ABCDEFGHJKMNPQRSTVWXYZ12' } });
  });
});
