import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiEnvelope } from '../../core/auth/auth.models';
import {
  CropCategoryItem,
  CropItem,
  CropPagination,
  CropProductOption,
  CropSolutionFilters,
  CropSolutionItem,
  CropSolutionPageResult,
  CropStageItem,
} from './crop-solution.models';

interface CropPageEnvelope extends ApiEnvelope<readonly CropSolutionItem[]> {
  readonly meta: { readonly request_id: string; readonly pagination: CropPagination };
}

type ProductPageEnvelope = ApiEnvelope<readonly CropProductOption[]>;

@Injectable({ providedIn: 'root' })
export class CropSolutionDataService {
  private readonly http = inject(HttpClient);
  private readonly url = `${environment.apiBaseUrl}/crop-solutions`;

  list(filters: CropSolutionFilters = {}, page = 1): Observable<CropSolutionPageResult> {
    let params = new HttpParams().set('page', page).set('per_page', 50).set('sort', 'sort_order');
    if (filters.search) params = params.set('search', filters.search);
    if (filters.status) params = params.set('filter[status]', filters.status);
    if (filters.crop_id) params = params.set('filter[crop_id]', filters.crop_id);
    if (filters.stage_id) params = params.set('filter[stage_id]', filters.stage_id);
    return this.http.get<CropPageEnvelope>(this.url, { params }).pipe(map((response) => ({ items: response.data, pagination: response.meta.pagination })));
  }

  createSolution(payload: unknown): Observable<CropSolutionItem> { return this.unwrap(this.http.post<ApiEnvelope<CropSolutionItem>>(this.url, payload)); }
  updateSolution(publicId: string, payload: unknown): Observable<CropSolutionItem> { return this.unwrap(this.http.put<ApiEnvelope<CropSolutionItem>>(`${this.url}/${publicId}`, payload)); }
  publishSolution(publicId: string): Observable<CropSolutionItem> { return this.unwrap(this.http.post<ApiEnvelope<CropSolutionItem>>(`${this.url}/${publicId}/publish`, {})); }
  archiveSolution(publicId: string): Observable<CropSolutionItem> { return this.unwrap(this.http.post<ApiEnvelope<CropSolutionItem>>(`${this.url}/${publicId}/archive`, {})); }
  deleteSolution(publicId: string): Observable<void> { return this.remove(publicId); }

  categories(): Observable<readonly CropCategoryItem[]> { return this.unwrap(this.http.get<ApiEnvelope<readonly CropCategoryItem[]>>(`${this.url}/categories`)); }
  crops(): Observable<readonly CropItem[]> { return this.unwrap(this.http.get<ApiEnvelope<readonly CropItem[]>>(`${this.url}/crops`)); }
  stages(): Observable<readonly CropStageItem[]> { return this.unwrap(this.http.get<ApiEnvelope<readonly CropStageItem[]>>(`${this.url}/stages`)); }

  saveCategory(publicId: string | null, payload: unknown): Observable<CropCategoryItem> { return this.save('categories', publicId, payload); }
  saveCrop(publicId: string | null, payload: unknown): Observable<CropItem> { return this.save('crops', publicId, payload); }
  saveStage(publicId: string | null, payload: unknown): Observable<CropStageItem> { return this.save('stages', publicId, payload); }
  deleteCategory(publicId: string): Observable<void> { return this.remove(`categories/${publicId}`); }
  deleteCrop(publicId: string): Observable<void> { return this.remove(`crops/${publicId}`); }
  deleteStage(publicId: string): Observable<void> { return this.remove(`stages/${publicId}`); }

  products(): Observable<readonly CropProductOption[]> {
    const params = new HttpParams().set('per_page', 100).set('sort', '-updated_at');
    return this.http.get<ProductPageEnvelope>(`${environment.apiBaseUrl}/products`, { params }).pipe(map((response) => response.data));
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
