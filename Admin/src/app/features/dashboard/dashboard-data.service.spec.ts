import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { DashboardDataService } from './dashboard-data.service';
import { DashboardReport } from './dashboard.models';

describe('DashboardDataService', () => {
  beforeEach(() => TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] }));

  it('sends the selected range and maps a queued report', () => {
    const service = TestBed.inject(DashboardDataService);
    const http = TestBed.inject(HttpTestingController);
    const report: DashboardReport = { public_id: '01KREPORT0000000000000000', type: 'leads', status: 'queued', row_count: 1001, expires_at: '2026-08-04T00:00:00Z', created_at: '2026-08-03T00:00:00Z', download_url: null };

    service.createLeadReport({ from: '2026-08-01', to: '2026-08-03', timezone: 'Asia/Ho_Chi_Minh' }).subscribe((value) => expect(value).toEqual(report));
    const request = http.expectOne('/api/admin/v1/dashboard/reports/leads');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({ from: '2026-08-01', to: '2026-08-03', timezone: 'Asia/Ho_Chi_Minh' });
    request.flush({ success: true, data: report, meta: { request_id: '01KTEST00000000000000000' }, message: null } satisfies ApiEnvelope<DashboardReport>);
    http.verify();
  });
});
