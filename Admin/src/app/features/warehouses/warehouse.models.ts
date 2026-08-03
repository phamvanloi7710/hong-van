export type WarehouseLocale = 'vi' | 'en' | 'zh';
export type WarehouseKind = 'warehouses' | 'facilities' | 'services';
export type WarehouseStatus = 'draft' | 'published' | 'scheduled' | 'archived';
export type MapDisplay = 'hidden' | 'approximate' | 'exact';

export interface WarehouseTranslation {
  readonly locale: WarehouseLocale;
  readonly name: string;
  readonly slug?: string;
  readonly summary?: string | null;
  readonly description?: string | null;
  readonly address_display?: string | null;
  readonly area_description?: string | null;
  readonly capacity_description?: string | null;
  readonly security_description?: string | null;
  readonly fire_safety_description?: string | null;
  readonly business_hours_description?: string | null;
  readonly meta_title?: string | null;
  readonly meta_description?: string | null;
}

export interface BusinessHour { readonly day: string; readonly opens: string | null; readonly closes: string | null; readonly closed: boolean; }
export interface WarehouseMedia { readonly public_id: string; readonly role: 'hero' | 'gallery' | 'floorplan'; readonly sort_order: number; }

export interface WarehouseItem {
  readonly public_id: string;
  readonly code: string;
  readonly sort_order: number;
  readonly translations: readonly WarehouseTranslation[];
  readonly icon?: string | null;
  readonly is_active?: boolean;
  readonly warehouses_count?: number;
  readonly area_value?: string | null;
  readonly area_unit?: 'm2' | null;
  readonly latitude?: string | null;
  readonly longitude?: string | null;
  readonly map_display?: MapDisplay;
  readonly business_hours?: readonly BusinessHour[];
  readonly status?: WarehouseStatus;
  readonly is_featured?: boolean;
  readonly published_at?: string | null;
  readonly unpublished_at?: string | null;
  readonly facility_ids?: readonly string[];
  readonly service_ids?: readonly string[];
  readonly media?: readonly WarehouseMedia[];
}

export interface WarehouseData { readonly warehouses: readonly WarehouseItem[]; readonly facilities: readonly WarehouseItem[]; readonly services: readonly WarehouseItem[]; }
export interface WarehouseDialogData { readonly kind: WarehouseKind; readonly item: WarehouseItem | null; readonly facilities: readonly WarehouseItem[]; readonly services: readonly WarehouseItem[]; }
