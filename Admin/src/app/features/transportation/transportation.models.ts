export type TransportLocale = 'vi' | 'en' | 'zh';
export type TransportKind = 'types' | 'vehicles' | 'routes' | 'areas';
export type TransportStatus = 'draft' | 'published' | 'scheduled' | 'archived';
export type AvailabilityDisplay = 'available' | 'limited' | 'unavailable' | 'contact';

export interface TransportTranslation {
  readonly locale: TransportLocale;
  readonly name: string;
  readonly slug?: string;
  readonly summary?: string | null;
  readonly description?: string | null;
  readonly body_dimensions?: string | null;
  readonly meta_title?: string | null;
  readonly meta_description?: string | null;
}

export interface TransportMedia {
  readonly public_id: string;
  readonly role: 'hero' | 'gallery';
  readonly sort_order: number;
}

export interface TransportItem {
  readonly public_id: string;
  readonly code: string;
  readonly sort_order: number;
  readonly translations: readonly TransportTranslation[];
  readonly is_active?: boolean;
  readonly vehicles_count?: number;
  readonly vehicle_type_id?: string;
  readonly payload_capacity?: string | null;
  readonly payload_unit?: 'kg' | 'ton' | null;
  readonly availability_display?: AvailabilityDisplay;
  readonly status?: TransportStatus;
  readonly is_featured?: boolean;
  readonly origin_code?: string;
  readonly destination_code?: string;
  readonly published_at?: string | null;
  readonly unpublished_at?: string | null;
  readonly media?: readonly TransportMedia[];
}

export interface TransportationData {
  readonly types: readonly TransportItem[];
  readonly vehicles: readonly TransportItem[];
  readonly routes: readonly TransportItem[];
  readonly areas: readonly TransportItem[];
}

export interface TransportDialogData {
  readonly kind: TransportKind;
  readonly item: TransportItem | null;
  readonly types: readonly TransportItem[];
}
