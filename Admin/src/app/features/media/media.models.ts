import { MediaPickerItem } from '../../core/media/media-picker.contract';

export type MediaStatus = 'processing' | 'ready' | 'failed' | 'trashed';

export interface MediaFolder {
  readonly public_id: string;
  readonly parent_id: string | null;
  readonly name: string;
  readonly slug: string;
  readonly sort_order: number;
  readonly is_locked: boolean;
  readonly media_count: number;
  readonly children_count: number;
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
  readonly is_locked: boolean;
  readonly title: string | null;
  readonly caption: string | null;
  readonly variants: readonly MediaVariant[];
  readonly tags: readonly { readonly public_id: string; readonly name: string; readonly slug: string }[];
  readonly usage_count: number;
  readonly usages?: readonly MediaUsage[];
  readonly can_delete: boolean;
  readonly deleted_at: string | null;
  readonly created_at: string;
  readonly updated_at: string;
}

export interface MediaUsage {
  readonly public_id: string;
  readonly owner_type: string;
  readonly owner_public_id: string;
  readonly field: string;
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
  readonly visibility?: 'private' | 'public';
  readonly locked?: boolean;
  readonly sort?: 'created_at' | '-created_at' | 'updated_at' | '-updated_at' | 'original_filename' | '-original_filename' | 'size_bytes' | '-size_bytes';
}

export interface MediaUploadState {
  readonly id: string;
  readonly name: string;
  readonly status: 'pending' | 'uploading' | 'success' | 'error';
  readonly progress: number;
  readonly message?: string;
  readonly file: File;
}

export type MediaUploadEvent =
  | { readonly kind: 'progress'; readonly progress: number }
  | { readonly kind: 'complete'; readonly item: MediaItem };
