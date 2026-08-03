export interface DashboardRange {
  readonly from: string;
  readonly to: string;
  readonly timezone: string;
}

export interface DashboardSeriesPoint {
  readonly date: string;
  readonly value: number;
}

export interface DashboardActivity {
  readonly public_id: string;
  readonly action: string;
  readonly subject_type: string | null;
  readonly subject_public_id: string | null;
  readonly actor_public_id: string | null;
  readonly occurred_at: string;
}

export interface DashboardSnapshot {
  readonly range: DashboardRange;
  readonly capabilities: {
    readonly products: boolean;
    readonly content: boolean;
    readonly leads: boolean;
    readonly activity: boolean;
    readonly analytics: boolean;
    readonly pages: boolean;
    readonly top_viewed: boolean;
  };
  readonly cards: {
    readonly products: { readonly total: number; readonly published: number } | null;
    readonly content: { readonly drafts: number; readonly scheduled: number; readonly pages: number | null } | null;
    readonly leads: {
      readonly total: number;
      readonly new_in_range: number;
      readonly overdue_follow_up: number;
      readonly by_type: Readonly<Record<string, number>>;
      readonly by_status: Readonly<Record<string, number>>;
    } | null;
  };
  readonly charts: {
    readonly leads: readonly DashboardSeriesPoint[];
    readonly published_products: readonly DashboardSeriesPoint[];
  };
  readonly recent_activity: readonly DashboardActivity[];
  readonly analytics: {
    readonly enabled: boolean;
    readonly top_search_terms: readonly { readonly term: string; readonly searches: number; readonly results: number }[];
    readonly top_viewed: readonly unknown[];
  };
  readonly generated_at: string;
  readonly cache_ttl_seconds: number;
}

export type DashboardReportStatus = 'queued' | 'processing' | 'ready' | 'failed';

export interface DashboardReport {
  readonly public_id: string;
  readonly type: 'leads';
  readonly status: DashboardReportStatus;
  readonly row_count: number;
  readonly expires_at: string;
  readonly created_at: string;
  readonly download_url: string | null;
}

export interface DashboardNotification {
  readonly id: string;
  readonly kind: string;
  readonly data: {
    readonly lead_public_id: string | null;
    readonly type: string | null;
    readonly status: string | null;
  };
  readonly deep_link: string | null;
  readonly read_at: string | null;
  readonly created_at: string;
}

export interface DashboardNotificationPage {
  readonly items: readonly DashboardNotification[];
  readonly unread_count: number;
  readonly meta: { readonly current_page: number; readonly last_page: number; readonly total: number };
}
