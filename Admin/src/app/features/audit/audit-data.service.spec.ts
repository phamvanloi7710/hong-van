import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { AuditDataService } from './audit-data.service';
import { AuditLogEntry, AuditPagination } from './audit.models';

describe('AuditDataService', () => {
  it('uses allowlisted filter parameters and maps pagination', () => {
    TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] });
    const service = TestBed.inject(AuditDataService);
    const http = TestBed.inject(HttpTestingController);
    const pagination: AuditPagination = { page: 2, last_page: 3, per_page: 20, total: 42 };

    service.list({ action: 'identity.user', subject_type: 'user' }, 2).subscribe((result) => {
      expect(result.pagination).toEqual(pagination);
      expect(result.items).toEqual([]);
    });

    const request = http.expectOne((candidate) => candidate.url === '/api/admin/v1/audit-logs');
    expect(request.request.method).toBe('GET');
    expect(request.request.params.get('filter[action]')).toBe('identity.user');
    expect(request.request.params.get('filter[subject_type]')).toBe('user');
    expect(request.request.params.get('page')).toBe('2');
    request.flush({ success: true, data: [] as AuditLogEntry[], meta: { request_id: '01JREQUEST0000000000000000', pagination }, message: null } satisfies ApiEnvelope<AuditLogEntry[]>);
    http.verify();
  });
});
