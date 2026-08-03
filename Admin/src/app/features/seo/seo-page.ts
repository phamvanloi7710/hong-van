import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { finalize } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { I18nService } from '../../core/i18n/i18n.service';
import { SeoDataService } from './seo-data.service';
import { SEO_TRANSLATIONS } from './seo.i18n';
import { SeoMetaPanel } from './seo-meta-panel';
import { SeoEntityOption, SeoEntityType, SeoLocale } from './seo.models';

@Component({
  selector: 'hv-seo-page',
  imports: [ReactiveFormsModule, MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule, MatSelectModule, SeoMetaPanel],
  templateUrl: './seo-page.html',
  styleUrl: './seo-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SeoPage {
  private readonly data = inject(SeoDataService);
  private readonly i18n = inject(I18nService);

  readonly types: readonly SeoEntityType[] = ['product', 'crop_solution', 'service', 'post', 'project', 'gallery', 'certification', 'vehicle', 'transport_route', 'transport_service_area', 'warehouse'];
  readonly locales: readonly SeoLocale[] = ['vi', 'en', 'zh'];
  readonly type = signal<SeoEntityType>('product');
  readonly locale = signal<SeoLocale>(this.i18n.locale());
  readonly search = new FormControl('', { nonNullable: true });
  readonly entities = signal<readonly SeoEntityOption[]>([]);
  readonly selected = signal<SeoEntityOption | null>(null);
  readonly loading = signal(true);
  readonly error = signal<string | null>(null);

  constructor() {
    this.loadEntities();
  }

  text(key: string): string {
    return SEO_TRANSLATIONS[this.i18n.locale()][key] ?? key;
  }

  setType(type: SeoEntityType): void {
    this.type.set(type);
    this.loadEntities();
  }

  setLocale(locale: SeoLocale): void {
    this.locale.set(locale);
    this.loadEntities();
  }

  select(entity: SeoEntityOption): void {
    this.selected.set(entity);
  }

  loadEntities(): void {
    this.loading.set(true);
    this.error.set(null);
    this.data.entities(this.type(), this.locale(), this.search.value).pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (entities) => {
        this.entities.set(entities);
        const previousId = this.selected()?.public_id;
        this.selected.set(entities.find((entity) => entity.public_id === previousId) ?? entities[0] ?? null);
      },
      error: (error: unknown) => {
        this.entities.set([]);
        this.selected.set(null);
        this.error.set(authErrorMessage(error, this.text('loadError')));
      },
    });
  }
}
