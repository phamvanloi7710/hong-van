import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { forkJoin, map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import { TransportItem, TransportKind, TransportationData } from './transportation.models';

@Injectable({ providedIn: 'root' })
export class TransportationDataService {
  private readonly http = inject(HttpClient);
  private readonly url = `${environment.apiBaseUrl}/transportation`;

  load(): Observable<TransportationData> {
    return forkJoin({
      types: this.list('types'),
      vehicles: this.list('vehicles'),
      routes: this.list('routes'),
      areas: this.list('areas'),
    });
  }

  list(kind: TransportKind): Observable<readonly TransportItem[]> {
    return this.http.get<ApiEnvelope<readonly TransportItem[]>>(`${this.url}/${kind}`).pipe(
      map((response) => response.data),
    );
  }

  save(kind: TransportKind, publicId: string | null, payload: unknown): Observable<TransportItem> {
    const request = publicId
      ? this.http.put<ApiEnvelope<TransportItem>>(`${this.url}/${kind}/${publicId}`, payload)
      : this.http.post<ApiEnvelope<TransportItem>>(`${this.url}/${kind}`, payload);
    return request.pipe(map((response) => response.data));
  }

  publish(kind: Exclude<TransportKind, 'types'>, publicId: string): Observable<TransportItem> {
    return this.http.post<ApiEnvelope<TransportItem>>(`${this.url}/${kind}/${publicId}/publish`, {}).pipe(
      map((response) => response.data),
    );
  }

  delete(kind: TransportKind, publicId: string): Observable<void> {
    return this.http.delete<ApiEnvelope<null>>(`${this.url}/${kind}/${publicId}`).pipe(map(() => undefined));
  }
}
