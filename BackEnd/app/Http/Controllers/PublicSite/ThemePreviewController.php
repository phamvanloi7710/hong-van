<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\PublicSite\PublicSiteViewData;
use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ThemePreviewController extends Controller
{
    public function __construct(private readonly PublicSiteViewData $viewData) {}

    public function __invoke(Theme $theme, ThemeVersion $version): View
    {
        if ($version->theme_id !== $theme->getKey()) {
            throw new NotFoundHttpException;
        }

        return view('pages.home', [
            ...$this->viewData->forPage('home'),
            'publicThemeCss' => $version->compiled_css,
            'publicThemeVersion' => $version->public_id,
            'isThemePreview' => true,
        ]);
    }
}
