import { TranslationKey } from '../i18n/translation-catalog';

export interface AdminMenuItem {
  readonly id: string;
  readonly labelKey: TranslationKey;
  readonly icon: string;
  readonly route?: string;
  readonly disabled?: boolean;
  readonly permission?: string;
  readonly children?: readonly AdminMenuItem[];
}
