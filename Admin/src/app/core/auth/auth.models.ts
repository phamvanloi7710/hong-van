export interface ApiEnvelope<T> {
  readonly success: boolean;
  readonly data: T;
  readonly meta: {
    readonly request_id: string;
    readonly pagination?: unknown;
  };
  readonly message: string | null;
  readonly errors?: Readonly<Record<string, readonly string[]>>;
}

export interface AdminUser {
  readonly public_id: string;
  readonly name: string;
  readonly email: string;
  readonly email_verified_at: string | null;
  readonly is_active: boolean;
  readonly locked_at: string | null;
  readonly roles: readonly string[];
  readonly permissions: readonly string[];
}

export interface LoginCredentials {
  readonly email: string;
  readonly password: string;
  readonly remember: boolean;
}

export interface ResetPasswordPayload {
  readonly email: string;
  readonly token: string;
  readonly password: string;
  readonly password_confirmation: string;
}

export type AuthStatus = 'unknown' | 'loading' | 'authenticated' | 'anonymous' | 'error';
