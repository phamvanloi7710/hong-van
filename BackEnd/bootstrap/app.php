<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\Seo\PublicRedirectController;
use App\Http\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetApiLocale;
use App\Http\Middleware\SetPublicLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/preview.php',
        ],
        api: [
            __DIR__.'/../routes/api.php',
            __DIR__.'/../routes/admin.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        then: static function (): void {
            Route::get('/health', HealthController::class)->name('health');
        },
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->preventRequestsDuringMaintenance(except: ['/health']);
        $middleware->convertEmptyStringsToNull(except: [
            static fn (Request $request): bool => $request->is('api/admin/v1/page-builder/*')
                && $request->has('document'),
        ]);
        $middleware->redirectGuestsTo(
            static fn (Request $request): ?string => $request->is('api/*') ? null : '/admin/login',
        );
        $middleware->prepend([
            AssignRequestId::class,
            SetApiLocale::class,
        ]);
        $middleware->append(SecurityHeaders::class);
        $middleware->throttleApi();
        $middleware->trustHosts(subdomains: false);
        $middleware->alias([
            'permission' => EnsurePermission::class,
            'public.locale' => SetPublicLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            static fn (Request $request, Throwable $exception): bool => $request->is('api/*')
                || $request->expectsJson(),
        );

        $exceptions->render(
            static function (Throwable $exception, Request $request): mixed {
                if ($exception instanceof NotFoundHttpException && ! $request->is('api/*')) {
                    return app()->call([app(PublicRedirectController::class), 'resolve'], ['request' => $request]);
                }
                if (! $request->is('api/*')) {
                    return null;
                }

                return app(ApiExceptionRenderer::class)->render($exception, $request);
            },
        );
    })->create();
