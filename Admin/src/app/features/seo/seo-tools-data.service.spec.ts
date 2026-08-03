import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { SeoToolsDataService } from './seo-tools-data.service';

describe('SeoToolsDataService', () => {
  beforeEach(() => TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] }));

  it('uses typed redirect CRUD endpoints', () => {
    const service = TestBed.inject(SeoToolsDataService);
    const http = TestBed.inject(HttpTestingController);
    service.saveRedirect(null, { source_path: '/old', target_path: '/new', locale: '*', status_code: 301, is_active: true, note: null }).subscribe();
    const create = http.expectOne('/api/admin/v1/seo-tools/redirects');
    expect(create.request.method).toBe('POST');
    create.flush(envelope({ public_id: '01JREDIRECT000000000000000', source_path: '/old', target_path: '/new', locale: '*', status_code: 301, is_active: true, hit_count: 0, last_hit_at: null, note: null }));
    service.deleteRedirect('01JREDIRECT000000000000000').subscribe();
    const remove = http.expectOne('/api/admin/v1/seo-tools/redirects/01JREDIRECT000000000000000');
    expect(remove.request.method).toBe('DELETE');
    remove.flush(envelope(null));
    http.verify();
  });

  it('loads sitemap health and allowlisted schema preview params', () => {
    const service = TestBed.inject(SeoToolsDataService);
    const http = TestBed.inject(HttpTestingController);
    service.schemaPreview('organization', 'zh').subscribe();
    const preview = http.expectOne('/api/admin/v1/seo-tools/schema-preview?type=organization&locale=zh');
    expect(preview.request.method).toBe('GET');
    preview.flush(envelope({ type: 'organization', locale: 'zh', schema: { '@type': 'Organization' }, json: '{}' }));
    http.verify();
  });
});

function envelope<T>(data: T): ApiEnvelope<T> {
  return { success: true, data, meta: { request_id: '01JREQUEST0000000000000000' }, message: null };
}
