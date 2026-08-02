import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { map } from 'rxjs';

import { AuthService } from './auth.service';

export const authGuard: CanActivateFn = (_route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (authService.store.authenticated()) {
    return true;
  }

  return authService.bootstrap().pipe(
    map((user) =>
      user === null
        ? router.createUrlTree(['/login'], { queryParams: { returnUrl: state.url } })
        : true,
    ),
  );
};

export const guestGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (authService.store.authenticated()) {
    return router.createUrlTree(['/dashboard']);
  }

  return authService.bootstrap().pipe(
    map((user) => (user === null ? true : router.createUrlTree(['/dashboard']))),
  );
};
