import { AdminMenuItem } from './admin-menu.model';

export const ADMIN_MENU_ITEMS: readonly AdminMenuItem[] = [
  {
    id: 'dashboard',
    labelKey: 'menu.dashboard',
    icon: 'dashboard',
    route: '/dashboard',
  },
  {
    id: 'catalog',
    labelKey: 'menu.catalog',
    icon: 'inventory_2',
    children: [
      { id: 'products', labelKey: 'menu.products', icon: 'eco', disabled: true },
      {
        id: 'crop-solutions',
        labelKey: 'menu.cropSolutions',
        icon: 'spa',
        disabled: true,
      },
      { id: 'services', labelKey: 'menu.services', icon: 'handyman', disabled: true },
    ],
  },
  {
    id: 'operations',
    labelKey: 'menu.operations',
    icon: 'local_shipping',
    children: [
      { id: 'transportation', labelKey: 'menu.transportation', icon: 'route', disabled: true },
      { id: 'warehouses', labelKey: 'menu.warehouses', icon: 'warehouse', disabled: true },
      { id: 'leads', labelKey: 'menu.leads', icon: 'contact_mail', disabled: true },
    ],
  },
  {
    id: 'content',
    labelKey: 'menu.content',
    icon: 'article',
    children: [
      { id: 'content-pages', labelKey: 'menu.contentPages', icon: 'description', disabled: true },
      { id: 'media', labelKey: 'menu.media', icon: 'perm_media', disabled: true },
      { id: 'page-builder', labelKey: 'menu.pageBuilder', icon: 'dashboard_customize', disabled: true },
      { id: 'seo', labelKey: 'menu.seo', icon: 'travel_explore', disabled: true },
    ],
  },
  {
    id: 'system',
    labelKey: 'menu.system',
    icon: 'settings',
    children: [
      {
        id: 'identity',
        labelKey: 'menu.identity',
        icon: 'admin_panel_settings',
        route: '/identity',
        permission: 'users.view',
      },
      {
        id: 'settings',
        labelKey: 'menu.settings',
        icon: 'tune',
        route: '/settings',
        permission: 'settings.view',
      },
      {
        id: 'localization',
        labelKey: 'menu.localization',
        icon: 'translate',
        route: '/localization',
        permission: 'localization.view',
      },
      {
        id: 'audit',
        labelKey: 'menu.audit',
        icon: 'history',
        route: '/audit',
        permission: 'audit.view',
      },
    ],
  },
];

export const NAVIGABLE_ADMIN_MENU_ITEMS: readonly AdminMenuItem[] = ADMIN_MENU_ITEMS.flatMap(
  (item) => (item.route ? [item] : (item.children ?? []).filter((child) => child.route)),
);

export function findAdminMenuItemById(id: string): AdminMenuItem | undefined {
  return NAVIGABLE_ADMIN_MENU_ITEMS.find((item) => item.id === id);
}

export function findAdminMenuItemByRoute(url: string): AdminMenuItem | undefined {
  const path = url.split(/[?#]/, 1)[0];

  return NAVIGABLE_ADMIN_MENU_ITEMS.find(
    (item) => item.route === path || (item.route !== '/' && path.startsWith(`${item.route}/`)),
  );
}
