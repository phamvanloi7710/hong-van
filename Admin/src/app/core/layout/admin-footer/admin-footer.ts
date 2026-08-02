import { ChangeDetectionStrategy, Component } from '@angular/core';

@Component({
  selector: 'hv-admin-footer',
  templateUrl: './admin-footer.html',
  styleUrl: './admin-footer.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminFooter {
  readonly year = new Date().getFullYear();
}
