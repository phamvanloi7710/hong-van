import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { SeoDataService } from './seo-data.service';
import { SeoEntityOption, SeoMetaRecord } from './seo.models';

describe('SeoDataService', () => {
  beforeEach(() => TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] }));

  it('loads only typed entity and locale query parameters', () => {
    const service = TestBed.inject(SeoDataService);
    const http = TestBed.inject(HttpTestingController);

    service.entities('product', 'vi', 'NPK').subscribe((items) => expect(items).toEqual([]));
    const request = http.expectOne((candidate) => candidate.url === '/api/admin/v1/seo-meta/entities');
    expect(request.request.method).toBe('GET');
    expect(request.request.params.get('type')).toBe('product');
    expect(request.request.params.get('locale')).toBe('vi');
    expect(request.request.params.get('search')).toBe('NPK');
    request.flush(envelope([] as SeoEntityOption[]));
    http.verify();
  });

  it('uses the typed entity route for read and update', () => {
    const service = TestBed.inject(SeoDataService);
    const http = TestBed.inject(HttpTestingController);
    const record = seoRecord();

    service.show('post', '01JSEOENTITY00000000000000', 'en').subscribe((result) => expect(result.locale).toBe('en'));
    const show = http.expectOne('/api/admin/v1/seo-meta/post/01JSEOENTITY00000000000000?locale=en');
    expect(show.request.method).toBe('GET');
    show.flush(envelope(record));

    service.update('post', '01JSEOENTITY00000000000000', {
      locale: 'en', meta_title: null, meta_description: null, canonical_url: null, robots_index: true,
      robots_follow: true, og_title: null, og_description: null, og_image_media_id: null, og_type: 'article',
      twitter_card: 'summary_large_image', twitter_title: null, twitter_description: null, focus_keywords: [],
    }).subscribe((result) => expect(result.og_type).toBe('article'));
    const update = http.expectOne('/api/admin/v1/seo-meta/post/01JSEOENTITY00000000000000');
    expect(update.request.method).toBe('PUT');
    update.flush(envelope({ ...record, og_type: 'article' }));
    http.verify();
  });
});

function envelope<T>(data: T): ApiEnvelope<T> {
  return { success: true, data, meta: { request_id: '01JREQUEST0000000000000000' }, message: null };
}

function seoRecord(): SeoMetaRecord {
  return {
    public_id: null, locale: 'en', meta_title: null, meta_description: null, canonical_url: null,
    robots_index: true, robots_follow: true, og_title: null, og_description: null, og_image: null,
    og_type: 'website', twitter_card: 'summary_large_image', twitter_title: null,
    twitter_description: null, focus_keywords: [], updated_at: null,
  };
}
