import { DatePipe, JsonPipe } from '@angular/common';
import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar } from '@angular/material/snack-bar';
import { finalize, forkJoin, Observable } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { AuthStore } from '../../core/auth/auth.store';
import { I18nService } from '../../core/i18n/i18n.service';
import { LeadDataService } from './lead-data.service';
import { LEAD_TRANSLATIONS } from './lead.i18n';
import { LEAD_FOLLOW_UP_TRANSLATIONS } from './lead-followup.i18n';
import { Lead, LeadAssignee, LeadAssignmentFilter, LeadMetrics, LeadStatus, LeadType } from './lead.models';

@Component({
  selector: 'hv-lead-page',
  imports: [DatePipe, FormsModule, JsonPipe, MatButtonModule, MatCardModule, MatChipsModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule, MatSelectModule],
  templateUrl: './lead-page.html',
  styleUrl: './lead-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LeadPage {
  private readonly service = inject(LeadDataService);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);
  readonly authStore = inject(AuthStore);
  readonly types: readonly LeadType[] = ['contact', 'product_quote', 'transport', 'warehouse'];
  readonly statuses: readonly LeadStatus[] = ['new', 'contacted', 'qualified', 'processing', 'done', 'spam', 'archived'];
  readonly leads = signal<readonly Lead[]>([]);
  readonly metrics = signal<LeadMetrics>({ total: 0, unassigned: 0, new_today: 0, by_status: {}, by_type: {} });
  readonly assignees = signal<readonly LeadAssignee[]>([]);
  readonly detail = signal<Lead | null>(null);
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly error = signal<string | null>(null);
  type: LeadType | '' = '';
  status: LeadStatus | '' = '';
  assignment: LeadAssignmentFilter = '';
  nextStatus: LeadStatus | '' = '';
  assigneeId = '';
  noteBody = '';
  followUpAt = '';

  constructor() { this.reload(); }

  text(key: string): string { return LEAD_FOLLOW_UP_TRANSLATIONS[this.i18n.locale()][key] ?? LEAD_TRANSLATIONS[this.i18n.locale()][key] ?? key; }

  reload(): void {
    this.loading.set(true); this.error.set(null);
    forkJoin({ page: this.service.list({ type: this.type, status: this.status, assignment: this.assignment }), metrics: this.service.metrics(), assignees: this.service.assignees() })
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({ next: ({ page, metrics, assignees }) => { this.leads.set(page.items); this.metrics.set(metrics); this.assignees.set(assignees); const selected = this.detail(); if (selected) this.select(selected); }, error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))) });
  }

  select(lead: Lead): void {
    this.service.show(lead.public_id).subscribe({ next: (detail) => { this.detail.set(detail); this.nextStatus = ''; this.assigneeId = detail.assignee?.public_id ?? ''; this.followUpAt = toDateTimeLocal(detail.next_follow_up_at); }, error: (error: unknown) => this.snackBar.open(authErrorMessage(error, this.text('loadError')), this.text('close'), { duration: 5000 }) });
  }

  changeStatus(): void {
    const lead = this.detail();
    if (!lead || !this.nextStatus) return;
    this.run(this.service.changeStatus(lead.public_id, this.nextStatus));
  }

  assign(): void {
    const lead = this.detail();
    if (!lead) return;
    this.run(this.service.assign(lead.public_id, this.assigneeId || null));
  }

  addNote(): void {
    const lead = this.detail();
    const body = this.noteBody.trim();
    if (!lead || !body) return;
    this.run(this.service.addNote(lead.public_id, body), () => { this.noteBody = ''; });
  }

  scheduleFollowUp(): void {
    const lead = this.detail();
    if (!lead) return;
    this.run(this.service.scheduleFollowUp(lead.public_id, this.followUpAt ? new Date(this.followUpAt).toISOString() : null));
  }

  export(): void {
    this.service.export({ type: this.type, status: this.status }).subscribe({ next: (blob) => { const url = URL.createObjectURL(blob); const anchor = document.createElement('a'); anchor.href = url; anchor.download = `leads-${new Date().toISOString().slice(0, 10)}.csv`; anchor.click(); URL.revokeObjectURL(url); this.snackBar.open(this.text('exported'), this.text('close'), { duration: 3000 }); }, error: (error: unknown) => this.snackBar.open(authErrorMessage(error, this.text('operationError')), this.text('close'), { duration: 5000 }) });
  }

  private run(request: Observable<Lead>, completed?: () => void): void {
    this.saving.set(true);
    request.pipe(finalize(() => this.saving.set(false))).subscribe({ next: (lead) => { completed?.(); this.select(lead); this.snackBar.open(this.text('updated'), this.text('close'), { duration: 3000 }); this.reloadList(); }, error: (error: unknown) => this.snackBar.open(authErrorMessage(error, this.text('operationError')), this.text('close'), { duration: 5000 }) });
  }

  private reloadList(): void {
    this.service.list({ type: this.type, status: this.status, assignment: this.assignment }).subscribe((page) => this.leads.set(page.items));
    this.service.metrics().subscribe((metrics) => this.metrics.set(metrics));
  }
}

function toDateTimeLocal(value: string | null): string {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  const local = new Date(date.getTime() - date.getTimezoneOffset() * 60_000);
  return local.toISOString().slice(0, 16);
}
