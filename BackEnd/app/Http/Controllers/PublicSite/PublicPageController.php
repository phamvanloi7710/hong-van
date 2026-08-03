<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\PublicSite\PublicSiteViewData;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

final class PublicPageController extends Controller
{
    public function __construct(private readonly PublicSiteViewData $viewData) {}

    public function home(): View
    {
        return view('pages.home', $this->viewData->forPage('home'));
    }

    public function privacy(): View
    {
        return view('pages.legal', $this->viewData->forPage('privacy'));
    }

    public function terms(): View
    {
        return view('pages.legal', $this->viewData->forPage('terms'));
    }
}
