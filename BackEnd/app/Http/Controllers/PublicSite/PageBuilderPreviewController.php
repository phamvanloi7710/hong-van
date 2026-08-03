<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\PageBuilder\PageDocumentRenderer;
use App\Domain\PageBuilder\PagePreviewSessionManager;
use App\Domain\PageBuilder\PageRenderOptions;
use App\Domain\PublicSite\PublicSiteViewData;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PagePreviewSession;
use App\Models\PageTranslation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

final class PageBuilderPreviewController extends Controller
{
    public function __construct(
        private readonly PagePreviewSessionManager $sessions,
        private readonly PageDocumentRenderer $renderer,
        private readonly PublicSiteViewData $viewData,
    ) {}

    public function __invoke(Request $request, string $token): View
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 404);
        $payload = $this->sessions->resolve($actor, $token);
        $session = $payload['session'] ?? null;
        abort_unless($session instanceof PagePreviewSession && $session->page instanceof Page, 404);
        $locale = (string) ($payload['locale'] ?? 'vi');
        App::setLocale($locale);
        $document = is_array($payload['document'] ?? null) ? $payload['document'] : [];
        $translation = $session->page->translations->firstWhere('locale', $locale)
            ?? $session->page->translations->firstWhere('locale', 'vi')
            ?? $session->page->translations->first();
        $settings = is_array($document['pageSettings'] ?? null) ? $document['pageSettings'] : [];

        return view('pages.page-builder-preview', [
            ...$this->viewData->forPage('home'),
            'metaTitle' => $translation instanceof PageTranslation ? $translation->title : $session->page->code,
            'currentPage' => 'page-builder-preview',
            'isPageBuilderPreview' => true,
            'hideHeader' => (bool) ($settings['hideHeader'] ?? false),
            'hideFooter' => (bool) ($settings['hideFooter'] ?? false),
            'pageHtml' => $this->renderer->render($document, new PageRenderOptions($locale, true, true)),
            'previewToken' => $token,
            'previewSchemaVersion' => (int) config('page_builder.preview.message_schema_version', 1),
        ]);
    }
}
