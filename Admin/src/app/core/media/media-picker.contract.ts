import { Observable } from 'rxjs';

export interface MediaPickerItem {
  readonly public_id: string;
  readonly original_filename: string;
  readonly mime_type: string;
  readonly width: number | null;
  readonly height: number | null;
  readonly alt_text: string | null;
  readonly content_url: string;
}

export interface MediaPickerOptions {
  readonly multiple?: boolean;
  readonly acceptedMimeTypes?: readonly string[];
  readonly selectedIds?: readonly string[];
}

export interface MediaPickerResult {
  readonly items: readonly MediaPickerItem[];
}

export abstract class MediaPickerContract {
  abstract open(options?: MediaPickerOptions): Observable<MediaPickerResult | null>;
}
