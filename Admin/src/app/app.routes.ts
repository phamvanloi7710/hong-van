import { Routes } from '@angular/router';

import { authGuard, guestGuard, permissionGuard } from './core/auth/auth.guard';
import { findAdminMenuItemById } from './core/navigation/admin-menu';

const loadModulePlaceholder = () =>
  import('./features/module-placeholder/module-placeholder').then((page) => page.ModulePlaceholder);

function placeholderData(id: string) {
  const menuItem = findAdminMenuItemById(id);

  if (menuItem === undefined) {
    throw new Error(`Admin menu item not found: ${id}`);
  }

  return {
    breadcrumb: menuItem.labelKey,
    icon: menuItem.icon,
    iconColor: menuItem.iconColor,
  };
}

export const routes: Routes = [
  {
    path: 'login',
    canActivate: [guestGuard],
    loadComponent: () =>
      import('./core/layout/auth-shell/auth-shell').then((shell) => shell.AuthShell),
    children: [
      {
        path: '',
        loadComponent: () => import('./features/auth/login/login').then((page) => page.Login),
      },
    ],
  },
  {
    path: 'forgot-password',
    canActivate: [guestGuard],
    loadComponent: () =>
      import('./core/layout/auth-shell/auth-shell').then((shell) => shell.AuthShell),
    children: [
      {
        path: '',
        loadComponent: () =>
          import('./features/auth/forgot-password/forgot-password').then(
            (page) => page.ForgotPassword,
          ),
      },
    ],
  },
  {
    path: 'reset-password',
    canActivate: [guestGuard],
    loadComponent: () =>
      import('./core/layout/auth-shell/auth-shell').then((shell) => shell.AuthShell),
    children: [
      {
        path: '',
        loadComponent: () =>
          import('./features/auth/reset-password/reset-password').then(
            (page) => page.ResetPassword,
          ),
      },
    ],
  },
  {
    path: '',
    canActivate: [authGuard],
    loadComponent: () =>
      import('./core/layout/admin-shell/admin-shell').then((shell) => shell.AdminShell),
    children: [
      {
        path: '',
        pathMatch: 'full',
        redirectTo: 'dashboard',
      },
      {
        path: 'dashboard',
        loadComponent: () =>
          import('./features/dashboard/dashboard').then((page) => page.Dashboard),
        data: { breadcrumb: 'menu.dashboard' },
      },
      {
        path: 'products',
        canActivate: [permissionGuard('products.view')],
        loadComponent: () =>
          import('./features/products/product-page').then((page) => page.ProductPage),
        data: { breadcrumb: 'menu.products' },
      },
      {
        path: 'crop-solutions',
        canActivate: [permissionGuard('crop_solutions.view')],
        loadComponent: () =>
          import('./features/crop-solutions/crop-solution-page').then((page) => page.CropSolutionPage),
        data: { breadcrumb: 'menu.cropSolutions' },
      },
      {
        path: 'services',
        canActivate: [permissionGuard('services.view')],
        loadComponent: () =>
          import('./features/services/service-page').then((page) => page.ServicePage),
        data: { breadcrumb: 'menu.services' },
      },
      {
        path: 'transportation',
        canActivate: [permissionGuard('transportation.view')],
        loadComponent: () =>
          import('./features/transportation/transportation-page').then((page) => page.TransportationPage),
        data: { breadcrumb: 'menu.transportation' },
      },
      {
        path: 'warehouses',
        loadComponent: loadModulePlaceholder,
        data: placeholderData('warehouses'),
      },
      {
        path: 'leads',
        loadComponent: loadModulePlaceholder,
        data: placeholderData('leads'),
      },
      {
        path: 'content-pages',
        loadComponent: loadModulePlaceholder,
        data: placeholderData('content-pages'),
      },
      {
        path: 'page-builder',
        loadComponent: loadModulePlaceholder,
        data: placeholderData('page-builder'),
      },
      {
        path: 'seo',
        loadComponent: loadModulePlaceholder,
        data: placeholderData('seo'),
      },
      {
        path: 'identity',
        canActivate: [permissionGuard('users.view')],
        loadComponent: () =>
          import('./features/identity/identity-page').then((page) => page.IdentityPage),
        data: { breadcrumb: 'menu.identity' },
      },
      {
        path: 'settings',
        canActivate: [permissionGuard('settings.view')],
        loadComponent: () =>
          import('./features/settings/settings-page').then((page) => page.SettingsPage),
        data: { breadcrumb: 'menu.settings' },
      },
      {
        path: 'localization',
        canActivate: [permissionGuard('localization.view')],
        loadComponent: () =>
          import('./features/localization/localization-page').then((page) => page.LocalizationPage),
        data: { breadcrumb: 'menu.localization' },
      },
      {
        path: 'audit',
        canActivate: [permissionGuard('audit.view')],
        loadComponent: () => import('./features/audit/audit-page').then((page) => page.AuditPage),
        data: { breadcrumb: 'menu.audit' },
      },
      {
        path: 'media',
        canActivate: [permissionGuard('media.view')],
        loadComponent: () => import('./features/media/media-page').then((page) => page.MediaPage),
        data: { breadcrumb: 'menu.media' },
      },
    ],
  },
  {
    path: '**',
    redirectTo: 'dashboard',
  },
];
