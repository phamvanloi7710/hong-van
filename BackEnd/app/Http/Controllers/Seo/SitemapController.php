<?php

namespace App\Http\Controllers\Seo;

use App\Domain\Seo\SitemapGenerator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

final class SitemapController extends Controller
{
    public function index(SitemapGenerator $generator): Response
    {
        return response($generator->index(), 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'X-Robots-Tag' => 'noindex']);
    }

    public function shard(string $name, SitemapGenerator $generator): Response
    {
        $xml = $generator->shard($name);
        abort_if($xml === null, 404);

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'X-Robots-Tag' => 'noindex']);
    }
}
