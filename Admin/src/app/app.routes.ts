import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: '',
    loadChildren: () =>
      import('./features/foundation/foundation.routes').then(
        (routesModule) => routesModule.FOUNDATION_ROUTES,
      ),
  },
  {
    path: '**',
    redirectTo: '',
  },
];
