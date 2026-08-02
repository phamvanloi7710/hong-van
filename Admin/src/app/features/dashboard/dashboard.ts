import { ChangeDetectionStrategy, Component } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { TranslationPipe } from '../../core/i18n/translation.pipe';

@Component({
  selector: 'hv-dashboard',
  imports: [MatCardModule, MatIconModule, MatProgressBarModule, TranslationPipe],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class Dashboard {}
