import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { NonNullableFormBuilder, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatDialog } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatTabsModule } from '@angular/material/tabs';
import { MatTooltipModule } from '@angular/material/tooltip';
import { finalize, forkJoin, Observable } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { AuthStore } from '../../core/auth/auth.store';
import { I18nService } from '../../core/i18n/i18n.service';
import { ServiceCategoryDialog } from './service-category-dialog';
import { ServiceDataService } from './service-data.service';
import { ServiceDialog } from './service-dialog';
import { SERVICE_TRANSLATIONS } from './service.i18n';
import {
  ServiceCategoryItem,
  ServiceDialogResult,
  ServiceFilters,
  ServiceItem,
  ServiceLocale,
  ServiceType,
} from './service.models';

@Component({
  selector: 'hv-service-page',
  imports: [ReactiveFormsModule, MatButtonModule, MatCardModule, MatChipsModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule, MatSelectModule, MatTabsModule, MatTooltipModule],
  templateUrl: './service-page.html',
  styleUrl: './service-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ServicePage {
  private readonly data = inject(ServiceDataService);
  private readonly dialog = inject(MatDialog);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);
  readonly authStore = inject(AuthStore);
  readonly services = signal<readonly ServiceItem[]>([]);
  readonly categories = signal<readonly ServiceCategoryItem[]>([]);
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly error = signal<string | null>(null);
  readonly filters = this.fb.group({ search: '', status: '', service_type: '', category_id: '', trashed: 'without' });
  readonly statuses = ['draft', 'published', 'scheduled', 'archived'] as const;
  readonly serviceTypes: readonly ServiceType[] = ['general', 'transportation_link', 'warehouse_link'];

  constructor() { this.reloadAll(); }

  text(key: string): string { return SERVICE_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  localizedName(item: ServiceItem | ServiceCategoryItem): string { return this.localized(item.translations); }
  categoryName(service: ServiceItem): string { return service.category ? this.localized(service.category.translations) : this.text('noSelection'); }
  typeLabel(type: ServiceType): string { return this.text(type === 'general' ? 'generalType' : type === 'transportation_link' ? 'transportationLink' : 'warehouseLink'); }

  reloadAll(): void {
    this.loading.set(true); this.error.set(null);
    const filters = this.filters.getRawValue() as ServiceFilters;
    forkJoin({ page: this.data.list(filters), categories: this.data.categories() })
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({
        next: (result) => { this.services.set(result.page.items); this.categories.set(result.categories); },
        error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
      });
  }

  applyFilters(): void { this.reloadAll(); }
  clearFilters(): void { this.filters.reset({ search: '', status: '', service_type: '', category_id: '', trashed: 'without' }); this.reloadAll(); }

  openService(service: ServiceItem | null = null): void {
    this.dialog.open<ServiceDialog, { service: ServiceItem | null; categories: readonly ServiceCategoryItem[] }, unknown>(ServiceDialog, {
      data: { service, categories: this.categories() }, width: '1180px', maxWidth: '98vw', maxHeight: '96vh', disableClose: true,
    }).afterClosed().subscribe((payload) => {
      if (!payload) return;
      this.run(this.data.saveService(service?.public_id ?? null, payload), this.text(service ? 'updated' : 'created'));
    });
  }

  openCategory(category: ServiceCategoryItem | null = null): void {
    this.dialog.open<ServiceCategoryDialog, { category: ServiceCategoryItem | null; categories: readonly ServiceCategoryItem[] }, ServiceDialogResult>(ServiceCategoryDialog, {
      data: { category, categories: this.categories() }, width: '980px', maxWidth: '96vw', maxHeight: '94vh',
    }).afterClosed().subscribe((result) => {
      if (result) this.run(this.data.saveCategory(result.publicId, result.payload), this.text(result.publicId ? 'updated' : 'created'));
    });
  }

  publish(service: ServiceItem): void { this.run(this.data.publish(service.public_id), this.text('publishedMessage')); }
  archive(service: ServiceItem): void { this.run(this.data.archive(service.public_id), this.text('archivedMessage')); }
  restore(service: ServiceItem): void { this.run(this.data.restore(service.public_id), this.text('restoredMessage')); }
  deleteService(service: ServiceItem): void {
    if (window.confirm(this.text('confirmDelete'))) this.run(this.data.deleteService(service.public_id), this.text('deleted'));
  }
  deleteCategory(category: ServiceCategoryItem): void {
    if (window.confirm(this.text('confirmDelete'))) this.run(this.data.deleteCategory(category.public_id), this.text('deleted'));
  }

  private run(request: Observable<unknown>, message: string): void {
    this.saving.set(true);
    request.pipe(finalize(() => this.saving.set(false))).subscribe({
      next: () => { this.snackBar.open(message, this.text('close'), { duration: 3000 }); this.reloadAll(); },
      error: (error: unknown) => this.snackBar.open(authErrorMessage(error, this.text('operationError')), this.text('close'), { duration: 5000 }),
    });
  }

  private localized(translations: readonly { readonly locale: ServiceLocale; readonly name: string }[]): string {
    const locale = this.i18n.locale();
    return translations.find((item) => item.locale === locale)?.name ?? translations.find((item) => item.locale === 'vi')?.name ?? translations[0]?.name ?? '—';
  }
}
