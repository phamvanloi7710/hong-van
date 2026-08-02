import { Routes } from '@angular/router';

export const FOUNDATION_ROUTES: Routes = [
  {
    path: '',
    loadComponent: () =>
      import('./foundation-page/foundation-page').then((pageModule) => pageModule.FoundationPage),
  },
];
