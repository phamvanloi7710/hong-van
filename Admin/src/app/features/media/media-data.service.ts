import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import { MediaFilters, MediaFolder, MediaItem, MediaPageResult, MediaPagination } from './media.models';

@Injectable({ providedIn: 'root' })
export class MediaDataService {
  private readonly http = inject(HttpClient);
  private readonly url = `${environment.apiBaseUrl}/media`;

  list(filters: MediaFilters = {}, page = 1, perPage = 24): Observable<MediaPageResult> {
    let params = new HttpParams().set('page', page).set('per_page', perPage).set('sort', '-created_at');

    if (filters.search) params = params.set('search', filters.search);
    for (const key of ['status', 'mime_type', 'folder_id', 'trashed'] as const) {
      const value = filters[key];
      if (value) params = params.set(`filter[${key}]`, value);
    }

    return this.http.get<ApiEnvelope<MediaItem[]>>(this.url, { params }).pipe(map((response) => ({
      items: response.data,
      pagination: isPagination(response.meta.pagination)
        ? response.meta.pagination
        : { page, last_page: 1, per_page: perPage, total: response.data.length },
    })));
  }

  folders(): Observable<readonly MediaFolder[]> {
    return this.http.get<ApiEnvelope<MediaFolder[]>>(`${this.url}/folders`).pipe(map((response) => response.data));
  }

  upload(file: File, folderId: string | null = null): Observable<MediaItem> {
    const body = new FormData();
    body.append('file', file, file.name);
    if (folderId) body.append('folder_id', folderId);

    return this.http.post<ApiEnvelope<MediaItem>>(this.url, body).pipe(map((response) => response.data));
  }

  update(publicId: string, payload: { readonly title: string | null; readonly alt_text: string | null; readonly caption: string | null }): Observable<MediaItem> {
    return this.http.patch<ApiEnvelope<MediaItem>>(`${this.url}/${publicId}`, payload).pipe(map((response) => response.data));
  }

  move(publicId: string, folderId: string | null): Observable<MediaItem> {
    return this.http.patch<ApiEnvelope<MediaItem>>(`${this.url}/${publicId}/move`, { folder_id: folderId }).pipe(map((response) => response.data));
  }

  trash(publicId: string): Observable<MediaItem> {
    return this.http.post<ApiEnvelope<MediaItem>>(`${this.url}/${publicId}/trash`, {}).pipe(map((response) => response.data));
  }

  restore(publicId: string): Observable<MediaItem> {
    return this.http.post<ApiEnvelope<MediaItem>>(`${this.url}/${publicId}/restore`, {}).pipe(map((response) => response.data));
  }

  retry(publicId: string): Observable<MediaItem> {
    return this.http.post<ApiEnvelope<MediaItem>>(`${this.url}/${publicId}/retry`, {}).pipe(map((response) => response.data));
  }

  delete(publicId: string): Observable<void> {
    return this.http.delete<ApiEnvelope<null>>(`${this.url}/${publicId}`).pipe(map(() => undefined));
  }
}

function isPagination(value: unknown): value is MediaPagination {
  if (typeof value !== 'object' || value === null) return false;
  const candidate = value as Partial<MediaPagination>;

  return typeof candidate.page === 'number'
    && typeof candidate.last_page === 'number'
    && typeof candidate.per_page === 'number'
    && typeof candidate.total === 'number';
}
