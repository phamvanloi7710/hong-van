<?php

namespace App\Http\Controllers\Seo;

use App\Domain\Settings\CompanySettingsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

final class RobotsController extends Controller
{
    public function __invoke(CompanySettingsService $settings): Response
    {
        $lines = ['User-agent: *'];
        if (! (bool) $settings->value('seo_defaults', 'public_indexing_enabled', true)) {
            $lines[] = 'Disallow: /';
        } else {
            foreach (preg_split('/\R/', (string) $settings->value('seo_defaults', 'robots_disallow_paths', "/admin\n/api\n/preview")) ?: [] as $path) {
                $path = trim($path);
                if ($path !== '' && str_starts_with($path, '/')) {
                    $lines[] = 'Disallow: '.$path;
                }
            }
        }
        $lines[] = '';
        $lines[] = 'Sitemap: '.url('/sitemap.xml');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
