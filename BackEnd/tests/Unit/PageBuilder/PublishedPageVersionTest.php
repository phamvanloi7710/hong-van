<?php

namespace Tests\Unit\PageBuilder;

use App\Domain\PageBuilder\PageDocumentSchema;
use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

final class PublishedPageVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_page_versions_are_immutable(): void
    {
        $page = Page::query()->create(['code' => 'immutable-page', 'type' => 'standard', 'status' => 'published', 'is_home' => false]);
        $version = PageVersion::query()->create([
            'page_id' => $page->getKey(), 'version_number' => 1, 'status' => 'published', 'schema_version' => 1,
            'document_json' => PageDocumentSchema::emptyDocument(), 'checksum' => str_repeat('a', 64), 'published_at' => now('UTC'),
        ]);

        $this->expectException(LogicException::class);
        $version->update(['checksum' => str_repeat('b', 64)]);
    }
}
