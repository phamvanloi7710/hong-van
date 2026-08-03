import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { environment } from '../../../environments/environment';
import { TransportationDataService } from './transportation-data.service';

describe('TransportationDataService', () => {
  let service: TransportationDataService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] });
    service = TestBed.inject(TransportationDataService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('loads all four transportation collections', () => {
    service.load().subscribe((data) => expect(data.types).toEqual([]));
    for (const kind of ['types', 'vehicles', 'routes', 'areas']) {
      http.expectOne(`${environment.apiBaseUrl}/transportation/${kind}`).flush({ data: [], meta: { request_id: 'test' } });
    }
  });

  it('uses the matching create and update endpoints', () => {
    service.save('types', null, { code: 'TRUCK' }).subscribe();
    http.expectOne(`${environment.apiBaseUrl}/transportation/types`).flush({ data: {} });
    service.save('vehicles', '01TEST', { code: 'VH-01' }).subscribe();
    http.expectOne(`${environment.apiBaseUrl}/transportation/vehicles/01TEST`).flush({ data: {} });
  });
});
