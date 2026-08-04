import { HttpClient, HttpHeaders } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable, shareReplay, tap } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import {
  PageBuilderDocument,
  PageBuilderRegistry,
  PagePreviewSession,
  PageLockRecord,
  PageLockSession,
  PageRecord,
  PageTemplateRecord,
  PageVersionRecord,
  PagePublishScheduleRecord,
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

  saveDraft(publicId: string, document: PageBuilderDocument, expectedChecksum: string | null, expectedVersionId: string | null, lockToken: string | null = null): Observable<PageRecord> {
    return this.http
      .put<ApiEnvelope<PageRecord>>(`${this.baseUrl}/pages/${publicId}/draft`, { document, expected_checksum: expectedChecksum, expected_version_id: expectedVersionId, ...(lockToken === null ? {} : { lock_token: lockToken }) })
      .pipe(map((response) => response.data));
  }

  versions(publicId: string): Observable<readonly PageVersionRecord[]> {
    return this.http.get<ApiEnvelope<readonly PageVersionRecord[]>>(`${this.baseUrl}/pages/${publicId}/versions`).pipe(map((response) => response.data));
  }

  saveVersion(publicId: string, expectedChecksum: string, expectedVersionId: string, note: string | null): Observable<PageRecord> {
    return this.http.post<ApiEnvelope<PageRecord>>(`${this.baseUrl}/pages/${publicId}/versions`, { expected_checksum: expectedChecksum, expected_version_id: expectedVersionId, note }).pipe(map((response) => response.data));
  }

  publish(publicId: string, expectedChecksum: string, expectedVersionId: string, note: string | null): Observable<PageRecord> {
    return this.http.post<ApiEnvelope<PageRecord>>(`${this.baseUrl}/pages/${publicId}/publish`, { expected_checksum: expectedChecksum, expected_version_id: expectedVersionId, note }).pipe(map((response) => response.data));
  }

  schedule(publicId: string, expectedChecksum: string, expectedVersionId: string, scheduledAt: string, timezone: string, note: string | null): Observable<PagePublishScheduleRecord> {
    return this.http.post<ApiEnvelope<PagePublishScheduleRecord>>(`${this.baseUrl}/pages/${publicId}/schedule`, { expected_checksum: expectedChecksum, expected_version_id: expectedVersionId, scheduled_at: scheduledAt, timezone, note }).pipe(map((response) => response.data));
  }

  rollback(publicId: string, versionId: string, note: string | null): Observable<PageRecord> {
    return this.http.post<ApiEnvelope<PageRecord>>(`${this.baseUrl}/pages/${publicId}/versions/${versionId}/rollback`, { note }).pipe(map((response) => response.data));
  }

  templates(): Observable<readonly PageTemplateRecord[]> {
    return this.http.get<ApiEnvelope<readonly PageTemplateRecord[]>>(`${this.baseUrl}/templates`).pipe(map((response) => response.data));
  }

  saveAsTemplate(publicId: string, key: string, name: string, categoryKey: string | null): Observable<PageTemplateRecord> {
    return this.http.post<ApiEnvelope<PageTemplateRecord>>(`${this.baseUrl}/pages/${publicId}/templates`, { key, name, category_key: categoryKey }).pipe(map((response) => response.data));
  }

  exportPage(publicId: string): Observable<unknown> {
    return this.http.get<unknown>(`${this.baseUrl}/pages/${publicId}/export`);
  }

  validateImport(payload: unknown): Observable<{ readonly valid: boolean; readonly migrated_schema_version: number; readonly media_references: readonly string[]; readonly missing_media: readonly string[] }> {
    return this.http.post<ApiEnvelope<{ readonly valid: boolean; readonly migrated_schema_version: number; readonly media_references: readonly string[]; readonly missing_media: readonly string[] }>>(`${this.baseUrl}/imports/validate`, { payload }).pipe(map((response) => response.data));
  }

  acquireLock(publicId: string): Observable<PageLockSession> {
    return this.http.post<ApiEnvelope<PageLockSession>>(`${this.baseUrl}/pages/${publicId}/lock`, {}).pipe(map((response) => response.data));
  }

  heartbeatLock(publicId: string, token: string): Observable<PageLockRecord> {
    return this.http.put<ApiEnvelope<PageLockRecord>>(`${this.baseUrl}/pages/${publicId}/lock`, { token }).pipe(map((response) => response.data));
  }

  releaseLock(publicId: string, token: string): Observable<void> {
    return this.http.delete<ApiEnvelope<null>>(`${this.baseUrl}/pages/${publicId}/lock`, { body: { token } }).pipe(map(() => undefined));
  }

  forceReleaseLock(publicId: string): Observable<void> {
    return this.http.delete<ApiEnvelope<null>>(`${this.baseUrl}/pages/${publicId}/lock/force`).pipe(map(() => undefined));
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
