import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { environment } from '../../../environments/environment';
import { WarehouseDataService } from './warehouse-data.service';

describe('WarehouseDataService', () => {
  let service: WarehouseDataService;
  let http: HttpTestingController;

  beforeEach(() => { TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] }); service = TestBed.inject(WarehouseDataService); http = TestBed.inject(HttpTestingController); });
  afterEach(() => http.verify());

  it('loads warehouses facilities and services', () => {
    service.load().subscribe((data) => expect(data.warehouses).toEqual([]));
    for (const suffix of ['', '/facilities', '/services']) http.expectOne(`${environment.apiBaseUrl}/warehouses${suffix}`).flush({ data: [] });
  });

  it('uses the matching create and update endpoints', () => {
    service.save('warehouses', null, { code: 'WH' }).subscribe();
    http.expectOne(`${environment.apiBaseUrl}/warehouses`).flush({ data: {} });
    service.save('facilities', '01TEST', { code: 'SECURITY' }).subscribe();
    http.expectOne(`${environment.apiBaseUrl}/warehouses/facilities/01TEST`).flush({ data: {} });
  });
});
