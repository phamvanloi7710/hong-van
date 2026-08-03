import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { ThemeStudioDataService } from './theme-studio-data.service';
import { PublicThemeRecord } from './theme-studio.models';

describe('ThemeStudioDataService', () => {
  beforeEach(() => TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] }));

  it('loads the active public theme and keeps publish separate from draft updates', () => {
    const service = TestBed.inject(ThemeStudioDataService);
    const http = TestBed.inject(HttpTestingController);
    const record = themeRecord();

    service.active().subscribe((theme) => expect(theme.public_id).toBe('01JTHEME000000000000000000'));
    const active = http.expectOne('/api/admin/v1/themes/active');
    expect(active.request.method).toBe('GET');
    active.flush(envelope(record));

    service.publish(record.public_id).subscribe((theme) => expect(theme.key).toBe('hong-van-public'));
    const publish = http.expectOne(`/api/admin/v1/themes/${record.public_id}/publish`);
    expect(publish.request.method).toBe('POST');
    publish.flush(envelope(record));
    http.verify();
  });
});

function envelope<T>(data: T): ApiEnvelope<T> {
  return { success: true, data, meta: { request_id: '01JREQUEST0000000000000000' }, message: null };
}

function themeRecord(): PublicThemeRecord {
  const tokens = {
    colors: { brand: '#63b82e' }, fonts: { body: 'system_sans' }, sizes: { base: 16 }, spacing: { xs: 4 },
    radii: { small: 4 }, shadows: { preset: 'soft' }, containers: { max: 1200 }, buttons: { radius: 'small' },
    headings: { font_weight: 800 }, sections: { gap: 72 }, animation: { preset: 'standard' },
  };
  const draft = { public_id: '01JDRAFT00000000000000000', version_number: 2, status: 'draft' as const, checksum: 'a'.repeat(64), tokens, published_at: null, updated_at: null };
  return { public_id: '01JTHEME000000000000000000', key: 'hong-van-public', name: 'Hồng Vân Public', description: null, is_active: true, draft, published: null, versions: [draft] };
}
