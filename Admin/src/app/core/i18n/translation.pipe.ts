import { inject, Pipe, PipeTransform } from '@angular/core';

import { I18nService } from './i18n.service';
import { TranslationKey } from './translation-catalog';

@Pipe({
  name: 'hvT',
  pure: false,
})
export class TranslationPipe implements PipeTransform {
  private readonly i18n = inject(I18nService);

  transform(key: TranslationKey): string {
    return this.i18n.t(key);
  }
}
