import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { ShowcaseDataService } from './showcase-data.service';

describe('ShowcaseDataService', () => {
  let service: ShowcaseDataService; let http: HttpTestingController;
  beforeEach(() => { TestBed.configureTestingModule({ providers: [ShowcaseDataService, provideHttpClient(), provideHttpClientTesting()] }); service = TestBed.inject(ShowcaseDataService); http = TestBed.inject(HttpTestingController); });
  afterEach(() => http.verify());
  it('sends only supported filters', () => { service.list('gallery-items', { search: 'kho', status: 'published', gallery_id: '01ABCDEFGHJKMNPQRSTVWXYZ12' }).subscribe(); const request = http.expectOne((candidate) => candidate.url.endsWith('/showcase/gallery-items')); expect(request.request.params.get('filter[status]')).toBe('published'); expect(request.request.params.get('filter[gallery_id]')).toBe('01ABCDEFGHJKMNPQRSTVWXYZ12'); request.flush({ data: [], meta: { request_id: 'test', pagination: { total: 0 } } }); });
  it('uses the typed publish endpoint', () => { service.publish('projects', '01ABCDEFGHJKMNPQRSTVWXYZ12').subscribe(); const request = http.expectOne((candidate) => candidate.url.endsWith('/showcase/projects/01ABCDEFGHJKMNPQRSTVWXYZ12/publish')); expect(request.request.method).toBe('POST'); request.flush({ data: {} }); });
});
