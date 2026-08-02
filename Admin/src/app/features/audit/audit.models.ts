export interface AuditLogEntry {
  readonly public_id: string;
  readonly actor_type: 'user' | 'anonymous' | 'system';
  readonly actor_public_id: string | null;
  readonly action: string;
  readonly subject_type: string | null;
  readonly subject_public_id: string | null;
  readonly before: Readonly<Record<string, unknown>> | null;
  readonly after: Readonly<Record<string, unknown>> | null;
  readonly metadata: Readonly<Record<string, unknown>> | null;
  readonly ip_hash: string | null;
  readonly user_agent_hash: string | null;
  readonly request_id: string;
  readonly occurred_at: string;
}

export interface AuditPagination {
  readonly page: number;
  readonly last_page: number;
  readonly per_page: number;
  readonly total: number;
}

export interface AuditLogPage {
  readonly items: readonly AuditLogEntry[];
  readonly pagination: AuditPagination;
}

export interface AuditLogFilters {
  readonly action?: string;
  readonly actor_public_id?: string;
  readonly subject_type?: string;
  readonly request_id?: string;
  readonly date_from?: string;
  readonly date_to?: string;
}
