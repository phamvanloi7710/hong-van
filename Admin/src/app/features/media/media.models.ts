import { MediaPickerItem } from '../../core/media/media-picker.contract';

export type MediaStatus = 'processing' | 'ready' | 'failed' | 'trashed';

export interface MediaFolder {
  readonly public_id: string;
  readonly parent_id: string | null;
  readonly name: string;
  readonly slug: string;
  readonly sort_order: number;
}

export interface MediaVariant {
  readonly public_id: string;
  readonly key: string;
  readonly mime_type: string;
  readonly size_bytes: number;
  readonly width: number;
  readonly height: number;
  readonly status: 'ready' | 'failed';
  readonly content_url: string;
}

export interface MediaItem extends MediaPickerItem {
  readonly folder: MediaFolder | null;
  readonly normalized_filename: string;
  readonly extension: string;
  readonly size_bytes: number;
  readonly checksum_sha256: string;
  readonly status: MediaStatus;
  readonly visibility: 'private' | 'public';
  readonly title: string | null;
  readonly caption: string | null;
  readonly variants: readonly MediaVariant[];
  readonly tags: readonly { readonly public_id: string; readonly name: string; readonly slug: string }[];
  readonly usage_count: number;
  readonly can_delete: boolean;
  readonly deleted_at: string | null;
  readonly created_at: string;
  readonly updated_at: string;
}

export interface MediaPagination {
  readonly page: number;
  readonly last_page: number;
  readonly per_page: number;
  readonly total: number;
}

export interface MediaPageResult {
  readonly items: readonly MediaItem[];
  readonly pagination: MediaPagination;
}

export interface MediaFilters {
  readonly search?: string;
  readonly status?: string;
  readonly mime_type?: string;
  readonly folder_id?: string;
  readonly trashed?: 'only' | 'with' | 'without';
}

export interface MediaUploadState {
  readonly id: string;
  readonly name: string;
  readonly status: 'uploading' | 'success' | 'error';
  readonly message?: string;
}
