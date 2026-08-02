import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import { AuditLogEntry, AuditLogFilters, AuditLogPage, AuditPagination } from './audit.models';

@Injectable({ providedIn: 'root' })
export class AuditDataService {
  private readonly http = inject(HttpClient);
  private readonly url = `${environment.apiBaseUrl}/audit-logs`;

  list(filters: AuditLogFilters, page = 1, perPage = 20): Observable<AuditLogPage> {
    let params = new HttpParams().set('page', page).set('per_page', perPage).set('sort', '-occurred_at');

    for (const [key, value] of Object.entries(filters)) {
      if (value) params = params.set(`filter[${key}]`, value);
    }

    return this.http.get<ApiEnvelope<AuditLogEntry[]>>(this.url, { params }).pipe(
      map((response) => ({
        items: response.data,
        pagination: isPagination(response.meta.pagination)
          ? response.meta.pagination
          : { page, last_page: 1, per_page: perPage, total: response.data.length },
      })),
    );
  }
}

function isPagination(value: unknown): value is AuditPagination {
  if (typeof value !== 'object' || value === null) return false;
  const candidate = value as Partial<AuditPagination>;

  return typeof candidate.page === 'number'
    && typeof candidate.last_page === 'number'
    && typeof candidate.per_page === 'number'
    && typeof candidate.total === 'number';
}
