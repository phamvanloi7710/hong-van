<?php

namespace App\Http\Middleware;

use App\Domain\Localization\LocaleRegistry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final readonly class SetApiLocale
{
    public function __construct(private LocaleRegistry $locales) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/*')) {
            return $next($request);
        }

        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        $request->attributes->set('api_locale', $locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }

    private function resolveLocale(Request $request): string
    {
        $allowedLocales = $this->locales->supportedLocales();
        $defaultLocale = $this->locales->defaultLocale();
        $user = $request->user();
        $userLocale = $user?->getAttribute('locale');

        $candidates = [
            is_string($userLocale) ? $userLocale : null,
            $request->header('X-Locale'),
            ...$this->acceptLanguageCandidates($request),
            $defaultLocale,
        ];

        foreach ($candidates as $candidate) {
            $locale = $this->allowedLocale($candidate, $allowedLocales);

            if ($locale !== null) {
                return $locale;
            }
        }

        return $allowedLocales[0] ?? 'vi';
    }

    /**
     * @return list<string>
     */
    private function acceptLanguageCandidates(Request $request): array
    {
        $header = trim((string) $request->header('Accept-Language', ''));

        if ($header === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $part): string => trim(explode(';', $part, 2)[0]),
            explode(',', $header),
        )));
    }

    /**
     * @param  list<string>  $allowedLocales
     */
    private function allowedLocale(mixed $candidate, array $allowedLocales): ?string
    {
        if (! is_string($candidate) || trim($candidate) === '') {
            return null;
        }

        $normalized = strtolower(str_replace('_', '-', trim($candidate)));
        $baseLocale = explode('-', $normalized, 2)[0];

        foreach ($allowedLocales as $allowedLocale) {
            $normalizedAllowed = strtolower($allowedLocale);

            if ($normalized === $normalizedAllowed || $baseLocale === $normalizedAllowed) {
                return $allowedLocale;
            }
        }

        return null;
    }
}
