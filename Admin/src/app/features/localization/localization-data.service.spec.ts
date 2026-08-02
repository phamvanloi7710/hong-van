import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { LocalizationDataService } from './localization-data.service';
import { LocalizationPayload } from './localization.models';

const payload: LocalizationPayload = {
  languages: [],
  missing_translations: { total_keys: 0, languages: [] },
  storage_timezone: 'UTC',
  display_timezone: 'Asia/Ho_Chi_Minh',
  generated_at: '2026-08-03T00:00:00.000000Z',
};

function envelope<T>(data: T): ApiEnvelope<T> {
  return { success: true, data, meta: { request_id: '01JREQUEST0000000000000000' }, message: null };
}

describe('LocalizationDataService', () => {
  it('loads and updates the typed localization contract', () => {
    TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] });
    const service = TestBed.inject(LocalizationDataService);
    const http = TestBed.inject(HttpTestingController);

    service.load().subscribe((result) => expect(result).toEqual(payload));
    const load = http.expectOne('/api/admin/v1/localization');
    expect(load.request.method).toBe('GET');
    load.flush(envelope(payload));

    service.updateLanguage('01JLANGUAGE000000000000000', { is_active: false }).subscribe();
    const update = http.expectOne('/api/admin/v1/localization/languages/01JLANGUAGE000000000000000');
    expect(update.request.method).toBe('PUT');
    expect(update.request.body).toEqual({ is_active: false });
    update.flush(envelope(payload));
    http.verify();
  });
});
