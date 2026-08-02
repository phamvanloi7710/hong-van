import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { finalize } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { DateTimeService } from '../../core/i18n/date-time.service';
import { I18nService } from '../../core/i18n/i18n.service';
import { AuditDataService } from './audit-data.service';
import { AUDIT_TRANSLATIONS } from './audit.i18n';
import { AuditLogEntry, AuditLogFilters, AuditPagination } from './audit.models';

@Component({
  selector: 'hv-audit-page',
  imports: [ReactiveFormsModule, MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule],
  templateUrl: './audit-page.html',
  styleUrl: './audit-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AuditPage {
  private readonly data = inject(AuditDataService);
  private readonly dateTimes = inject(DateTimeService);
  private readonly i18n = inject(I18nService);

  readonly filters = new FormGroup({
    action: new FormControl('', { nonNullable: true }),
    actor_public_id: new FormControl('', { nonNullable: true }),
    subject_type: new FormControl('', { nonNullable: true }),
    request_id: new FormControl('', { nonNullable: true }),
    date_from: new FormControl('', { nonNullable: true }),
    date_to: new FormControl('', { nonNullable: true }),
  });
  readonly entries = signal<readonly AuditLogEntry[]>([]);
  readonly pagination = signal<AuditPagination | null>(null);
  readonly loading = signal(true);
  readonly error = signal<string | null>(null);

  constructor() {
    this.load();
  }

  text(key: string): string {
    return AUDIT_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  load(page = 1): void {
    this.loading.set(true);
    this.error.set(null);
    this.data.list(this.normalizedFilters(), page).pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (result) => {
        this.entries.set(result.items);
        this.pagination.set(result.pagination);
      },
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
    });
  }

  reset(): void {
    this.filters.reset({ action: '', actor_public_id: '', subject_type: '', request_id: '', date_from: '', date_to: '' });
    this.load();
  }

  previous(): void {
    const current = this.pagination()?.page ?? 1;
    if (current > 1) this.load(current - 1);
  }

  next(): void {
    const pagination = this.pagination();
    if (pagination && pagination.page < pagination.last_page) this.load(pagination.page + 1);
  }

  formattedTime(value: string): string {
    return this.dateTimes.format(value);
  }

  actor(entry: AuditLogEntry): string {
    return entry.actor_public_id ?? this.text(entry.actor_type === 'system' ? 'system' : 'anonymous');
  }

  subject(entry: AuditLogEntry): string {
    return [entry.subject_type, entry.subject_public_id].filter(Boolean).join(' · ') || '—';
  }

  json(value: Readonly<Record<string, unknown>> | null): string {
    return value ? JSON.stringify(value, null, 2) : this.text('noDiff');
  }

  private normalizedFilters(): AuditLogFilters {
    return Object.fromEntries(
      Object.entries(this.filters.getRawValue())
        .map(([key, value]) => [key, value.trim()])
        .filter(([, value]) => value !== ''),
    );
  }
}
