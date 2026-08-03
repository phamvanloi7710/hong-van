import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { PostDataService } from './post-data.service';

describe('PostDataService', () => {
  let service: PostDataService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({ providers: [PostDataService, provideHttpClient(), provideHttpClientTesting()] });
    service = TestBed.inject(PostDataService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('sends allowlisted post filters to the admin API', () => {
    service.list({ search: 'phân bón', status: 'scheduled', category_id: '01ABCDEFGHJKMNPQRSTVWXYZ12', trashed: 'without' }).subscribe();
    const request = http.expectOne((candidate) => candidate.url.endsWith('/posts'));
    expect(request.request.params.get('search')).toBe('phân bón');
    expect(request.request.params.get('filter[status]')).toBe('scheduled');
    expect(request.request.params.get('filter[category_id]')).toBe('01ABCDEFGHJKMNPQRSTVWXYZ12');
    request.flush({ data: [], meta: { request_id: 'test', pagination: { page: 1, last_page: 1, per_page: 50, total: 0 } } });
  });

  it('uses dedicated taxonomy endpoints', () => {
    service.categories().subscribe();
    http.expectOne((candidate) => candidate.url.endsWith('/posts/categories')).flush({ data: [] });
    service.tags().subscribe();
    http.expectOne((candidate) => candidate.url.endsWith('/posts/tags')).flush({ data: [] });
    service.authors().subscribe();
    http.expectOne((candidate) => candidate.url.endsWith('/posts/authors')).flush({ data: [] });
  });

  it('sends publishing and restore commands to the post endpoints', () => {
    const id = '01ABCDEFGHJKMNPQRSTVWXYZ12';
    service.publish(id).subscribe();
    const publish = http.expectOne((candidate) => candidate.url.endsWith(`/posts/${id}/publish`));
    expect(publish.request.method).toBe('POST');
    publish.flush({ data: { public_id: id } });
    service.restore(id).subscribe();
    const restore = http.expectOne((candidate) => candidate.url.endsWith(`/posts/${id}/restore`));
    expect(restore.request.method).toBe('POST');
    restore.flush({ data: { public_id: id } });
  });
});
