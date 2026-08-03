import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { PageBuilderDataService, registryVersion } from './page-builder-data.service';
import { PageBuilderRegistry, PagePreviewSession, PageRecord, emptyPageBuilderDocument } from './page-builder.models';

describe('PageBuilderDataService', () => {
  beforeEach(() =>
    TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] }),
  );

  it('caches registry metadata by deterministic schema and block versions', () => {
    const service = TestBed.inject(PageBuilderDataService);
    const http = TestBed.inject(HttpTestingController);
    const registry = registryFixture();
    let emissions = 0;

    service.registry().subscribe(() => (emissions += 1));
    service.registry().subscribe(() => (emissions += 1));
    const request = http.expectOne('/api/admin/v1/page-builder/registry');
    request.flush(envelope(registry));

    expect(emissions).toBe(2);
    expect(service.registryVersion()).toBe('1:content.text@2|layout.section@1');
    expect(registryVersion(registry)).toBe(service.registryVersion());
    http.verify();
  });

  it('keeps draft writes in the typed data service', () => {
    const service = TestBed.inject(PageBuilderDataService);
    const http = TestBed.inject(HttpTestingController);
    const document = emptyPageBuilderDocument(1);
    const page = pageFixture(document);

    service.saveDraft(page.public_id, document).subscribe((saved) =>
      expect(saved.draft?.document).toEqual(document),
    );
    const request = http.expectOne(`/api/admin/v1/page-builder/pages/${page.public_id}/draft`);
    expect(request.request.method).toBe('PUT');
    expect(request.request.body).toEqual({ document });
    request.flush(envelope(page));
    http.verify();
  });

  it('keeps preview create update refresh and close token-scoped', () => {
    const service = TestBed.inject(PageBuilderDataService);
    const http = TestBed.inject(HttpTestingController);
    const document = emptyPageBuilderDocument(1);
    const session = previewFixture();

    service.createPreview('01JPAGE', document, 'vi').subscribe();
    const create = http.expectOne('/api/admin/v1/page-builder/pages/01JPAGE/preview-sessions');
    expect(create.request.method).toBe('POST');
    expect(create.request.body).toEqual({ document, locale: 'vi' });
    create.flush(envelope(session));

    service.updatePreview(session, document).subscribe();
    const update = http.expectOne(`/api/admin/v1/page-builder/preview-sessions/${session.public_id}`);
    expect(update.request.method).toBe('PUT');
    expect(update.request.headers.get('X-Preview-Token')).toBe(session.token);
    update.flush(envelope({ ...session, revision: 2 }));

    service.refreshPreview(session).subscribe();
    const refresh = http.expectOne(`/api/admin/v1/page-builder/preview-sessions/${session.public_id}/refresh`);
    expect(refresh.request.headers.get('X-Preview-Token')).toBe(session.token);
    refresh.flush(envelope(session));

    service.closePreview(session).subscribe();
    const close = http.expectOne(`/api/admin/v1/page-builder/preview-sessions/${session.public_id}`);
    expect(close.request.method).toBe('DELETE');
    expect(close.request.headers.get('X-Preview-Token')).toBe(session.token);
    close.flush(envelope(null));
    http.verify();
  });
});

function envelope<T>(data: T): ApiEnvelope<T> {
  return { success: true, data, meta: { request_id: '01JREQUEST0000000000000000' }, message: null };
}

function registryFixture(): PageBuilderRegistry {
  const definition = (type: string, version: number) => ({
    type,
    version,
    labels: { vi: type, en: type, zh: type },
    category: 'layout',
    icon: 'widgets',
    thumbnail: null,
    schema: { props: {}, style: {}, visibility: {}, bindings: {} },
    defaults: {
      props: {}, style: { desktop: {}, tablet: {}, mobile: {} },
      visibility: { desktop: true, tablet: true, mobile: true }, bindings: {}, children: [],
    },
    allowRoot: true,
    allowedParents: [],
    allowedChildren: [],
    maxDepth: 12,
    minChildren: 0,
    maxChildren: 20,
    dataDependencies: [],
    permissions: [],
    cacheTags: [],
  });

  return {
    document: {
      schemaVersion: 1,
      limits: { maxBytes: 524288, maxDepth: 12, maxBlocks: 300 },
      blockFields: [],
      pageSettings: { container: ['default', 'wide', 'full'], background: ['surface', 'muted', 'brand'] },
    },
    blocks: [definition('layout.section', 1), definition('content.text', 2)],
    dataSources: [],
    forms: [],
    cache: {},
  };
}

function pageFixture(document: ReturnType<typeof emptyPageBuilderDocument>): PageRecord {
  return {
    public_id: '01JPAGE0000000000000000000',
    code: 'home',
    type: 'standard',
    status: 'draft',
    is_home: true,
    translations: [{ locale: 'vi', title: 'Trang chủ', navigation_label: 'Trang chủ', slug: 'trang-chu' }],
    draft: {
      public_id: '01JDRAFT00000000000000000', version_number: 1, status: 'draft', schema_version: 1,
      checksum: 'a'.repeat(64), document, published_at: null, updated_at: null,
    },
    published: null,
    created_at: null,
    updated_at: null,
  };
}

function previewFixture(): PagePreviewSession {
  return {
    public_id: '01JPREVIEW0000000000000000',
    token: 't'.repeat(64),
    url: 'http://hongvan.local/preview/page-builder/token?expires=1&signature=test',
    expires_at: '2026-08-03T12:00:00.000Z',
    ttl_seconds: 300,
    revision: 1,
    message_schema_version: 1,
  };
}
