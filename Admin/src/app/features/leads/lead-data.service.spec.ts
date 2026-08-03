import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { LeadDataService } from './lead-data.service';

describe('LeadDataService', () => {
  let service: LeadDataService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] });
    service = TestBed.inject(LeadDataService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('loads the unified inbox with typed filters', () => {
    service.list({ type: 'transport', status: 'new', assignment: 'unassigned' }).subscribe((page) => expect(page.meta.total).toBe(0));
    const request = http.expectOne((candidate) => candidate.url.endsWith('/leads'));
    expect(request.request.params.get('type')).toBe('transport');
    expect(request.request.params.get('status')).toBe('new');
    expect(request.request.params.get('assignment')).toBe('unassigned');
    request.flush({ data: { items: [], meta: { current_page: 1, last_page: 1, total: 0 } } });
  });

  it('uses dedicated append-only workflow endpoints', () => {
    service.addNote('01TESTLEAD0000000000000000', 'Follow up').subscribe();
    const request = http.expectOne((candidate) => candidate.url.endsWith('/leads/01TESTLEAD0000000000000000/notes'));
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({ body: 'Follow up' });
    request.flush({ data: {} });
  });
});
