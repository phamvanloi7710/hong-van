import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';

import { AuthStore } from './auth.store';
import { I18nService } from '../i18n/i18n.service';

function isSameOriginRelativeUrl(url: string): boolean {
  return url.startsWith('/') && !url.startsWith('//');
}

export const sessionCredentialsInterceptor: HttpInterceptorFn = (request, next) => {
  const locale = inject(I18nService).locale();
  const statefulRequest = isSameOriginRelativeUrl(request.url)
    ? request.clone({ withCredentials: true, setHeaders: { 'X-Locale': locale } })
    : request;

  return next(statefulRequest);
};

export const unauthorizedInterceptor: HttpInterceptorFn = (request, next) => {
  const authStore = inject(AuthStore);
  const router = inject(Router);

  return next(request).pipe(
    catchError((error: unknown) => {
      if (
        error instanceof HttpErrorResponse &&
        error.status === 401 &&
        !request.url.endsWith('/auth/login')
      ) {
        authStore.markAnonymous();

        if (!router.url.startsWith('/login')) {
          void router.navigate(['/login'], {
            queryParams: router.url === '/' ? undefined : { returnUrl: router.url },
          });
        }
      }

      return throwError(() => error);
    }),
  );
};
