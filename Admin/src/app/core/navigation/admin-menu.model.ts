import { TranslationKey } from '../i18n/translation-catalog';

export interface AdminMenuItem {
  readonly id: string;
  readonly labelKey: TranslationKey;
  readonly icon: string;
  readonly iconColor: string;
  readonly route?: string;
  readonly permission?: string;
  readonly children?: readonly AdminMenuItem[];
}
