import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { environment } from '../../../environments/environment';
import {
  BranchPayload,
  BusinessHour,
  CompanySettingGroup,
  CompanySettingsPayload,
  ContactChannelPayload,
  SettingValue,
  SocialLinkPayload,
} from './settings.models';

@Injectable({ providedIn: 'root' })
export class SettingsDataService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiBaseUrl}/settings`;

  load(): Observable<CompanySettingsPayload> {
    return this.unwrap(this.http.get<ApiEnvelope<CompanySettingsPayload>>(this.baseUrl));
  }

  updateGroup(key: string, values: Readonly<Record<string, SettingValue>>): Observable<CompanySettingGroup> {
    return this.unwrap(
      this.http.put<ApiEnvelope<CompanySettingGroup>>(`${this.baseUrl}/groups/${key}`, { values }),
    );
  }

  saveBranch(publicId: string | null, payload: BranchPayload): Observable<CompanySettingsPayload> {
    const request = publicId
      ? this.http.put<ApiEnvelope<CompanySettingsPayload>>(`${this.baseUrl}/branches/${publicId}`, payload)
      : this.http.post<ApiEnvelope<CompanySettingsPayload>>(`${this.baseUrl}/branches`, payload);
    return this.unwrap(request);
  }

  deleteBranch(publicId: string): Observable<CompanySettingsPayload> {
    return this.unwrap(this.http.delete<ApiEnvelope<CompanySettingsPayload>>(`${this.baseUrl}/branches/${publicId}`));
  }

  saveSocialLink(publicId: string | null, payload: SocialLinkPayload): Observable<CompanySettingsPayload> {
    const request = publicId
      ? this.http.put<ApiEnvelope<CompanySettingsPayload>>(`${this.baseUrl}/social-links/${publicId}`, payload)
      : this.http.post<ApiEnvelope<CompanySettingsPayload>>(`${this.baseUrl}/social-links`, payload);
    return this.unwrap(request);
  }

  deleteSocialLink(publicId: string): Observable<CompanySettingsPayload> {
    return this.unwrap(this.http.delete<ApiEnvelope<CompanySettingsPayload>>(`${this.baseUrl}/social-links/${publicId}`));
  }

  saveContactChannel(publicId: string | null, payload: ContactChannelPayload): Observable<CompanySettingsPayload> {
    const request = publicId
      ? this.http.put<ApiEnvelope<CompanySettingsPayload>>(`${this.baseUrl}/contact-channels/${publicId}`, payload)
      : this.http.post<ApiEnvelope<CompanySettingsPayload>>(`${this.baseUrl}/contact-channels`, payload);
    return this.unwrap(request);
  }

  deleteContactChannel(publicId: string): Observable<CompanySettingsPayload> {
    return this.unwrap(this.http.delete<ApiEnvelope<CompanySettingsPayload>>(`${this.baseUrl}/contact-channels/${publicId}`));
  }

  replaceBusinessHours(branchId: string | null, hours: readonly BusinessHour[]): Observable<CompanySettingsPayload> {
    const path = branchId ? `branches/${branchId}/business-hours` : 'business-hours/global';
    const normalized = hours.map(({ day_of_week, opens_at, closes_at, is_closed, note, is_active }) => ({
      day_of_week,
      opens_at,
      closes_at,
      is_closed,
      note,
      is_active,
    }));
    return this.unwrap(this.http.put<ApiEnvelope<CompanySettingsPayload>>(`${this.baseUrl}/${path}`, { hours: normalized }));
  }

  private unwrap<T>(request: Observable<ApiEnvelope<T>>): Observable<T> {
    return request.pipe(map((response) => response.data));
  }
}
