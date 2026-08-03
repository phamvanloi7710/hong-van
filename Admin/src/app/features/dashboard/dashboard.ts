import { ChangeDetectionStrategy, Component, computed, DestroyRef, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { RouterLink } from '@angular/router';
import { finalize, switchMap, takeWhile, timer } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { AuthStore } from '../../core/auth/auth.store';
import { DateTimeService } from '../../core/i18n/date-time.service';
import { I18nService } from '../../core/i18n/i18n.service';
import { DashboardDataService } from './dashboard-data.service';
import { DASHBOARD_TRANSLATIONS } from './dashboard.i18n';
import { DashboardRange, DashboardReport, DashboardSeriesPoint, DashboardSnapshot } from './dashboard.models';

@Component({
  selector: 'hv-dashboard',
  imports: [ReactiveFormsModule, RouterLink, MatButtonModule, MatCardModule, MatIconModule, MatProgressSpinnerModule],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class Dashboard {
  private readonly data = inject(DashboardDataService);
  private readonly destroyRef = inject(DestroyRef);
  private readonly dates = inject(DateTimeService);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);
  readonly authStore = inject(AuthStore);

  readonly filters = new FormGroup({
    from: new FormControl(dateInput(new Date(Date.now() - 29 * 86_400_000)), { nonNullable: true }),
    to: new FormControl(dateInput(new Date()), { nonNullable: true }),
  });
  readonly snapshot = signal<DashboardSnapshot | null>(null);
  readonly loading = signal(true);
  readonly exporting = signal(false);
  readonly error = signal<string | null>(null);
  readonly report = signal<DashboardReport | null>(null);
  readonly leadChartMax = computed(() => maxValue(this.snapshot()?.charts.leads ?? []));
  readonly productChartMax = computed(() => maxValue(this.snapshot()?.charts.published_products ?? []));

  constructor() {
    this.load();
  }

  text(key: string, parameters: Readonly<Record<string, string | number>> = {}): string {
    const value = DASHBOARD_TRANSLATIONS[this.i18n.locale()][key] ?? key;
    return Object.entries(parameters).reduce((result, [name, replacement]) => result.replaceAll(`{${name}}`, String(replacement)), value);
  }

  load(): void {
    this.loading.set(true);
    this.error.set(null);
    this.data.snapshot(this.range()).pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (snapshot) => this.snapshot.set(snapshot),
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
    });
  }

  exportLeads(): void {
    if (this.exporting()) return;
    this.exporting.set(true);
    this.data.createLeadReport(this.range()).pipe(finalize(() => this.exporting.set(false))).subscribe({
      next: (report) => {
        this.report.set(report);
        if (report.status === 'ready') {
          this.download(report);
          return;
        }
        this.snackBar.open(this.text('reportQueued'), this.text('close'), { duration: 5000 });
        this.pollReport(report.public_id);
      },
      error: (error: unknown) => this.snackBar.open(authErrorMessage(error, this.text('reportFailed')), this.text('close'), { duration: 5000 }),
    });
  }

  chartHeight(point: DashboardSeriesPoint, maximum: number): number {
    return maximum === 0 ? 0 : Math.max(8, Math.round((point.value / maximum) * 100));
  }

  entries(values: Readonly<Record<string, number>> | undefined): readonly [string, number][] {
    return Object.entries(values ?? {}).sort((left, right) => right[1] - left[1]);
  }

  formattedTime(value: string): string {
    return this.dates.format(value);
  }

  private range(): DashboardRange {
    const values = this.filters.getRawValue();
    return { from: values.from, to: values.to, timezone: 'Asia/Ho_Chi_Minh' };
  }

  private pollReport(publicId: string): void {
    timer(2500, 2500).pipe(
      switchMap(() => this.data.report(publicId)),
      takeWhile((report) => report.status === 'queued' || report.status === 'processing', true),
      takeUntilDestroyed(this.destroyRef),
    ).subscribe({
      next: (report) => {
        this.report.set(report);
        if (report.status === 'ready') this.download(report);
        if (report.status === 'failed') this.snackBar.open(this.text('reportFailed'), this.text('close'), { duration: 5000 });
      },
      error: () => this.snackBar.open(this.text('reportFailed'), this.text('close'), { duration: 5000 }),
    });
  }

  private download(report: DashboardReport): void {
    this.data.downloadReport(report.public_id).subscribe({
      next: (blob) => {
        const url = URL.createObjectURL(blob);
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = `hong-van-leads-${new Date().toISOString().slice(0, 10)}.csv`;
        anchor.click();
        URL.revokeObjectURL(url);
        this.snackBar.open(this.text('reportReady'), this.text('close'), { duration: 4000 });
      },
      error: () => this.snackBar.open(this.text('reportFailed'), this.text('close'), { duration: 5000 }),
    });
  }
}

function dateInput(date: Date): string {
  const local = new Date(date.getTime() - date.getTimezoneOffset() * 60_000);
  return local.toISOString().slice(0, 10);
}

function maxValue(points: readonly DashboardSeriesPoint[]): number {
  return Math.max(0, ...points.map((point) => point.value));
}
