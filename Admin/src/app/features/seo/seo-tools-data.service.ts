import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import { SeoLocale } from './seo.models';
import { RedirectPayload, RedirectRule, SchemaPreview, SitemapHealth } from './seo-tools.models';

@Injectable({ providedIn: 'root' })
export class SeoToolsDataService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiBaseUrl}/seo-tools`;

  redirects(): Observable<readonly RedirectRule[]> {
    return this.http.get<ApiEnvelope<readonly RedirectRule[]>>(`${this.baseUrl}/redirects`).pipe(map((response) => response.data));
  }

  saveRedirect(publicId: string | null, payload: RedirectPayload): Observable<RedirectRule> {
    const request = publicId
      ? this.http.put<ApiEnvelope<RedirectRule>>(`${this.baseUrl}/redirects/${publicId}`, payload)
      : this.http.post<ApiEnvelope<RedirectRule>>(`${this.baseUrl}/redirects`, payload);
    return request.pipe(map((response) => response.data));
  }

  deleteRedirect(publicId: string): Observable<void> {
    return this.http.delete<ApiEnvelope<null>>(`${this.baseUrl}/redirects/${publicId}`).pipe(map(() => undefined));
  }

  health(): Observable<SitemapHealth> {
    return this.http.get<ApiEnvelope<SitemapHealth>>(`${this.baseUrl}/health`).pipe(map((response) => response.data));
  }

  regenerate(): Observable<void> {
    return this.http.post<ApiEnvelope<{ readonly queued: boolean }>>(`${this.baseUrl}/regenerate`, {}).pipe(map(() => undefined));
  }

  saveRobots(disallowPaths: string): Observable<string> {
    return this.http.put<ApiEnvelope<{ readonly disallow_paths: string }>>(`${this.baseUrl}/robots`, { disallow_paths: disallowPaths })
      .pipe(map((response) => response.data.disallow_paths));
  }

  schemaPreview(type: SchemaPreview['type'], locale: SeoLocale): Observable<SchemaPreview> {
    const params = new HttpParams().set('type', type).set('locale', locale);
    return this.http.get<ApiEnvelope<SchemaPreview>>(`${this.baseUrl}/schema-preview`, { params }).pipe(map((response) => response.data));
  }
}
