export interface AdminMenuItem {
  readonly id: string;
  readonly label: string;
  readonly icon: string;
  readonly route?: string;
  readonly disabled?: boolean;
  readonly children?: readonly AdminMenuItem[];
}
