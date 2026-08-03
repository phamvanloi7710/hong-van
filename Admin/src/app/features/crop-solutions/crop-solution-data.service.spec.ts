import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { environment } from '../../../environments/environment';
import { CropSolutionDataService } from './crop-solution-data.service';

describe('CropSolutionDataService', () => {
  let service: CropSolutionDataService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] });
    service = TestBed.inject(CropSolutionDataService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('sends allowlisted crop solution filters', () => {
    service.list({ search: 'rice', status: 'published', crop_id: '01TESTCROP0000000000000000' }).subscribe();
    const request = http.expectOne((candidate) => candidate.url === `${environment.apiBaseUrl}/crop-solutions`);
    expect(request.request.params.get('search')).toBe('rice');
    expect(request.request.params.get('filter[status]')).toBe('published');
    expect(request.request.params.get('filter[crop_id]')).toBe('01TESTCROP0000000000000000');
    request.flush({ success: true, data: [], meta: { request_id: 'test', pagination: { page: 1, last_page: 1, per_page: 50, total: 0 } }, message: null });
  });

  it('uses the publish and stage endpoints', () => {
    service.publishSolution('01SOLUTION000000000000000').subscribe();
    const publish = http.expectOne(`${environment.apiBaseUrl}/crop-solutions/01SOLUTION000000000000000/publish`);
    expect(publish.request.method).toBe('POST');
    publish.flush({ success: true, data: {}, meta: { request_id: 'test', pagination: null }, message: null });

    service.saveStage(null, { code: 'stage' }).subscribe();
    const stage = http.expectOne(`${environment.apiBaseUrl}/crop-solutions/stages`);
    expect(stage.request.method).toBe('POST');
    stage.flush({ success: true, data: {}, meta: { request_id: 'test', pagination: null }, message: null });
  });
});
