import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { MediaDataService } from './media-data.service';
import { MediaItem, MediaPagination } from './media.models';

describe('MediaDataService', () => {
  beforeEach(() => TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] }));

  it('maps typed media filters and pagination', () => {
    const service = TestBed.inject(MediaDataService);
    const http = TestBed.inject(HttpTestingController);
    const pagination: MediaPagination = { page: 2, last_page: 3, per_page: 24, total: 60 };

    service.list({ search: 'logo', status: 'ready', visibility: 'private', locked: true, trashed: 'without', sort: 'original_filename' }, 2).subscribe((result) => {
      expect(result.pagination).toEqual(pagination);
      expect(result.items).toEqual([]);
    });

    const request = http.expectOne((candidate) => candidate.url === '/api/admin/v1/media');
    expect(request.request.params.get('search')).toBe('logo');
    expect(request.request.params.get('filter[status]')).toBe('ready');
    expect(request.request.params.get('filter[trashed]')).toBe('without');
    expect(request.request.params.get('filter[visibility]')).toBe('private');
    expect(request.request.params.get('filter[locked]')).toBe('1');
    expect(request.request.params.get('sort')).toBe('original_filename');
    request.flush({ success: true, data: [] as MediaItem[], meta: { request_id: '01JREQUEST0000000000000000', pagination }, message: null } satisfies ApiEnvelope<MediaItem[]>);
    http.verify();
  });

  it('maps clone folder lock and visibility actions to versioned APIs', () => {
    const service = TestBed.inject(MediaDataService);
    const http = TestBed.inject(HttpTestingController);

    service.renameFolder('01JFOLDER00000000000000000', 'Campaign 2026').subscribe();
    const rename = http.expectOne('/api/admin/v1/media/folders/01JFOLDER00000000000000000');
    expect(rename.request.method).toBe('PATCH');
    expect(rename.request.body).toEqual({ name: 'Campaign 2026' });
    rename.flush({ success: true, data: {}, meta: { request_id: '01JREQUEST0000000000000000' }, message: null });

    service.setLock('01JMEDIA000000000000000000', true).subscribe();
    const lock = http.expectOne('/api/admin/v1/media/01JMEDIA000000000000000000/lock');
    expect(lock.request.body).toEqual({ locked: true });
    lock.flush({ success: true, data: {}, meta: { request_id: '01JREQUEST0000000000000000' }, message: null });

    service.setVisibility('01JMEDIA000000000000000000', 'public').subscribe();
    const visibility = http.expectOne('/api/admin/v1/media/01JMEDIA000000000000000000/visibility');
    expect(visibility.request.body).toEqual({ visibility: 'public' });
    visibility.flush({ success: true, data: {}, meta: { request_id: '01JREQUEST0000000000000000' }, message: null });
    http.verify();
  });

  it('uploads with FormData without trusting a client path', () => {
    const service = TestBed.inject(MediaDataService);
    const http = TestBed.inject(HttpTestingController);
    const file = new File(['safe'], 'safe.png', { type: 'image/png' });

    service.upload(file, '01JFOLDER00000000000000000').subscribe();
    const request = http.expectOne('/api/admin/v1/media');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toBeInstanceOf(FormData);
    expect((request.request.body as FormData).get('folder_id')).toBe('01JFOLDER00000000000000000');
    request.flush({ success: true, data: {} as MediaItem, meta: { request_id: '01JREQUEST0000000000000000' }, message: null } satisfies ApiEnvelope<MediaItem>);
    http.verify();
  });
});
