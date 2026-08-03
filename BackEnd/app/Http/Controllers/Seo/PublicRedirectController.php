<?php

namespace App\Http\Controllers\Seo;

use App\Domain\Localization\LocaleRegistry;
use App\Http\Controllers\Controller;
use App\Models\RedirectRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class PublicRedirectController extends Controller
{
    public function __invoke(Request $request, LocaleRegistry $locales): RedirectResponse|Response
    {
        return $this->resolve($request, $locales) ?? abort(404);
    }

    public function resolve(Request $request, LocaleRegistry $locales): RedirectResponse|Response|null
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return null;
        }
        $path = '/'.ltrim($request->path(), '/');
        $locale = $locales->normalize($request->segment(1)) ?? $locales->defaultLocale();
        $rule = RedirectRule::query()->where('source_path', $path)->where('is_active', true)
            ->whereIn('locale', [$locale, '*'])->orderByRaw('locale = ? desc', [$locale])->first();
        if (! $rule instanceof RedirectRule) {
            return null;
        }
        $rule->forceFill(['hit_count' => $rule->hit_count + 1, 'last_hit_at' => now('UTC')])->saveQuietly();

        if ($rule->status_code === 410) {
            return response('', 410, ['X-Robots-Tag' => 'noindex, nofollow']);
        }

        return redirect()->to(url($rule->target_path), $rule->status_code);
    }
}
