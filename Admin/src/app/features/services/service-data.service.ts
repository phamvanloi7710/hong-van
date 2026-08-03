import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import {
  ServiceCategoryItem,
  ServiceFilters,
  ServiceItem,
  ServicePageResult,
  ServicePagination,
} from './service.models';

interface ServicePageEnvelope extends ApiEnvelope<readonly ServiceItem[]> {
  readonly meta: { readonly request_id: string; readonly pagination: ServicePagination };
}

@Injectable({ providedIn: 'root' })
export class ServiceDataService {
  private readonly http = inject(HttpClient);
  private readonly url = `${environment.apiBaseUrl}/services`;

  list(filters: ServiceFilters = {}, page = 1): Observable<ServicePageResult> {
    let params = new HttpParams().set('page', page).set('per_page', 50).set('sort', 'sort_order');
    if (filters.search) params = params.set('search', filters.search);
    if (filters.status) params = params.set('filter[status]', filters.status);
    if (filters.service_type) params = params.set('filter[service_type]', filters.service_type);
    if (filters.category_id) params = params.set('filter[category_id]', filters.category_id);
    if (filters.trashed) params = params.set('filter[trashed]', filters.trashed);
    return this.http.get<ServicePageEnvelope>(this.url, { params }).pipe(
      map((response) => ({ items: response.data, pagination: response.meta.pagination })),
    );
  }

  categories(): Observable<readonly ServiceCategoryItem[]> {
    return this.unwrap(this.http.get<ApiEnvelope<readonly ServiceCategoryItem[]>>(`${this.url}/categories`));
  }

  saveService(publicId: string | null, payload: unknown): Observable<ServiceItem> {
    const request = publicId === null
      ? this.http.post<ApiEnvelope<ServiceItem>>(this.url, payload)
      : this.http.put<ApiEnvelope<ServiceItem>>(`${this.url}/${publicId}`, payload);
    return this.unwrap(request);
  }

  saveCategory(publicId: string | null, payload: unknown): Observable<ServiceCategoryItem> {
    const request = publicId === null
      ? this.http.post<ApiEnvelope<ServiceCategoryItem>>(`${this.url}/categories`, payload)
      : this.http.put<ApiEnvelope<ServiceCategoryItem>>(`${this.url}/categories/${publicId}`, payload);
    return this.unwrap(request);
  }

  publish(publicId: string): Observable<ServiceItem> {
    return this.unwrap(this.http.post<ApiEnvelope<ServiceItem>>(`${this.url}/${publicId}/publish`, {}));
  }

  archive(publicId: string): Observable<ServiceItem> {
    return this.unwrap(this.http.post<ApiEnvelope<ServiceItem>>(`${this.url}/${publicId}/archive`, {}));
  }

  restore(publicId: string): Observable<ServiceItem> {
    return this.unwrap(this.http.post<ApiEnvelope<ServiceItem>>(`${this.url}/${publicId}/restore`, {}));
  }

  deleteService(publicId: string): Observable<void> {
    return this.remove(`${this.url}/${publicId}`);
  }

  deleteCategory(publicId: string): Observable<void> {
    return this.remove(`${this.url}/categories/${publicId}`);
  }

  private remove(url: string): Observable<void> {
    return this.http.delete<ApiEnvelope<null>>(url).pipe(map(() => undefined));
  }

  private unwrap<T>(request: Observable<ApiEnvelope<T>>): Observable<T> {
    return request.pipe(map((response) => response.data));
  }
}
