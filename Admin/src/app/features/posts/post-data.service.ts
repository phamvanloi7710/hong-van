import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import { PostAuthor, PostFilters, PostItem, PostPageResult, PostPagination, PostTaxonomyItem } from './post.models';

interface PostPageEnvelope extends ApiEnvelope<readonly PostItem[]> {
  readonly meta: { readonly request_id: string; readonly pagination: PostPagination };
}

@Injectable({ providedIn: 'root' })
export class PostDataService {
  private readonly http = inject(HttpClient);
  private readonly url = `${environment.apiBaseUrl}/posts`;

  list(filters: PostFilters = {}, page = 1): Observable<PostPageResult> {
    let params = new HttpParams().set('page', page).set('per_page', 50).set('sort', '-updated_at');
    if (filters.search) params = params.set('search', filters.search);
    if (filters.status) params = params.set('filter[status]', filters.status);
    if (filters.category_id) params = params.set('filter[category_id]', filters.category_id);
    if (filters.trashed) params = params.set('filter[trashed]', filters.trashed);
    return this.http.get<PostPageEnvelope>(this.url, { params }).pipe(map((response) => ({ items: response.data, pagination: response.meta.pagination })));
  }

  categories(): Observable<readonly PostTaxonomyItem[]> { return this.unwrap(this.http.get<ApiEnvelope<readonly PostTaxonomyItem[]>>(`${this.url}/categories`)); }
  tags(): Observable<readonly PostTaxonomyItem[]> { return this.unwrap(this.http.get<ApiEnvelope<readonly PostTaxonomyItem[]>>(`${this.url}/tags`)); }
  authors(): Observable<readonly PostAuthor[]> { return this.unwrap(this.http.get<ApiEnvelope<readonly PostAuthor[]>>(`${this.url}/authors`)); }

  savePost(publicId: string | null, payload: unknown): Observable<PostItem> {
    return this.unwrap(publicId === null
      ? this.http.post<ApiEnvelope<PostItem>>(this.url, payload)
      : this.http.put<ApiEnvelope<PostItem>>(`${this.url}/${publicId}`, payload));
  }

  saveTaxonomy(kind: 'category' | 'tag', publicId: string | null, payload: unknown): Observable<PostTaxonomyItem> {
    const segment = kind === 'category' ? 'categories' : 'tags';
    return this.unwrap(publicId === null
      ? this.http.post<ApiEnvelope<PostTaxonomyItem>>(`${this.url}/${segment}`, payload)
      : this.http.put<ApiEnvelope<PostTaxonomyItem>>(`${this.url}/${segment}/${publicId}`, payload));
  }

  publish(publicId: string): Observable<PostItem> { return this.unwrap(this.http.post<ApiEnvelope<PostItem>>(`${this.url}/${publicId}/publish`, {})); }
  archive(publicId: string): Observable<PostItem> { return this.unwrap(this.http.post<ApiEnvelope<PostItem>>(`${this.url}/${publicId}/archive`, {})); }
  restore(publicId: string): Observable<PostItem> { return this.unwrap(this.http.post<ApiEnvelope<PostItem>>(`${this.url}/${publicId}/restore`, {})); }
  deletePost(publicId: string): Observable<void> { return this.remove(`${this.url}/${publicId}`); }
  deleteTaxonomy(kind: 'category' | 'tag', publicId: string): Observable<void> { return this.remove(`${this.url}/${kind === 'category' ? 'categories' : 'tags'}/${publicId}`); }

  private remove(url: string): Observable<void> { return this.http.delete<ApiEnvelope<null>>(url).pipe(map(() => undefined)); }
  private unwrap<T>(request: Observable<ApiEnvelope<T>>): Observable<T> { return request.pipe(map((response) => response.data)); }
}
