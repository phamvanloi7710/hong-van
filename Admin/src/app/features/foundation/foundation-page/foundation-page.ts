import { ChangeDetectionStrategy, Component } from '@angular/core';

@Component({
  selector: 'hv-foundation-page',
  templateUrl: './foundation-page.html',
  styleUrl: './foundation-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FoundationPage {}
