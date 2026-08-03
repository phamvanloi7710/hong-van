import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import { Lead, LeadAssignee, LeadFilters, LeadMetrics, LeadPage, LeadStatus } from './lead.models';

@Injectable({ providedIn: 'root' })
export class LeadDataService {
  private readonly http = inject(HttpClient);
  private readonly url = `${environment.apiBaseUrl}/leads`;

  list(filters: LeadFilters): Observable<LeadPage> {
    let params = new HttpParams();
    if (filters.type) params = params.set('type', filters.type);
    if (filters.status) params = params.set('status', filters.status);
    if (filters.assignment) params = params.set('assignment', filters.assignment);
    return this.http.get<ApiEnvelope<LeadPage>>(this.url, { params }).pipe(map((response) => response.data));
  }

  show(publicId: string): Observable<Lead> {
    return this.http.get<ApiEnvelope<Lead>>(`${this.url}/${publicId}`).pipe(map((response) => response.data));
  }

  metrics(): Observable<LeadMetrics> {
    return this.http.get<ApiEnvelope<LeadMetrics>>(`${this.url}/metrics`).pipe(map((response) => response.data));
  }

  assignees(): Observable<readonly LeadAssignee[]> {
    return this.http.get<ApiEnvelope<readonly LeadAssignee[]>>(`${this.url}/assignees`).pipe(map((response) => response.data));
  }

  changeStatus(publicId: string, status: LeadStatus): Observable<Lead> {
    return this.http.post<ApiEnvelope<Lead>>(`${this.url}/${publicId}/status`, { status }).pipe(map((response) => response.data));
  }

  assign(publicId: string, userId: string | null): Observable<Lead> {
    return this.http.post<ApiEnvelope<Lead>>(`${this.url}/${publicId}/assign`, { user_id: userId }).pipe(map((response) => response.data));
  }

  addNote(publicId: string, body: string): Observable<Lead> {
    return this.http.post<ApiEnvelope<Lead>>(`${this.url}/${publicId}/notes`, { body }).pipe(map((response) => response.data));
  }

  scheduleFollowUp(publicId: string, nextFollowUpAt: string | null): Observable<Lead> {
    return this.http
      .put<ApiEnvelope<Lead>>(`${this.url}/${publicId}/follow-up`, { next_follow_up_at: nextFollowUpAt })
      .pipe(map((response) => response.data));
  }

  export(filters: Pick<LeadFilters, 'type' | 'status'>): Observable<Blob> {
    let params = new HttpParams();
    if (filters.type) params = params.set('type', filters.type);
    if (filters.status) params = params.set('status', filters.status);
    return this.http.get(`${this.url}/export`, { params, responseType: 'blob' });
  }
}
