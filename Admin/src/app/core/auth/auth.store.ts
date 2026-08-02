import { computed, Injectable, signal } from '@angular/core';

import { AdminUser, AuthStatus } from './auth.models';

@Injectable({ providedIn: 'root' })
export class AuthStore {
  private readonly userState = signal<AdminUser | null>(null);
  private readonly statusState = signal<AuthStatus>('unknown');
  private readonly errorState = signal<string | null>(null);

  readonly user = this.userState.asReadonly();
  readonly status = this.statusState.asReadonly();
  readonly error = this.errorState.asReadonly();
  readonly authenticated = computed(
    () => this.statusState() === 'authenticated' && this.userState() !== null,
  );
  readonly initials = computed(() => {
    const parts = this.userState()
      ?.name.trim()
      .split(/\s+/)
      .filter(Boolean);

    if (!parts?.length) {
      return 'HV';
    }

    return parts
      .slice(-2)
      .map((part) => part.charAt(0).toLocaleUpperCase('vi'))
      .join('');
  });

  markLoading(): void {
    this.statusState.set('loading');
    this.errorState.set(null);
  }

  markAuthenticated(user: AdminUser): void {
    this.userState.set(user);
    this.statusState.set('authenticated');
    this.errorState.set(null);
  }

  markAnonymous(): void {
    this.userState.set(null);
    this.statusState.set('anonymous');
    this.errorState.set(null);
  }

  markError(message: string): void {
    this.userState.set(null);
    this.statusState.set('error');
    this.errorState.set(message);
  }
}
