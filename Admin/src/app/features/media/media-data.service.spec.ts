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

    service.list({ search: 'logo', status: 'ready', trashed: 'without' }, 2).subscribe((result) => {
      expect(result.pagination).toEqual(pagination);
      expect(result.items).toEqual([]);
    });

    const request = http.expectOne((candidate) => candidate.url === '/api/admin/v1/media');
    expect(request.request.params.get('search')).toBe('logo');
    expect(request.request.params.get('filter[status]')).toBe('ready');
    expect(request.request.params.get('filter[trashed]')).toBe('without');
    request.flush({ success: true, data: [] as MediaItem[], meta: { request_id: '01JREQUEST0000000000000000', pagination }, message: null } satisfies ApiEnvelope<MediaItem[]>);
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
