<?php

namespace App\Http\Middleware;

use App\Domain\Localization\LocaleRegistry;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final readonly class SetPublicLocale
{
    public function __construct(private LocaleRegistry $locales) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $requested = $request->route('locale');
        $locale = is_string($requested) ? $this->locales->normalize($requested) : $this->locales->defaultLocale();

        if (is_string($requested) && ($locale === null || ! $this->locales->isActive($locale))) {
            return redirect()->route('public.home');
        }

        if (is_string($requested) && $locale === $this->locales->defaultLocale()) {
            return redirect()->route('public.home');
        }

        $locale ??= $this->locales->defaultLocale();
        App::setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }
}
