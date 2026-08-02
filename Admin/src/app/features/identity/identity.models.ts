export interface IdentityPermission {
  readonly public_id: string;
  readonly key: string;
  readonly module: string;
  readonly action: string;
  readonly name: string;
  readonly description: string | null;
  readonly is_system: boolean;
  readonly roles_count?: number;
}

export interface IdentityRole {
  readonly public_id: string;
  readonly name: string;
  readonly slug: string;
  readonly description: string | null;
  readonly is_system: boolean;
  readonly users_count?: number;
  readonly permissions: readonly IdentityPermission[];
}

export interface PermissionOverride {
  readonly permission_id: string;
  readonly key: string;
  readonly is_allowed: boolean;
}

export interface IdentityUser {
  readonly public_id: string;
  readonly name: string;
  readonly email: string;
  readonly email_verified_at: string | null;
  readonly is_active: boolean;
  readonly locked_at: string | null;
  readonly roles: readonly IdentityRole[];
  readonly permission_overrides: readonly PermissionOverride[];
  readonly permissions: readonly string[];
}

export interface UserPayload {
  readonly name: string;
  readonly email: string;
  readonly password?: string;
  readonly password_confirmation?: string;
  readonly is_active?: boolean;
  readonly role_ids?: readonly string[];
  readonly permission_overrides?: readonly {
    readonly permission_id: string;
    readonly is_allowed: boolean;
  }[];
}

export interface RolePayload {
  readonly name: string;
  readonly slug: string;
  readonly description: string | null;
  readonly permission_ids: readonly string[];
}

export interface PermissionPayload {
  readonly module: string;
  readonly action: string;
  readonly name: string;
  readonly description: string | null;
}
