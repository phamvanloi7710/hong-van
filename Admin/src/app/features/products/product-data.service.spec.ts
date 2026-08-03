import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { ProductDataService } from './product-data.service';
import { ProductPageResult } from './product.models';

function envelope<T>(data: T): ApiEnvelope<T> {
  return { success: true, data, meta: { request_id: '01JREQUEST0000000000000000' }, message: null };
}

describe('ProductDataService', () => {
  it('sends only typed product filters to the versioned admin endpoint', () => {
    TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] });
    const service = TestBed.inject(ProductDataService);
    const http = TestBed.inject(HttpTestingController);
    let result: ProductPageResult | null = null;

    service.list({ search: 'NPK', status: 'draft', price_mode: 'contact' }, 2, 20).subscribe((value) => (result = value));

    const request = http.expectOne((candidate) => candidate.url === '/api/admin/v1/products');
    expect(request.request.method).toBe('GET');
    expect(request.request.params.get('search')).toBe('NPK');
    expect(request.request.params.get('filter[status]')).toBe('draft');
    expect(request.request.params.get('filter[price_mode]')).toBe('contact');
    expect(request.request.params.get('page')).toBe('2');
    request.flush({ ...envelope([]), meta: { request_id: '01JREQUEST0000000000000000', pagination: { page: 2, last_page: 2, per_page: 20, total: 21 } } });
    expect(result).toEqual({ items: [], pagination: { page: 2, last_page: 2, per_page: 20, total: 21 } });
    http.verify();
  });

  it('uses the dedicated bulk endpoint for publish and archive actions', () => {
    TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] });
    const service = TestBed.inject(ProductDataService);
    const http = TestBed.inject(HttpTestingController);
    let updated = 0;

    service.bulk('publish', ['01JPRODUCT0000000000000000']).subscribe((value) => (updated = value));

    const request = http.expectOne('/api/admin/v1/products/bulk');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({ action: 'publish', product_ids: ['01JPRODUCT0000000000000000'] });
    request.flush(envelope({ updated: 1 }));
    expect(updated).toBe(1);
    http.verify();
  });
});
