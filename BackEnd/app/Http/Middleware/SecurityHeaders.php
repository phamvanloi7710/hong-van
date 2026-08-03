<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $isPreview = $request->is('preview/*') || $request->is('api/admin/v1/preview-sessions*');
        $contentSecurityPolicy = $isPreview
            ? config('security.headers.preview_content_security_policy')
            : config('security.headers.content_security_policy');

        if (is_string($contentSecurityPolicy) && $contentSecurityPolicy !== '') {
            $response->headers->set('Content-Security-Policy', $contentSecurityPolicy);
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', (string) config('security.headers.referrer_policy', 'strict-origin-when-cross-origin'));
        $response->headers->set('X-Frame-Options', $isPreview ? 'SAMEORIGIN' : 'DENY');

        if ($isPreview || $request->is('admin*') || $request->is('api/admin/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        if ((string) config('app.env') === 'production' && $request->isSecure()) {
            $maxAge = max(0, (int) config('security.headers.hsts_max_age', 31536000));
            $response->headers->set('Strict-Transport-Security', "max-age={$maxAge}; includeSubDomains");
        }

        return $response;
    }
}
