import { ChangeDetectionStrategy, Component, DestroyRef, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { NonNullableFormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { finalize } from 'rxjs';

import { authErrorMessage } from '../../../core/auth/auth-error';
import { AuthService } from '../../../core/auth/auth.service';
import { I18nService } from '../../../core/i18n/i18n.service';
import { TranslationPipe } from '../../../core/i18n/translation.pipe';

@Component({
  selector: 'hv-reset-password',
  imports: [
    MatButtonModule,
    MatCardModule,
    MatFormFieldModule,
    MatIconModule,
    MatInputModule,
    ReactiveFormsModule,
    RouterLink,
    TranslationPipe,
  ],
  templateUrl: './reset-password.html',
  styleUrl: './reset-password.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ResetPassword {
  private readonly authService = inject(AuthService);
  private readonly destroyRef = inject(DestroyRef);
  private readonly formBuilder = inject(NonNullableFormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly i18n = inject(I18nService);

  readonly token = this.route.snapshot.queryParamMap.get('token') ?? '';
  readonly submitting = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);
  readonly form = this.formBuilder.group({
    email: [this.route.snapshot.queryParamMap.get('email') ?? '', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(8)]],
    password_confirmation: ['', [Validators.required]],
  });

  submit(): void {
    this.errorMessage.set(null);
    this.successMessage.set(null);

    if (this.form.invalid || this.token === '') {
      this.form.markAllAsTouched();
      this.errorMessage.set(
        this.token === '' ? this.i18n.t('auth.resetLinkInvalid') : null,
      );
      return;
    }

    const value = this.form.getRawValue();

    if (value.password !== value.password_confirmation) {
      this.errorMessage.set(this.i18n.t('auth.confirmationMismatch'));
      return;
    }

    this.submitting.set(true);
    this.authService
      .resetPassword({ ...value, token: this.token })
      .pipe(
        finalize(() => this.submitting.set(false)),
        takeUntilDestroyed(this.destroyRef),
      )
      .subscribe({
        next: (message) => this.successMessage.set(message),
        error: (error: unknown) =>
          this.errorMessage.set(
            authErrorMessage(error, this.i18n.t('auth.resetError')),
          ),
      });
  }
}
