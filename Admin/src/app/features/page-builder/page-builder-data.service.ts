import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable, shareReplay, tap } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import { PageBuilderDocument, PageBuilderRegistry, PageRecord } from './page-builder.models';

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
}

export function registryVersion(registry: PageBuilderRegistry): string {
  const blocks = registry.blocks
    .map((block) => `${block.type}@${block.version}`)
    .sort()
    .join('|');

  return `${registry.document.schemaVersion}:${blocks}`;
}
