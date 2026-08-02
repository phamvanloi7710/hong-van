import { inject, Injectable } from '@angular/core';

import { I18nService } from './i18n.service';

export interface ZonedDateTimeParts {
  readonly year: string;
  readonly month: string;
  readonly day: string;
  readonly hour: string;
  readonly minute: string;
}

const BROWSER_LOCALES = { vi: 'vi-VN', en: 'en-GB', zh: 'zh-CN' } as const;

@Injectable({ providedIn: 'root' })
export class DateTimeService {
  private readonly i18n = inject(I18nService);

  format(value: string | Date, timeZone = 'Asia/Ho_Chi_Minh'): string {
    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) return '—';

    return new Intl.DateTimeFormat(BROWSER_LOCALES[this.i18n.locale()], {
      dateStyle: 'medium',
      timeStyle: 'short',
      timeZone,
    }).format(date);
  }

  parts(value: string | Date, timeZone = 'Asia/Ho_Chi_Minh'): ZonedDateTimeParts {
    const date = value instanceof Date ? value : new Date(value);
    const parts = new Intl.DateTimeFormat('en-CA', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      hourCycle: 'h23',
      timeZone,
    }).formatToParts(date);
    const valueOf = (type: Intl.DateTimeFormatPartTypes): string =>
      parts.find((part) => part.type === type)?.value ?? '';

    return {
      year: valueOf('year'),
      month: valueOf('month'),
      day: valueOf('day'),
      hour: valueOf('hour'),
      minute: valueOf('minute'),
    };
  }
}
