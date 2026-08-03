import { AdminMenuItem } from './admin-menu.model';

export const ADMIN_MENU_ITEMS: readonly AdminMenuItem[] = [
  {
    id: 'dashboard',
    labelKey: 'menu.dashboard',
    icon: 'dashboard',
    iconColor: '#3f51b5',
    route: '/dashboard',
  },
  {
    id: 'catalog',
    labelKey: 'menu.catalog',
    icon: 'inventory_2',
    iconColor: '#7b1fa2',
    children: [
      {
        id: 'products',
        labelKey: 'menu.products',
        icon: 'eco',
        iconColor: '#43a047',
        route: '/products',
      },
      {
        id: 'crop-solutions',
        labelKey: 'menu.cropSolutions',
        icon: 'spa',
        iconColor: '#7cb342',
        route: '/crop-solutions',
        permission: 'crop_solutions.view',
      },
      {
        id: 'services',
        labelKey: 'menu.services',
        icon: 'handyman',
        iconColor: '#fb8c00',
        route: '/services',
        permission: 'services.view',
      },
    ],
  },
  {
    id: 'operations',
    labelKey: 'menu.operations',
    icon: 'local_shipping',
    iconColor: '#00897b',
    children: [
      {
        id: 'transportation',
        labelKey: 'menu.transportation',
        icon: 'route',
        iconColor: '#1e88e5',
        route: '/transportation',
        permission: 'transportation.view',
      },
      {
        id: 'warehouses',
        labelKey: 'menu.warehouses',
        icon: 'warehouse',
        iconColor: '#6d4c41',
        route: '/warehouses',
        permission: 'warehouses.view',
      },
      {
        id: 'leads',
        labelKey: 'menu.leads',
        icon: 'contact_mail',
        iconColor: '#d81b60',
        route: '/leads',
        permission: 'leads.view',
      },
    ],
  },
  {
    id: 'content',
    labelKey: 'menu.content',
    icon: 'article',
    iconColor: '#f4511e',
    children: [
      {
        id: 'content-pages',
        labelKey: 'menu.contentPages',
        icon: 'description',
        iconColor: '#3949ab',
        route: '/content-pages',
        permission: 'posts.view',
      },
      {
        id: 'media',
        labelKey: 'menu.media',
        icon: 'perm_media',
        iconColor: '#00acc1',
        route: '/media',
        permission: 'media.view',
      },
      {
        id: 'showcase',
        labelKey: 'menu.showcase',
        icon: 'collections',
        iconColor: '#8e24aa',
        route: '/showcase',
        permission: 'showcase.view',
      },
      {
        id: 'page-builder',
        labelKey: 'menu.pageBuilder',
        icon: 'dashboard_customize',
        iconColor: '#8e24aa',
        route: '/page-builder',
      },
      {
        id: 'seo',
        labelKey: 'menu.seo',
        icon: 'travel_explore',
        iconColor: '#039be5',
        route: '/seo',
      },
    ],
  },
  {
    id: 'system',
    labelKey: 'menu.system',
    icon: 'settings',
    iconColor: '#546e7a',
    children: [
      {
        id: 'identity',
        labelKey: 'menu.identity',
        icon: 'admin_panel_settings',
        iconColor: '#5c6bc0',
        route: '/identity',
        permission: 'users.view',
      },
      {
        id: 'settings',
        labelKey: 'menu.settings',
        icon: 'tune',
        iconColor: '#fb8c00',
        route: '/settings',
        permission: 'settings.view',
      },
      {
        id: 'localization',
        labelKey: 'menu.localization',
        icon: 'translate',
        iconColor: '#00897b',
        route: '/localization',
        permission: 'localization.view',
      },
      {
        id: 'audit',
        labelKey: 'menu.audit',
        icon: 'history',
        iconColor: '#7e57c2',
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
