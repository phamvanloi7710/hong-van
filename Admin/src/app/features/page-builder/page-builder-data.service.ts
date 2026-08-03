import { HttpClient, HttpHeaders } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable, shareReplay, tap } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import {
  PageBuilderDocument,
  PageBuilderRegistry,
  PagePreviewSession,
  PageRecord,
} from './page-builder.models';

@Injectable({ providedIn: 'root' })
export class PageBuilderDataService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiBaseUrl}/page-builder`;
  private registryRequest: Observable<PageBuilderRegistry> | null = null;
  private registryVersionValue = '';

  registry(): Observable<PageBuilderRegistry> {
    this.registryRequest ??= this.http
      .get<ApiEnvelope<PageBuilderRegistry>>(`${this.baseUrl}/registry`)
      .pipe(
        map((response) => response.data),
        tap((registry) => (this.registryVersionValue = registryVersion(registry))),
        shareReplay({ bufferSize: 1, refCount: false }),
      );

    return this.registryRequest;
  }

  registryVersion(): string {
    return this.registryVersionValue;
  }

  pages(): Observable<readonly PageRecord[]> {
    return this.http
      .get<ApiEnvelope<readonly PageRecord[]>>(`${this.baseUrl}/pages`, {
        params: { per_page: 100, sort: '-updated_at' },
      })
      .pipe(map((response) => response.data));
  }

  page(publicId: string): Observable<PageRecord> {
    return this.http
      .get<ApiEnvelope<PageRecord>>(`${this.baseUrl}/pages/${publicId}`)
      .pipe(map((response) => response.data));
  }

  saveDraft(publicId: string, document: PageBuilderDocument): Observable<PageRecord> {
    return this.http
      .put<ApiEnvelope<PageRecord>>(`${this.baseUrl}/pages/${publicId}/draft`, { document })
      .pipe(map((response) => response.data));
  }

  createPreview(publicId: string, document: PageBuilderDocument, locale: string): Observable<PagePreviewSession> {
    return this.http
      .post<ApiEnvelope<PagePreviewSession>>(`${this.baseUrl}/pages/${publicId}/preview-sessions`, { document, locale })
      .pipe(map((response) => response.data));
  }

  updatePreview(session: PagePreviewSession, document: PageBuilderDocument): Observable<PagePreviewSession> {
    return this.http
      .put<ApiEnvelope<PagePreviewSession>>(`${this.baseUrl}/preview-sessions/${session.public_id}`, { document }, { headers: previewHeaders(session.token) })
      .pipe(map((response) => response.data));
  }

  refreshPreview(session: PagePreviewSession): Observable<PagePreviewSession> {
    return this.http
      .post<ApiEnvelope<PagePreviewSession>>(`${this.baseUrl}/preview-sessions/${session.public_id}/refresh`, {}, { headers: previewHeaders(session.token) })
      .pipe(map((response) => response.data));
  }

  closePreview(session: PagePreviewSession): Observable<void> {
    return this.http
      .delete<ApiEnvelope<null>>(`${this.baseUrl}/preview-sessions/${session.public_id}`, { headers: previewHeaders(session.token) })
      .pipe(map(() => undefined));
  }
}

function previewHeaders(token: string): HttpHeaders {
  return new HttpHeaders({ 'X-Preview-Token': token });
}

export function registryVersion(registry: PageBuilderRegistry): string {
  const blocks = registry.blocks
    .map((block) => `${block.type}@${block.version}`)
    .sort()
    .join('|');

  return `${registry.document.schemaVersion}:${blocks}`;
}
