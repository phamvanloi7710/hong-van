import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { NonNullableFormBuilder, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatCheckboxModule } from '@angular/material/checkbox';
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
import { DateTimeService } from '../../core/i18n/date-time.service';
import { I18nService } from '../../core/i18n/i18n.service';
import { CatalogEntityDialog } from './catalog-entity-dialog';
import { ProductDataService } from './product-data.service';
import { ProductEditorDialog } from './product-editor-dialog';
import { PRODUCT_TRANSLATIONS } from './product.i18n';
import {
  CatalogEntityDialogData,
  CatalogEntityDialogResult,
  CatalogEntityType,
  ProductAttribute,
  ProductBrand,
  ProductCategory,
  ProductEditorData,
  ProductFilters,
  ProductItem,
  ProductPagination,
  ProductPayload,
  ProductTag,
} from './product.models';

type CatalogEntity = ProductCategory | ProductBrand | ProductTag | ProductAttribute;

@Component({
  selector: 'hv-product-page',
  imports: [ReactiveFormsModule, MatButtonModule, MatCardModule, MatCheckboxModule, MatChipsModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule, MatSelectModule, MatTabsModule, MatTooltipModule],
  templateUrl: './product-page.html',
  styleUrl: './product-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ProductPage {
  private readonly data = inject(ProductDataService);
  private readonly dialog = inject(MatDialog);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);
  private readonly dateTimes = inject(DateTimeService);
  readonly authStore = inject(AuthStore);

  readonly products = signal<readonly ProductItem[]>([]);
  readonly categories = signal<readonly ProductCategory[]>([]);
  readonly brands = signal<readonly ProductBrand[]>([]);
  readonly tags = signal<readonly ProductTag[]>([]);
  readonly attributes = signal<readonly ProductAttribute[]>([]);
  readonly pagination = signal<ProductPagination>({ page: 1, last_page: 1, per_page: 20, total: 0 });
  readonly selected = signal<ReadonlySet<string>>(new Set());
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly error = signal<string | null>(null);

  readonly filters = this.fb.group({ search: '', status: '', category_id: '', brand_id: '', price_mode: '' });

  constructor() {
    this.reloadAll();
  }

  text(key: string): string {
    return PRODUCT_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  localizedName(item: ProductItem | ProductCategory | ProductBrand): string {
    const locale = this.i18n.locale();
    return item.translations.find((translation) => translation.locale === locale)?.name
      ?? item.translations.find((translation) => translation.locale === 'vi')?.name
      ?? ('sku' in item ? item.sku : item.code);
  }

  formattedTime(value: string): string {
    return this.dateTimes.format(value);
  }

  reloadAll(): void {
    this.loadReferences();
    this.loadProducts(1);
  }

  applyFilters(): void {
    this.loadProducts(1);
  }

  clearFilters(): void {
    this.filters.reset({ search: '', status: '', category_id: '', brand_id: '', price_mode: '' });
    this.loadProducts(1);
  }

  loadProducts(page = this.pagination().page): void {
    this.loading.set(true);
    this.error.set(null);
    this.selected.set(new Set());
    const filters = this.filters.getRawValue() as ProductFilters;
    this.data.list(filters, page).pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (result) => { this.products.set(result.items); this.pagination.set(result.pagination); },
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
    });
  }

  toggleSelection(publicId: string, checked: boolean): void {
    this.selected.update((current) => {
      const next = new Set(current);
      if (checked) {
        next.add(publicId);
      } else {
        next.delete(publicId);
      }
      return next;
    });
  }

  toggleAll(checked: boolean): void {
    this.selected.set(checked ? new Set(this.products().map((product) => product.public_id)) : new Set());
  }

  allSelected(): boolean {
    return this.products().length > 0 && this.products().every((product) => this.selected().has(product.public_id));
  }

  openProduct(product: ProductItem | null = null): void {
    if (product === null) {
      this.openProductDialog(null);
      return;
    }
    this.saving.set(true);
    this.data.show(product.public_id).pipe(finalize(() => this.saving.set(false))).subscribe({
      next: (detail) => this.openProductDialog(detail),
      error: (error: unknown) => this.showError(error),
    });
  }

  trashProduct(product: ProductItem): void {
    if (!window.confirm(this.text('confirmDeleteProduct'))) return;
    this.runProductOperation(this.data.trash(product.public_id), this.text('deleted'));
  }

  publishProduct(product: ProductItem): void {
    this.runProductOperation(this.data.publish(product.public_id), this.text('published'));
  }

  archiveProduct(product: ProductItem): void {
    this.runProductOperation(this.data.archive(product.public_id), this.text('archived'));
  }

  bulk(action: 'publish' | 'archive'): void {
    if (this.selected().size === 0) return;
    this.saving.set(true);
    this.data.bulk(action, [...this.selected()]).pipe(finalize(() => this.saving.set(false))).subscribe({
      next: () => { this.notify(this.text('bulkDone')); this.loadProducts(); },
      error: (error: unknown) => this.showError(error),
    });
  }

  openEntity(type: CatalogEntityType, item: CatalogEntity | null = null): void {
    this.dialog.open<CatalogEntityDialog, CatalogEntityDialogData, CatalogEntityDialogResult>(CatalogEntityDialog, {
      data: { type, item, categories: this.categories() }, width: '980px', maxWidth: '96vw', maxHeight: '92vh',
    }).afterClosed().subscribe((result) => {
      if (!result) return;
      this.saving.set(true);
      const request: Observable<unknown> = type === 'category' ? this.data.saveCategory(result.publicId, result.payload)
        : type === 'brand' ? this.data.saveBrand(result.publicId, result.payload)
          : type === 'tag' ? this.data.saveTag(result.publicId, result.payload)
            : this.data.saveAttribute(result.publicId, result.payload);
      request.pipe(finalize(() => this.saving.set(false))).subscribe({
        next: () => { this.notify(this.text(result.publicId ? 'updated' : 'created')); this.loadReferences(); },
        error: (error: unknown) => this.showError(error),
      });
    });
  }

  deleteEntity(type: CatalogEntityType, item: CatalogEntity): void {
    if (!window.confirm(this.text('confirmDeleteEntity'))) return;
    this.saving.set(true);
    const request = type === 'category' ? this.data.deleteCategory(item.public_id)
      : type === 'brand' ? this.data.deleteBrand(item.public_id)
        : type === 'tag' ? this.data.deleteTag(item.public_id)
          : this.data.deleteAttribute(item.public_id);
    request.pipe(finalize(() => this.saving.set(false))).subscribe({
      next: () => { this.notify(this.text('deleted')); this.loadReferences(); },
      error: (error: unknown) => this.showError(error),
    });
  }

  private loadReferences(): void {
    forkJoin({ categories: this.data.categories(), brands: this.data.brands(), tags: this.data.tags(), attributes: this.data.attributes() }).subscribe({
      next: (result) => { this.categories.set(result.categories); this.brands.set(result.brands); this.tags.set(result.tags); this.attributes.set(result.attributes); },
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
    });
  }

  private openProductDialog(product: ProductItem | null): void {
    this.dialog.open<ProductEditorDialog, ProductEditorData, ProductPayload>(ProductEditorDialog, {
      data: { product, categories: this.categories(), brands: this.brands(), tags: this.tags(), attributes: this.attributes(), products: this.products() },
      width: '1180px', maxWidth: '98vw', maxHeight: '96vh', disableClose: true,
    }).afterClosed().subscribe((payload) => {
      if (!payload) return;
      this.saving.set(true);
      const request = product ? this.data.update(product.public_id, payload) : this.data.create(payload);
      request.pipe(finalize(() => this.saving.set(false))).subscribe({
        next: () => { this.notify(this.text(product ? 'updated' : 'created')); this.loadProducts(); },
        error: (error: unknown) => this.showError(error),
      });
    });
  }

  private runProductOperation(request: ReturnType<ProductDataService['publish']>, message: string): void {
    this.saving.set(true);
    request.pipe(finalize(() => this.saving.set(false))).subscribe({
      next: () => { this.notify(message); this.loadProducts(); },
      error: (error: unknown) => this.showError(error),
    });
  }

  private notify(message: string): void {
    this.snackBar.open(message, this.text('close'), { duration: 3000 });
  }

  private showError(error: unknown): void {
    this.snackBar.open(authErrorMessage(error, this.text('operationError')), this.text('close'), { duration: 5000 });
  }
}
