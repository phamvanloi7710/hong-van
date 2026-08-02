import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import { LocalizationPayload, UpdateLanguagePayload } from './localization.models';

@Injectable({ providedIn: 'root' })
export class LocalizationDataService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiBaseUrl}/localization`;

  load(): Observable<LocalizationPayload> {
    return this.unwrap(this.http.get<ApiEnvelope<LocalizationPayload>>(this.baseUrl));
  }

  updateLanguage(publicId: string, payload: UpdateLanguagePayload): Observable<LocalizationPayload> {
    return this.unwrap(
      this.http.put<ApiEnvelope<LocalizationPayload>>(`${this.baseUrl}/languages/${publicId}`, payload),
    );
  }

  private unwrap<T>(request: Observable<ApiEnvelope<T>>): Observable<T> {
    return request.pipe(map((response) => response.data));
  }
}
