<?php

namespace Tests\Feature\Architecture;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use SplFileInfo;
use Tests\TestCase;

final class BackendArchitectureTest extends TestCase
{
    private const CONTROLLER_MAX_LINES = 150;

    public function test_controllers_remain_below_the_thin_controller_threshold(): void
    {
        $violations = [];

        foreach (File::allFiles(app_path('Http/Controllers')) as $file) {
            $lines = count(file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: []);
            if ($lines > self::CONTROLLER_MAX_LINES) {
                $violations[] = $file->getRelativePathname()." ({$lines} lines)";
            }
        }

        $this->assertSame([], $violations, 'Controllers above '.self::CONTROLLER_MAX_LINES.' lines: '.implode(', ', $violations));
    }

    public function test_blade_views_do_not_query_the_database(): void
    {
        $violations = $this->matchingFiles(resource_path('views'), [
            '/\\bDB::/',
            '/::(?:query|where|find|findOrFail|all)\\s*\\(/',
            '/@inject\\s*\\([^)]*App\\\\Models\\\\/',
        ]);

        $this->assertSame([], $violations, 'Database access found in Blade views: '.implode(', ', $violations));
    }

    public function test_page_builder_cannot_select_or_execute_an_arbitrary_renderer(): void
    {
        $patterns = [
            '/\\beval\\s*\\(/i',
            '/\\b(?:exec|passthru|proc_open|shell_exec|system)\\s*\\(/i',
            '/\\bBlade::render\\s*\\(/',
            '/\\bview\\s*\\(\\s*(?:request\\s*\\(|\\$request\\b|\\$document\\b|\\$payload\\b|\\$block\\b)/',
            '/\\b(?:include|require)(?:_once)?\\s*\\(?\\s*\\$/i',
            '/\\bnew\\s+\\$/',
            '/\\bcall_user_func(?:_array)?\\s*\\(/',
            '/\\bunserialize\\s*\\(/i',
        ];
        $violations = [
            ...$this->matchingFiles(app_path('Domain/PageBuilder'), $patterns),
            ...$this->matchingFiles(resource_path('views/components/page-builder'), $patterns),
        ];

        $this->assertDirectoryExists(app_path('Domain/PageBuilder/Registry'));
        $this->assertSame([], $violations, 'Unsafe Page Builder renderer capability found: '.implode(', ', $violations));
    }

    #[DataProvider('publicDataSourceProvider')]
    public function test_public_data_sources_enforce_published_status(string $relativePath): void
    {
        $source = File::get(app_path($relativePath));

        $this->assertMatchesRegularExpression(
            "/->where\\(\\s*['\"](?:content\\.)?status['\"]\\s*,\\s*['\"]published['\"]\\s*\\)/",
            $source,
            $relativePath.' must keep an explicit published-only constraint.',
        );
    }

    public function test_scheduled_post_publisher_runs_every_minute_without_overlap(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(static fn ($candidate): bool => str_contains((string) $candidate->command, 'posts:publish-scheduled'));

        $this->assertNotNull($event, 'posts:publish-scheduled is not registered.');
        $this->assertSame('* * * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }

    /** @return array<string, array{string}> */
    public static function publicDataSourceProvider(): array
    {
        return [
            'crop solutions' => ['Domain/CropSolutions/CropSolutionDataSource.php'],
            'posts' => ['Domain/Posts/PostDataSource.php'],
            'public search' => ['Domain/Search/PublicSearchQuery.php'],
            'related content' => ['Domain/Search/RelatedContentQuery.php'],
            'services' => ['Domain/Services/ServiceDataSource.php'],
            'showcase' => ['Domain/Showcase/ShowcaseDataSource.php'],
            'sitemap' => ['Domain/Seo/SitemapGenerator.php'],
            'transportation' => ['Domain/Transportation/TransportationDataSource.php'],
            'warehouses' => ['Domain/Warehouses/WarehouseDataSource.php'],
        ];
    }

    /** @param list<string> $patterns @return list<string> */
    private function matchingFiles(string $directory, array $patterns): array
    {
        $violations = [];

        foreach (File::allFiles($directory) as $file) {
            if (! $this->isPhpSource($file)) {
                continue;
            }

            $source = File::get($file->getPathname());
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $source) === 1) {
                    $violations[] = $file->getRelativePathname().' matches '.$pattern;
                }
            }
        }

        return $violations;
    }

    private function isPhpSource(SplFileInfo $file): bool
    {
        return $file->getExtension() === 'php' || str_ends_with($file->getFilename(), '.blade.php');
    }
}
