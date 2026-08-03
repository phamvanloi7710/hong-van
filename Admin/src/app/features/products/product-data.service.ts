import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import {
  ProductAttribute,
  ProductBrand,
  ProductCategory,
  ProductFilters,
  ProductItem,
  ProductPageResult,
  ProductPagination,
  ProductPayload,
  ProductTag,
} from './product.models';

interface ProductPageEnvelope extends ApiEnvelope<readonly ProductItem[]> {
  readonly meta: { readonly request_id: string; readonly pagination: ProductPagination };
}

@Injectable({ providedIn: 'root' })
export class ProductDataService {
  private readonly http = inject(HttpClient);
  private readonly url = `${environment.apiBaseUrl}/products`;

  list(filters: ProductFilters = {}, page = 1, perPage = 20): Observable<ProductPageResult> {
    let params = new HttpParams().set('page', page).set('per_page', perPage).set('sort', '-updated_at');
    if (filters.search) params = params.set('search', filters.search);
    if (filters.status) params = params.set('filter[status]', filters.status);
    if (filters.category_id) params = params.set('filter[category_id]', filters.category_id);
    if (filters.brand_id) params = params.set('filter[brand_id]', filters.brand_id);
    if (filters.price_mode) params = params.set('filter[price_mode]', filters.price_mode);
    if (filters.featured !== undefined) params = params.set('filter[featured]', filters.featured);

    return this.http.get<ProductPageEnvelope>(this.url, { params }).pipe(
      map((response) => ({ items: response.data, pagination: response.meta.pagination })),
    );
  }

  show(publicId: string): Observable<ProductItem> {
    return this.unwrap(this.http.get<ApiEnvelope<ProductItem>>(`${this.url}/${publicId}`));
  }

  create(payload: ProductPayload): Observable<ProductItem> {
    return this.unwrap(this.http.post<ApiEnvelope<ProductItem>>(this.url, payload));
  }

  update(publicId: string, payload: ProductPayload): Observable<ProductItem> {
    return this.unwrap(this.http.put<ApiEnvelope<ProductItem>>(`${this.url}/${publicId}`, payload));
  }

  trash(publicId: string): Observable<ProductItem> {
    return this.unwrap(this.http.delete<ApiEnvelope<ProductItem>>(`${this.url}/${publicId}`));
  }

  publish(publicId: string): Observable<ProductItem> {
    return this.unwrap(this.http.post<ApiEnvelope<ProductItem>>(`${this.url}/${publicId}/publish`, {}));
  }

  archive(publicId: string): Observable<ProductItem> {
    return this.unwrap(this.http.post<ApiEnvelope<ProductItem>>(`${this.url}/${publicId}/archive`, {}));
  }

  bulk(action: 'publish' | 'archive', productIds: readonly string[]): Observable<number> {
    return this.unwrap(this.http.post<ApiEnvelope<{ readonly updated: number }>>(`${this.url}/bulk`, { action, product_ids: productIds }))
      .pipe(map((result) => result.updated));
  }

  categories(): Observable<readonly ProductCategory[]> {
    return this.unwrap(this.http.get<ApiEnvelope<readonly ProductCategory[]>>(`${this.url}/categories`));
  }

  saveCategory(publicId: string | null, payload: unknown): Observable<ProductCategory> {
    return this.save<ProductCategory>('categories', publicId, payload);
  }

  deleteCategory(publicId: string): Observable<void> {
    return this.remove(`categories/${publicId}`);
  }

  brands(): Observable<readonly ProductBrand[]> {
    return this.unwrap(this.http.get<ApiEnvelope<readonly ProductBrand[]>>(`${this.url}/brands`));
  }

  saveBrand(publicId: string | null, payload: unknown): Observable<ProductBrand> {
    return this.save<ProductBrand>('brands', publicId, payload);
  }

  deleteBrand(publicId: string): Observable<void> {
    return this.remove(`brands/${publicId}`);
  }

  tags(): Observable<readonly ProductTag[]> {
    return this.unwrap(this.http.get<ApiEnvelope<readonly ProductTag[]>>(`${this.url}/tags`));
  }

  saveTag(publicId: string | null, payload: unknown): Observable<ProductTag> {
    return this.save<ProductTag>('tags', publicId, payload);
  }

  deleteTag(publicId: string): Observable<void> {
    return this.remove(`tags/${publicId}`);
  }

  attributes(): Observable<readonly ProductAttribute[]> {
    return this.unwrap(this.http.get<ApiEnvelope<readonly ProductAttribute[]>>(`${this.url}/attributes`));
  }

  saveAttribute(publicId: string | null, payload: unknown): Observable<ProductAttribute> {
    return this.save<ProductAttribute>('attributes', publicId, payload);
  }

  deleteAttribute(publicId: string): Observable<void> {
    return this.remove(`attributes/${publicId}`);
  }

  private save<T>(segment: string, publicId: string | null, payload: unknown): Observable<T> {
    const request = publicId === null
      ? this.http.post<ApiEnvelope<T>>(`${this.url}/${segment}`, payload)
      : this.http.put<ApiEnvelope<T>>(`${this.url}/${segment}/${publicId}`, payload);
    return this.unwrap(request);
  }

  private remove(segment: string): Observable<void> {
    return this.http.delete<ApiEnvelope<null>>(`${this.url}/${segment}`).pipe(map(() => undefined));
  }

  private unwrap<T>(request: Observable<ApiEnvelope<T>>): Observable<T> {
    return request.pipe(map((response) => response.data));
  }
}
