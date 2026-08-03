import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import { ShowcaseFilters, ShowcaseItem, ShowcaseKind, ShowcasePageResult } from './showcase.models';

interface PageEnvelope extends ApiEnvelope<readonly ShowcaseItem[]> { readonly meta: { readonly request_id: string; readonly pagination: { readonly total: number } }; }

@Injectable({ providedIn: 'root' })
export class ShowcaseDataService {
  private readonly http = inject(HttpClient);
  private readonly url = `${environment.apiBaseUrl}/showcase`;

  list(kind: ShowcaseKind, filters: ShowcaseFilters = {}): Observable<ShowcasePageResult> {
    let params = new HttpParams().set('per_page', 100).set('sort', 'sort_order');
    if (filters.search) params = params.set('search', filters.search);
    if (filters.status) params = params.set('filter[status]', filters.status);
    if (filters.trashed) params = params.set('filter[trashed]', filters.trashed);
    if (filters.gallery_id) params = params.set('filter[gallery_id]', filters.gallery_id);
    return this.http.get<PageEnvelope>(`${this.url}/${kind}`, { params }).pipe(map((response) => ({ items: response.data, total: response.meta.pagination.total })));
  }
  save(kind: ShowcaseKind, publicId: string | null, payload: unknown): Observable<ShowcaseItem> {
    return this.unwrap(publicId === null ? this.http.post<ApiEnvelope<ShowcaseItem>>(`${this.url}/${kind}`, payload) : this.http.put<ApiEnvelope<ShowcaseItem>>(`${this.url}/${kind}/${publicId}`, payload));
  }
  publish(kind: ShowcaseKind, id: string): Observable<ShowcaseItem> { return this.unwrap(this.http.post<ApiEnvelope<ShowcaseItem>>(`${this.url}/${kind}/${id}/publish`, {})); }
  archive(kind: ShowcaseKind, id: string): Observable<ShowcaseItem> { return this.unwrap(this.http.post<ApiEnvelope<ShowcaseItem>>(`${this.url}/${kind}/${id}/archive`, {})); }
  restore(kind: ShowcaseKind, id: string): Observable<ShowcaseItem> { return this.unwrap(this.http.post<ApiEnvelope<ShowcaseItem>>(`${this.url}/${kind}/${id}/restore`, {})); }
  delete(kind: ShowcaseKind, id: string): Observable<void> { return this.http.delete<ApiEnvelope<null>>(`${this.url}/${kind}/${id}`).pipe(map(() => undefined)); }
  private unwrap<T>(request: Observable<ApiEnvelope<T>>): Observable<T> { return request.pipe(map((response) => response.data)); }
}
