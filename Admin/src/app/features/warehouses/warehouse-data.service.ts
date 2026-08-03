import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { forkJoin, map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import { WarehouseData, WarehouseItem, WarehouseKind } from './warehouse.models';

@Injectable({ providedIn: 'root' })
export class WarehouseDataService {
  private readonly http = inject(HttpClient);
  private readonly url = `${environment.apiBaseUrl}/warehouses`;

  load(): Observable<WarehouseData> {
    return forkJoin({ warehouses: this.list('warehouses'), facilities: this.list('facilities'), services: this.list('services') });
  }

  list(kind: WarehouseKind): Observable<readonly WarehouseItem[]> {
    const url = kind === 'warehouses' ? this.url : `${this.url}/${kind}`;
    return this.http.get<ApiEnvelope<readonly WarehouseItem[]>>(url).pipe(map((response) => response.data));
  }

  save(kind: WarehouseKind, publicId: string | null, payload: unknown): Observable<WarehouseItem> {
    const base = kind === 'warehouses' ? this.url : `${this.url}/${kind}`;
    const request = publicId ? this.http.put<ApiEnvelope<WarehouseItem>>(`${base}/${publicId}`, payload) : this.http.post<ApiEnvelope<WarehouseItem>>(base, payload);
    return request.pipe(map((response) => response.data));
  }

  publish(publicId: string): Observable<WarehouseItem> {
    return this.http.post<ApiEnvelope<WarehouseItem>>(`${this.url}/${publicId}/publish`, {}).pipe(map((response) => response.data));
  }

  delete(kind: WarehouseKind, publicId: string): Observable<void> {
    return this.http.delete<ApiEnvelope<null>>(`${this.url}/${kind}/${publicId}`).pipe(map(() => undefined));
  }
}
