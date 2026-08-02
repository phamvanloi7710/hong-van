import { ChangeDetectionStrategy, Component, input } from '@angular/core';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'hv-admin-brand',
  imports: [RouterLink],
  templateUrl: './admin-brand.html',
  styleUrl: './admin-brand.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminBrand {
  readonly compact = input(false);
  readonly link = input('/dashboard');
}
