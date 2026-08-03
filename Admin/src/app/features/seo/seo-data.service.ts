import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import { SeoEntityOption, SeoEntityType, SeoLocale, SeoMetaPayload, SeoMetaRecord } from './seo.models';

@Injectable({ providedIn: 'root' })
export class SeoDataService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiBaseUrl}/seo-meta`;

  entities(type: SeoEntityType, locale: SeoLocale, search = ''): Observable<readonly SeoEntityOption[]> {
    let params = new HttpParams().set('type', type).set('locale', locale).set('per_page', 100);
    if (search.trim()) params = params.set('search', search.trim());
    return this.http.get<ApiEnvelope<readonly SeoEntityOption[]>>(`${this.baseUrl}/entities`, { params }).pipe(map((response) => response.data));
  }

  show(type: SeoEntityType, publicId: string, locale: SeoLocale): Observable<SeoMetaRecord> {
    return this.http.get<ApiEnvelope<SeoMetaRecord>>(`${this.baseUrl}/${type}/${publicId}`, { params: { locale } }).pipe(map((response) => response.data));
  }

  update(type: SeoEntityType, publicId: string, payload: SeoMetaPayload): Observable<SeoMetaRecord> {
    return this.http.put<ApiEnvelope<SeoMetaRecord>>(`${this.baseUrl}/${type}/${publicId}`, payload).pipe(map((response) => response.data));
  }
}
