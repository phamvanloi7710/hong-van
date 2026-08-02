import { ChangeDetectionStrategy, Component, input } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';
import { RouterLink } from '@angular/router';
import { TranslationPipe } from '../../i18n/translation.pipe';

@Component({
  selector: 'hv-admin-breadcrumb',
  imports: [MatIconModule, RouterLink, TranslationPipe],
  templateUrl: './admin-breadcrumb.html',
  styleUrl: './admin-breadcrumb.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminBreadcrumb {
  readonly title = input.required<string>();
}
