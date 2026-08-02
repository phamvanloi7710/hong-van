export type AdminMenuOrientation = 'vertical' | 'horizontal';
export type AdminMenuDensity = 'default' | 'compact' | 'mini';
export type AdminSkin =
  | 'indigo-light'
  | 'teal-light'
  | 'red-light'
  | 'gray-light'
  | 'blue-dark'
  | 'green-dark'
  | 'pink-dark'
  | 'gray-dark';

export interface AdminThemePreferences {
  readonly fixedHeader: boolean;
  readonly fixedSidenav: boolean;
  readonly fixedFooter: boolean;
  readonly sidenavOpened: boolean;
  readonly sidenavPinned: boolean;
  readonly menuOrientation: AdminMenuOrientation;
  readonly menuDensity: AdminMenuDensity;
  readonly skin: AdminSkin;
  readonly rtl: boolean;
}

export interface AdminSkinOption {
  readonly id: AdminSkin;
  readonly labelKey:
    | 'theme.skin.indigoLight'
    | 'theme.skin.tealLight'
    | 'theme.skin.redLight'
    | 'theme.skin.grayLight'
    | 'theme.skin.blueDark'
    | 'theme.skin.greenDark'
    | 'theme.skin.pinkDark'
    | 'theme.skin.grayDark';
  readonly primary: string;
  readonly surface: string;
  readonly dark: boolean;
}

export const DEFAULT_ADMIN_THEME_PREFERENCES: AdminThemePreferences = {
  fixedHeader: true,
  fixedSidenav: true,
  fixedFooter: false,
  sidenavOpened: true,
  sidenavPinned: true,
  menuOrientation: 'vertical',
  menuDensity: 'default',
  skin: 'indigo-light',
  rtl: false,
};

export const ADMIN_SKINS: readonly AdminSkinOption[] = [
  { id: 'indigo-light', labelKey: 'theme.skin.indigoLight', primary: '#3f51b5', surface: '#ececec', dark: false },
  { id: 'teal-light', labelKey: 'theme.skin.tealLight', primary: '#009688', surface: '#ececec', dark: false },
  { id: 'red-light', labelKey: 'theme.skin.redLight', primary: '#f44336', surface: '#ececec', dark: false },
  { id: 'gray-light', labelKey: 'theme.skin.grayLight', primary: '#757575', surface: '#ececec', dark: false },
  { id: 'blue-dark', labelKey: 'theme.skin.blueDark', primary: '#0277bd', surface: '#262626', dark: true },
  { id: 'green-dark', labelKey: 'theme.skin.greenDark', primary: '#388e3c', surface: '#262626', dark: true },
  { id: 'pink-dark', labelKey: 'theme.skin.pinkDark', primary: '#d81b60', surface: '#262626', dark: true },
  { id: 'gray-dark', labelKey: 'theme.skin.grayDark', primary: '#607d8b', surface: '#262626', dark: true },
];

export const ADMIN_MENU_ORIENTATIONS: readonly AdminMenuOrientation[] = ['vertical', 'horizontal'];
export const ADMIN_MENU_DENSITIES: readonly AdminMenuDensity[] = ['default', 'compact', 'mini'];
