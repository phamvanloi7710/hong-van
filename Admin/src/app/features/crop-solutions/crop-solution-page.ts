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
import { CropReferenceDialog } from './crop-reference-dialog';
import { CropSolutionDataService } from './crop-solution-data.service';
import { CropSolutionDialog } from './crop-solution-dialog';
import { CROP_SOLUTION_TRANSLATIONS } from './crop-solution.i18n';
import {
  CropCategoryItem,
  CropDialogResult,
  CropItem,
  CropLocale,
  CropProductOption,
  CropReferenceDialogData,
  CropSolutionDialogData,
  CropSolutionFilters,
  CropSolutionItem,
  CropStageItem,
} from './crop-solution.models';

type CropReference = CropCategoryItem | CropItem | CropStageItem;
type ReferenceType = CropReferenceDialogData['type'];

@Component({
  selector: 'hv-crop-solution-page',
  imports: [ReactiveFormsModule, MatButtonModule, MatCardModule, MatChipsModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule, MatSelectModule, MatTabsModule, MatTooltipModule],
  templateUrl: './crop-solution-page.html',
  styleUrl: './crop-solution-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CropSolutionPage {
  private readonly data = inject(CropSolutionDataService);
  private readonly dialog = inject(MatDialog);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);
  readonly authStore = inject(AuthStore);

  readonly solutions = signal<readonly CropSolutionItem[]>([]);
  readonly categories = signal<readonly CropCategoryItem[]>([]);
  readonly crops = signal<readonly CropItem[]>([]);
  readonly stages = signal<readonly CropStageItem[]>([]);
  readonly products = signal<readonly CropProductOption[]>([]);
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly error = signal<string | null>(null);
  readonly filters = this.fb.group({ search: '', status: '', crop_id: '', stage_id: '' });

  constructor() { this.reloadAll(); }

  text(key: string): string { return CROP_SOLUTION_TRANSLATIONS[this.i18n.locale()][key] ?? key; }
  localizedName(item: CropCategoryItem | CropItem | CropStageItem): string { return this.localized(item.translations, 'name') || item.code; }
  solutionTitle(item: CropSolutionItem): string { return this.localized(item.translations, 'title') || item.code; }
  cropName(solution: CropSolutionItem): string { return this.localized(solution.crop.translations, 'name'); }
  stageName(solution: CropSolutionItem): string { return solution.stage ? this.localized(solution.stage.translations, 'name') : this.text('noSelection'); }

  reloadAll(): void {
    this.loading.set(true); this.error.set(null);
    const filters = this.filters.getRawValue() as CropSolutionFilters;
    forkJoin({
      page: this.data.list(filters), categories: this.data.categories(), crops: this.data.crops(), stages: this.data.stages(), products: this.data.products(),
    }).pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (result) => {
        this.solutions.set(result.page.items); this.categories.set(result.categories); this.crops.set(result.crops); this.stages.set(result.stages); this.products.set(result.products);
      },
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
    });
  }

  applyFilters(): void { this.reloadAll(); }
  clearFilters(): void { this.filters.reset({ search: '', status: '', crop_id: '', stage_id: '' }); this.reloadAll(); }

  openSolution(solution: CropSolutionItem | null = null): void {
    this.dialog.open<CropSolutionDialog, CropSolutionDialogData, unknown>(CropSolutionDialog, {
      data: { solution, crops: this.crops(), stages: this.stages(), products: this.products() },
      width: '1180px', maxWidth: '98vw', maxHeight: '96vh', disableClose: true,
    }).afterClosed().subscribe((payload) => {
      if (!payload) return;
      this.saving.set(true);
      const request = solution ? this.data.updateSolution(solution.public_id, payload) : this.data.createSolution(payload);
      request.pipe(finalize(() => this.saving.set(false))).subscribe({
        next: () => { this.notify(this.text(solution ? 'updated' : 'created')); this.reloadAll(); },
        error: (error: unknown) => this.showError(error),
      });
    });
  }

  publish(solution: CropSolutionItem): void { this.run(this.data.publishSolution(solution.public_id), this.text('published')); }
  archive(solution: CropSolutionItem): void { this.run(this.data.archiveSolution(solution.public_id), this.text('archived')); }
  deleteSolution(solution: CropSolutionItem): void {
    if (!window.confirm(this.text('confirmDelete'))) return;
    this.run(this.data.deleteSolution(solution.public_id), this.text('deleted'));
  }

  openReference(type: ReferenceType, item: CropReference | null = null): void {
    this.dialog.open<CropReferenceDialog, CropReferenceDialogData, CropDialogResult>(CropReferenceDialog, {
      data: { type, item, categories: this.categories(), crops: this.crops() }, width: '980px', maxWidth: '96vw', maxHeight: '94vh',
    }).afterClosed().subscribe((result) => {
      if (!result) return;
      const request: Observable<unknown> = type === 'category' ? this.data.saveCategory(result.publicId, result.payload)
        : type === 'crop' ? this.data.saveCrop(result.publicId, result.payload)
          : this.data.saveStage(result.publicId, result.payload);
      this.run(request, this.text(result.publicId ? 'updated' : 'created'));
    });
  }

  deleteReference(type: ReferenceType, item: CropReference): void {
    if (!window.confirm(this.text('confirmDelete'))) return;
    const request = type === 'category' ? this.data.deleteCategory(item.public_id)
      : type === 'crop' ? this.data.deleteCrop(item.public_id)
        : this.data.deleteStage(item.public_id);
    this.run(request, this.text('deleted'));
  }

  private run(request: Observable<unknown>, message: string): void {
    this.saving.set(true);
    request.pipe(finalize(() => this.saving.set(false))).subscribe({
      next: () => { this.notify(message); this.reloadAll(); },
      error: (error: unknown) => this.showError(error),
    });
  }

  private localized(items: readonly { readonly locale: CropLocale; readonly name?: string; readonly title?: string }[], field: 'name' | 'title'): string {
    const locale = this.i18n.locale();
    const item = items.find((value) => value['locale'] === locale) ?? items.find((value) => value['locale'] === 'vi') ?? items[0];
    return item?.[field] ?? '';
  }

  private notify(message: string): void { this.snackBar.open(message, this.text('close'), { duration: 3000 }); }
  private showError(error: unknown): void { this.snackBar.open(authErrorMessage(error, this.text('operationError')), this.text('close'), { duration: 5000 }); }
}
