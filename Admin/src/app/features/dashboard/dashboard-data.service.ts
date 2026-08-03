import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import {
  DashboardNotification,
  DashboardNotificationPage,
  DashboardRange,
  DashboardReport,
  DashboardSnapshot,
} from './dashboard.models';

@Injectable({ providedIn: 'root' })
export class DashboardDataService {
  private readonly http = inject(HttpClient);
  private readonly url = `${environment.apiBaseUrl}/dashboard`;

  snapshot(range: DashboardRange): Observable<DashboardSnapshot> {
    const params = new HttpParams()
      .set('from', range.from)
      .set('to', range.to)
      .set('timezone', range.timezone);
    return this.http
      .get<ApiEnvelope<DashboardSnapshot>>(this.url, { params })
      .pipe(map((response) => response.data));
  }

  notifications(state: 'all' | 'unread' | 'read' = 'all'): Observable<DashboardNotificationPage> {
    return this.http
      .get<ApiEnvelope<DashboardNotificationPage>>(`${this.url}/notifications`, {
        params: new HttpParams().set('state', state),
      })
      .pipe(map((response) => response.data));
  }

  markNotificationRead(id: string): Observable<DashboardNotification> {
    return this.http
      .post<ApiEnvelope<DashboardNotification>>(`${this.url}/notifications/${id}/read`, {})
      .pipe(map((response) => response.data));
  }

  markAllNotificationsRead(): Observable<number> {
    return this.http
      .post<ApiEnvelope<{ readonly unread_count: number }>>(`${this.url}/notifications/read-all`, {})
      .pipe(map((response) => response.data.unread_count));
  }

  createLeadReport(range: DashboardRange): Observable<DashboardReport> {
    return this.http
      .post<ApiEnvelope<DashboardReport>>(`${this.url}/reports/leads`, range)
      .pipe(map((response) => response.data));
  }

  report(publicId: string): Observable<DashboardReport> {
    return this.http
      .get<ApiEnvelope<DashboardReport>>(`${this.url}/reports/${publicId}`)
      .pipe(map((response) => response.data));
  }

  downloadReport(publicId: string): Observable<Blob> {
    return this.http.get(`${this.url}/reports/${publicId}/download`, { responseType: 'blob' });
  }
}
