import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { ActivatedRoute, RouterLink } from '@angular/router';

import { TranslationKey } from '../../core/i18n/translation-catalog';
import { TranslationPipe } from '../../core/i18n/translation.pipe';

@Component({
  selector: 'hv-module-placeholder',
  imports: [MatButtonModule, MatCardModule, MatIconModule, RouterLink, TranslationPipe],
  templateUrl: './module-placeholder.html',
  styleUrl: './module-placeholder.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ModulePlaceholder {
  private readonly route = inject(ActivatedRoute);

  readonly icon = this.route.snapshot.data['icon'] as string;
  readonly iconColor = this.route.snapshot.data['iconColor'] as string;
  readonly titleKey = this.route.snapshot.data['breadcrumb'] as TranslationKey;
}
