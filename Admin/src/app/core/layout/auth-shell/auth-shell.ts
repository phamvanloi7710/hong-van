import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';

import { AdminBrand } from '../../../shared/components/admin-brand/admin-brand';

@Component({
  selector: 'hv-auth-shell',
  imports: [AdminBrand, RouterOutlet],
  templateUrl: './auth-shell.html',
  styleUrl: './auth-shell.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AuthShell {}
