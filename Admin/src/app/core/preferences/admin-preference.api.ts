import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../auth/auth.models';
import { AdminPreferencesDto, AdminPreferencesUpdateDto } from './admin-preferences.model';

@Injectable({ providedIn: 'root' })
export class AdminPreferenceApi {
  private readonly http = inject(HttpClient);
  private readonly endpoint = `${environment.apiBaseUrl}/preferences`;

  get(): Observable<AdminPreferencesDto> {
    return this.http.get<ApiEnvelope<AdminPreferencesDto>>(this.endpoint).pipe(
      map((response) => response.data),
    );
  }

  update(preferences: AdminPreferencesUpdateDto): Observable<AdminPreferencesDto> {
    return this.http.put<ApiEnvelope<AdminPreferencesDto>>(this.endpoint, preferences).pipe(
      map((response) => response.data),
    );
  }

  reset(): Observable<AdminPreferencesDto> {
    return this.http.delete<ApiEnvelope<AdminPreferencesDto>>(this.endpoint).pipe(
      map((response) => response.data),
    );
  }
}
