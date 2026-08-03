export type LeadType = 'contact' | 'product_quote' | 'transport' | 'warehouse';
export type LeadStatus = 'new' | 'contacted' | 'qualified' | 'processing' | 'done' | 'spam' | 'archived';
export type LeadAssignmentFilter = '' | 'mine' | 'unassigned' | 'assigned';

export interface LeadContact { readonly name: string; readonly phone: string | null; readonly email: string | null; }
export interface LeadAssignee { readonly public_id: string; readonly name: string; readonly email?: string; }
export interface LeadTimeline { readonly from_status: LeadStatus | null; readonly to_status: LeadStatus; readonly actor: string | null; readonly created_at: string; }
export interface LeadAssignment { readonly from: string | null; readonly to: string | null; readonly actor: string | null; readonly created_at: string; }
export interface LeadNote { readonly public_id: string; readonly body: string; readonly author: string | null; readonly created_at: string; }
export interface LeadQuoteItem { readonly product_name: string; readonly quantity: string | null; readonly unit: string | null; readonly notes: string | null; }

export interface Lead {
  readonly public_id: string;
  readonly type: LeadType;
  readonly status: LeadStatus;
  readonly source: string;
  readonly contact: LeadContact;
  readonly original_payload?: Readonly<Record<string, unknown>>;
  readonly contact_detail?: Readonly<Record<string, string | null>> | null;
  readonly quote_items?: readonly LeadQuoteItem[];
  readonly linked_request?: { readonly transport_request_id: string | null; readonly warehouse_request_id: string | null } | null;
  readonly assignee: LeadAssignee | null;
  readonly allowed_transitions: readonly LeadStatus[];
  readonly timeline?: readonly LeadTimeline[];
  readonly assignments?: readonly LeadAssignment[];
  readonly notes?: readonly LeadNote[];
  readonly consent_at: string;
  readonly privacy_policy_version: string;
  readonly anonymized_at: string | null;
  readonly next_follow_up_at: string | null;
  readonly created_at: string;
  readonly updated_at: string;
}

export interface LeadPage { readonly items: readonly Lead[]; readonly meta: { readonly current_page: number; readonly last_page: number; readonly total: number }; }
export interface LeadMetrics { readonly total: number; readonly unassigned: number; readonly new_today: number; readonly by_status: Readonly<Record<string, number>>; readonly by_type: Readonly<Record<string, number>>; }
export interface LeadFilters { readonly type: LeadType | ''; readonly status: LeadStatus | ''; readonly assignment: LeadAssignmentFilter; }
