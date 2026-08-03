<?php

namespace App\Jobs\Seo;

use App\Domain\Seo\SitemapGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class RegenerateSitemaps implements ShouldQueue
{
    use Queueable;

    public function handle(SitemapGenerator $generator): void
    {
        $generator->index();
        foreach ($generator->shardNames() as $name) {
            $generator->shard($name);
        }
    }
}
