import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { SettingsDataService } from './settings-data.service';
import { CompanySettingsPayload } from './settings.models';

const payload: CompanySettingsPayload = {
  groups: [], branches: [], business_hours: [], social_links: [], contact_channels: [],
};

function envelope<T>(data: T): ApiEnvelope<T> {
  return { success: true, data, meta: { request_id: '01JREQUEST0000000000000000' }, message: null };
}

describe('SettingsDataService', () => {
  it('loads the typed company settings contract from the versioned endpoint', () => {
    TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] });
    const service = TestBed.inject(SettingsDataService);
    const http = TestBed.inject(HttpTestingController);
    let result: CompanySettingsPayload | null = null;

    service.load().subscribe((value) => (result = value));

    const request = http.expectOne('/api/admin/v1/settings');
    expect(request.request.method).toBe('GET');
    request.flush(envelope(payload));
    expect(result).toEqual(payload);
    http.verify();
  });

  it('normalizes business-hour fields before writing a branch scope', () => {
    TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] });
    const service = TestBed.inject(SettingsDataService);
    const http = TestBed.inject(HttpTestingController);

    service.replaceBusinessHours('01JBRANCH0000000000000000', [{
      public_id: '01JHOUR000000000000000000', branch_id: '01JBRANCH0000000000000000',
      day_of_week: 1, opens_at: '08:00', closes_at: '17:00', is_closed: false,
      note: null, is_active: true, sort_order: 0,
    }]).subscribe();

    const request = http.expectOne('/api/admin/v1/settings/branches/01JBRANCH0000000000000000/business-hours');
    expect(request.request.method).toBe('PUT');
    expect(request.request.body.hours[0]).toEqual({ day_of_week: 1, opens_at: '08:00', closes_at: '17:00', is_closed: false, note: null, is_active: true });
    request.flush(envelope(payload));
    http.verify();
  });
});
