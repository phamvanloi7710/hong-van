import { Routes } from '@angular/router';

import { authGuard, guestGuard, permissionGuard } from './core/auth/auth.guard';

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
        loadComponent: () =>
          import('./features/audit/audit-page').then((page) => page.AuditPage),
        data: { breadcrumb: 'menu.audit' },
      },
      {
        path: 'media',
        canActivate: [permissionGuard('media.view')],
        loadComponent: () =>
          import('./features/media/media-page').then((page) => page.MediaPage),
        data: { breadcrumb: 'menu.media' },
      },
    ],
  },
  {
    path: '**',
    redirectTo: 'dashboard',
  },
];
