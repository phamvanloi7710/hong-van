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
import { WarehouseDataService } from './warehouse-data.service';
import { WarehouseDialog } from './warehouse-dialog';
import { WAREHOUSE_TRANSLATIONS } from './warehouse.i18n';
import { WarehouseData, WarehouseDialogData, WarehouseItem, WarehouseKind, WarehouseLocale } from './warehouse.models';

@Component({
  selector: 'hv-warehouse-page',
  imports: [MatButtonModule, MatCardModule, MatChipsModule, MatIconModule, MatProgressSpinnerModule, MatTabsModule, MatTooltipModule],
  templateUrl: './warehouse-page.html',
  styleUrl: './warehouse-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class WarehousePage {
  private readonly service = inject(WarehouseDataService);
  private readonly dialog = inject(MatDialog);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);
  readonly authStore = inject(AuthStore);
  readonly kinds: readonly WarehouseKind[] = ['warehouses', 'facilities', 'services'];
  readonly data = signal<WarehouseData>({ warehouses: [], facilities: [], services: [] });
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly error = signal<string | null>(null);

  constructor() { this.reload(); }

  text(key: string): string { return WAREHOUSE_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  items(kind: WarehouseKind): readonly WarehouseItem[] { return this.data()[kind]; }
  icon(kind: WarehouseKind): string { return kind === 'warehouses' ? 'warehouse' : kind === 'facilities' ? 'verified_user' : 'handyman'; }
  localizedName(item: WarehouseItem): string { return this.localized(item.translations); }
  details(item: WarehouseItem, kind: WarehouseKind): string {
    if (kind !== 'warehouses') return `${item.warehouses_count ?? 0} ${this.text('warehouses')}`;
    return [item.area_value ? `${item.area_value} ${item.area_unit ?? ''}` : null, item.map_display ? this.text(item.map_display) : null].filter(Boolean).join(' · ');
  }

  reload(): void {
    this.loading.set(true); this.error.set(null);
    this.service.load().pipe(finalize(() => this.loading.set(false))).subscribe({ next: (data) => this.data.set(data), error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))) });
  }

  open(kind: WarehouseKind, item: WarehouseItem | null = null): void {
    this.dialog.open<WarehouseDialog, WarehouseDialogData, unknown>(WarehouseDialog, { data: { kind, item, facilities: this.data().facilities, services: this.data().services }, width: '1100px', maxWidth: '98vw', maxHeight: '96vh', disableClose: true }).afterClosed().subscribe((payload) => {
      if (payload) this.run(this.service.save(kind, item?.public_id ?? null, payload), this.text(item ? 'updated' : 'created'));
    });
  }

  publish(item: WarehouseItem): void { this.run(this.service.publish(item.public_id), this.text('publishedMessage')); }
  delete(kind: WarehouseKind, item: WarehouseItem): void { if (window.confirm(this.text('confirmDelete'))) this.run(this.service.delete(kind, item.public_id), this.text('deleted')); }

  private run(request: Observable<unknown>, message: string): void {
    this.saving.set(true);
    request.pipe(finalize(() => this.saving.set(false))).subscribe({ next: () => { this.snackBar.open(message, this.text('close'), { duration: 3000 }); this.reload(); }, error: (error: unknown) => this.snackBar.open(authErrorMessage(error, this.text('operationError')), this.text('close'), { duration: 5000 }) });
  }

  private localized(translations: readonly { readonly locale: WarehouseLocale; readonly name: string }[]): string {
    const locale = this.i18n.locale();
    return translations.find((item) => item.locale === locale)?.name ?? translations.find((item) => item.locale === 'vi')?.name ?? translations[0]?.name ?? '—';
  }
}
