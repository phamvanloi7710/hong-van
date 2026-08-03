import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatDialog } from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatTabsModule } from '@angular/material/tabs';
import { MatTooltipModule } from '@angular/material/tooltip';
import { finalize, Observable } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { AuthStore } from '../../core/auth/auth.store';
import { I18nService } from '../../core/i18n/i18n.service';
import { TransportationDataService } from './transportation-data.service';
import { TransportationDialog } from './transportation-dialog';
import { TRANSPORT_TRANSLATIONS } from './transportation.i18n';
import { TransportDialogData, TransportItem, TransportKind, TransportLocale, TransportationData } from './transportation.models';

@Component({
  selector: 'hv-transportation-page',
  imports: [MatButtonModule, MatCardModule, MatChipsModule, MatIconModule, MatProgressSpinnerModule, MatTabsModule, MatTooltipModule],
  templateUrl: './transportation-page.html',
  styleUrl: './transportation-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TransportationPage {
  private readonly service = inject(TransportationDataService);
  private readonly dialog = inject(MatDialog);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);
  readonly authStore = inject(AuthStore);
  readonly kinds: readonly TransportKind[] = ['types', 'vehicles', 'routes', 'areas'];
  readonly data = signal<TransportationData>({ types: [], vehicles: [], routes: [], areas: [] });
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly error = signal<string | null>(null);

  constructor() { this.reload(); }

  text(key: string): string { return TRANSPORT_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  items(kind: TransportKind): readonly TransportItem[] { return this.data()[kind]; }
  icon(kind: TransportKind): string { return kind === 'types' ? 'category' : kind === 'vehicles' ? 'local_shipping' : kind === 'routes' ? 'route' : 'map'; }
  localizedName(item: TransportItem): string { return this.localized(item.translations); }
  details(item: TransportItem, kind: TransportKind): string {
    if (kind === 'types') return `${item.vehicles_count ?? 0} ${this.text('vehicles')}`;
    if (kind === 'vehicles') return [item.payload_capacity ? `${item.payload_capacity} ${item.payload_unit ?? ''}` : null, item.availability_display ? this.text(item.availability_display) : null].filter(Boolean).join(' · ');
    if (kind === 'routes') return `${item.origin_code ?? '—'} → ${item.destination_code ?? '—'}`;
    return item.code;
  }

  reload(): void {
    this.loading.set(true); this.error.set(null);
    this.service.load().pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (data) => this.data.set(data),
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
    });
  }

  open(kind: TransportKind, item: TransportItem | null = null): void {
    this.dialog.open<TransportationDialog, TransportDialogData, unknown>(TransportationDialog, {
      data: { kind, item, types: this.data().types }, width: '1060px', maxWidth: '98vw', maxHeight: '96vh', disableClose: true,
    }).afterClosed().subscribe((payload) => {
      if (payload) this.run(this.service.save(kind, item?.public_id ?? null, payload), this.text(item ? 'updated' : 'created'));
    });
  }

  publish(kind: TransportKind, item: TransportItem): void {
    if (kind !== 'types') this.run(this.service.publish(kind, item.public_id), this.text('publishedMessage'));
  }

  delete(kind: TransportKind, item: TransportItem): void {
    if (window.confirm(this.text('confirmDelete'))) this.run(this.service.delete(kind, item.public_id), this.text('deleted'));
  }

  private run(request: Observable<unknown>, message: string): void {
    this.saving.set(true);
    request.pipe(finalize(() => this.saving.set(false))).subscribe({
      next: () => { this.snackBar.open(message, this.text('close'), { duration: 3000 }); this.reload(); },
      error: (error: unknown) => this.snackBar.open(authErrorMessage(error, this.text('operationError')), this.text('close'), { duration: 5000 }),
    });
  }

  private localized(translations: readonly { readonly locale: TransportLocale; readonly name: string }[]): string {
    const locale = this.i18n.locale();
    return translations.find((item) => item.locale === locale)?.name ?? translations.find((item) => item.locale === 'vi')?.name ?? translations[0]?.name ?? '—';
  }
}
