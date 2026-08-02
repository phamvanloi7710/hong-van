export type SettingValue = string | number | boolean | null;

export interface CompanySetting {
  readonly public_id: string;
  readonly key: string;
  readonly label: string;
  readonly description: string | null;
  readonly value: SettingValue;
  readonly value_type: 'boolean' | 'decimal' | 'secret' | 'string' | 'text' | 'url';
  readonly is_public: boolean;
  readonly is_locked: boolean;
  readonly has_value: boolean;
}

export interface CompanySettingGroup {
  readonly public_id: string;
  readonly key: string;
  readonly label: string;
  readonly description: string | null;
  readonly settings: readonly CompanySetting[];
}

export interface Branch {
  readonly public_id: string;
  readonly name: string;
  readonly code: string | null;
  readonly address: string | null;
  readonly province: string | null;
  readonly district: string | null;
  readonly ward: string | null;
  readonly postal_code: string | null;
  readonly latitude: string | null;
  readonly longitude: string | null;
  readonly phone: string | null;
  readonly email: string | null;
  readonly is_head_office: boolean;
  readonly is_active: boolean;
  readonly sort_order: number;
}

export interface BusinessHour {
  readonly public_id?: string;
  readonly branch_id: string | null;
  readonly day_of_week: number;
  readonly opens_at: string | null;
  readonly closes_at: string | null;
  readonly is_closed: boolean;
  readonly note: string | null;
  readonly is_active: boolean;
  readonly sort_order?: number;
}

export interface SocialLink {
  readonly public_id: string;
  readonly platform: string;
  readonly label: string;
  readonly url: string;
  readonly icon: string | null;
  readonly is_active: boolean;
  readonly sort_order: number;
}

export interface ContactChannel {
  readonly public_id: string;
  readonly type: string;
  readonly label: string;
  readonly value: string;
  readonly href: string | null;
  readonly availability_note: string | null;
  readonly is_primary: boolean;
  readonly is_active: boolean;
  readonly sort_order: number;
}

export interface CompanySettingsPayload {
  readonly groups: readonly CompanySettingGroup[];
  readonly branches: readonly Branch[];
  readonly business_hours: readonly BusinessHour[];
  readonly social_links: readonly SocialLink[];
  readonly contact_channels: readonly ContactChannel[];
}

export type BranchPayload = Omit<Branch, 'public_id'>;
export type SocialLinkPayload = Omit<SocialLink, 'public_id'>;
export type ContactChannelPayload = Omit<ContactChannel, 'public_id'>;
