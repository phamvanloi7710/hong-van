<?php

namespace App\Http\Middleware;

use App\Domain\Analytics\AnalyticsConfiguration;
use App\Domain\Analytics\ApprovedAnalyticsProviders;
use App\Domain\Analytics\ConsentManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function __construct(
        private readonly ConsentManager $consent,
        private readonly AnalyticsConfiguration $analytics,
        private readonly ApprovedAnalyticsProviders $providers,
    ) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
        $request->attributes->set('csp_nonce', $nonce);
        $response = $next($request);
        $isPreview = $request->is('preview/*') || $request->is('api/admin/v1/preview-sessions*');
        $contentSecurityPolicy = $isPreview
            ? config('security.headers.preview_content_security_policy')
            : config('security.headers.content_security_policy');

        if (is_string($contentSecurityPolicy) && $contentSecurityPolicy !== '') {
            $contentSecurityPolicy = $this->withSource($contentSecurityPolicy, 'script-src', "'nonce-{$nonce}'");

            if (! $isPreview && ! $request->is('admin*') && ! $request->is('api/admin/*') && $this->consent->hasAnalyticsConsent($request)) {
                $provider = $this->analytics->get()['provider'];
                $identifier = $this->analytics->trackingIdentifier();
                $sources = is_string($identifier) && $this->providers->identifierIsValid($provider, $identifier)
                    ? $this->providers->cspSources($provider)
                    : ['script' => [], 'connect' => [], 'image' => []];
                foreach ($sources['script'] as $source) {
                    $contentSecurityPolicy = $this->withSource($contentSecurityPolicy, 'script-src', $source);
                }
                foreach ($sources['connect'] as $source) {
                    $contentSecurityPolicy = $this->withSource($contentSecurityPolicy, 'connect-src', $source);
                }
                foreach ($sources['image'] as $source) {
                    $contentSecurityPolicy = $this->withSource($contentSecurityPolicy, 'img-src', $source);
                }
            }

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

    private function withSource(string $policy, string $directive, string $source): string
    {
        if (str_contains($policy, $source)) {
            return $policy;
        }

        return preg_replace_callback(
            '/(?:^|;\s*)'.preg_quote($directive, '/').'\s+([^;]+)/',
            static fn (array $match): string => str_starts_with($match[0], ';')
                ? '; '.$directive.' '.$match[1].' '.$source
                : $directive.' '.$match[1].' '.$source,
            $policy,
            1,
        ) ?? $policy;
    }
}
