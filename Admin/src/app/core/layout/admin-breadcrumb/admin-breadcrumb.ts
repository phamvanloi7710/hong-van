import { ChangeDetectionStrategy, Component, input } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'hv-admin-breadcrumb',
  imports: [MatIconModule, RouterLink],
  templateUrl: './admin-breadcrumb.html',
  styleUrl: './admin-breadcrumb.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminBreadcrumb {
  readonly title = input.required<string>();
}
