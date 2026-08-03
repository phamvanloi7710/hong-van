import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import { PublicThemeRecord, ThemeTokens } from './theme-studio.models';

@Injectable({ providedIn: 'root' })
export class ThemeStudioDataService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiBaseUrl}/themes`;

  active(): Observable<PublicThemeRecord> {
    return this.http.get<ApiEnvelope<PublicThemeRecord>>(`${this.baseUrl}/active`).pipe(map((response) => response.data));
  }

  saveDraft(themeId: string, tokens: ThemeTokens): Observable<PublicThemeRecord> {
    return this.http.put<ApiEnvelope<PublicThemeRecord>>(`${this.baseUrl}/${themeId}/draft`, { tokens }).pipe(map((response) => response.data));
  }

  preview(themeId: string): Observable<string> {
    return this.http.post<ApiEnvelope<{ readonly url: string }>>(`${this.baseUrl}/${themeId}/preview`, {}).pipe(map((response) => response.data.url));
  }

  publish(themeId: string): Observable<PublicThemeRecord> {
    return this.http.post<ApiEnvelope<PublicThemeRecord>>(`${this.baseUrl}/${themeId}/publish`, {}).pipe(map((response) => response.data));
  }

  rollback(themeId: string, versionId: string): Observable<PublicThemeRecord> {
    return this.http.post<ApiEnvelope<PublicThemeRecord>>(`${this.baseUrl}/${themeId}/rollback/${versionId}`, {}).pipe(map((response) => response.data));
  }
}
