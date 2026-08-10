<?php

namespace Tests\Feature\Architecture;

use App\Jobs\Dashboard\GenerateLeadReportExport;
use App\Jobs\Leads\DispatchLeadNotifications;
use App\Jobs\Media\GenerateMediaVariants;
use App\Jobs\Seo\RegenerateSitemaps;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class QueueCacheRedisContractTest extends TestCase
{
    public function test_example_environment_isolates_redis_cache_and_uses_an_asynchronous_queue(): void
    {
        $variables = $this->environmentVariables();

        $this->assertSame('redis', $variables['QUEUE_CONNECTION']);
        $this->assertSame('redis', $variables['CACHE_STORE']);
        $this->assertSame('redis', $variables['CACHE_LIMITER_STORE']);
        $this->assertSame('redis', $variables['PAGE_BUILDER_PREVIEW_CACHE_STORE']);
        $this->assertSame('hongvan_cache_', $variables['CACHE_PREFIX']);
        $this->assertSame('0', $variables['REDIS_DB']);
        $this->assertSame('1', $variables['REDIS_CACHE_DB']);
        $this->assertNotSame($variables['REDIS_DB'], $variables['REDIS_CACHE_DB']);
        $this->assertSame('hongvan_jobs', $variables['DB_QUEUE_TABLE']);
        $this->assertSame('hongvan_job_batches', $variables['DB_QUEUE_BATCH_TABLE']);
        $this->assertSame('hongvan_failed_jobs', $variables['DB_FAILED_JOBS_TABLE']);
        $this->assertNotSame('sync', $variables['QUEUE_CONNECTION']);
    }

    public function test_redis_supports_tags_while_local_fallback_stores_remain_available(): void
    {
        $this->assertSame('redis', config('cache.stores.redis.driver'));
        $this->assertSame('cache', config('cache.stores.redis.connection'));
        $this->assertTrue(Cache::store('redis')->supportsTags());
        $this->assertSame('file', config('cache.stores.file.driver'));
        $this->assertSame('database', config('cache.stores.database.driver'));
        $this->assertSame('database', config('queue.connections.database.driver'));
    }

    public function test_heavy_jobs_cannot_silently_fall_back_to_explicit_sync_dispatch(): void
    {
        foreach ([
            GenerateMediaVariants::class,
            DispatchLeadNotifications::class,
            RegenerateSitemaps::class,
            GenerateLeadReportExport::class,
        ] as $job) {
            $this->assertTrue(is_subclass_of($job, ShouldQueue::class), "$job must remain asynchronous.");
        }

        $syncDispatches = [];
        foreach (File::allFiles(app_path()) as $file) {
            $source = File::get($file->getPathname());
            foreach (['dispatchSync(', 'dispatchNow('] as $call) {
                if (str_contains($source, $call)) {
                    $syncDispatches[] = $file->getRelativePathname().': '.$call;
                }
            }
        }

        $this->assertSame([], $syncDispatches, 'Heavy work must not use explicit synchronous dispatch.');
    }

    /** @return array<string, string> */
    private function environmentVariables(): array
    {
        $content = File::get(base_path('.env.example'));
        preg_match_all('/^([A-Z][A-Z0-9_]*)=(.*)$/m', $content, $matches, PREG_SET_ORDER);

        $variables = [];
        foreach ($matches as $match) {
            $variables[$match[1]] = trim($match[2], " \t\n\r\0\x0B\"'");
        }

        return $variables;
    }
}
