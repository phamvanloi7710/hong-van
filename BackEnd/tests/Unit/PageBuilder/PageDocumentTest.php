<?php

namespace Tests\Unit\PageBuilder;

use App\Domain\PageBuilder\BlockDefinition;
use App\Domain\PageBuilder\BlockRegistry;
use App\Domain\PageBuilder\Contracts\BlockVersionMigration;
use App\Domain\PageBuilder\PageDocumentMigrator;
use App\Domain\PageBuilder\PageDocumentSchema;
use App\Domain\PageBuilder\PageDocumentValidator;
use App\Domain\PageBuilder\Rendering\FoundationPlaceholderRenderer;
use App\Domain\PageBuilder\Sanitization\FoundationPlaceholderSanitizer;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class PageDocumentTest extends TestCase
{
    public function test_validator_rejects_duplicate_ids_invalid_children_and_excessive_depth(): void
    {
        $validator = app(PageDocumentValidator::class);
        $duplicate = PageDocumentSchema::emptyDocument();
        $duplicate['blocks'] = [$this->placeholder('duplicate-0001'), $this->placeholder('duplicate-0001')];
        $this->expectValidationPath(fn () => $validator->validate($duplicate), 'document.blocks.1.id');

        $child = PageDocumentSchema::emptyDocument();
        $root = $this->placeholder('parent-00000001');
        $root['children'][] = $this->placeholder('child-000000001');
        $child['blocks'][] = $root;
        $this->expectValidationPath(fn () => $validator->validate($child), 'document.blocks.0.children');

        $deep = PageDocumentSchema::emptyDocument();
        $deep['blocks'][] = $this->nestedPlaceholder(13);
        $this->expectValidationPath(fn () => $validator->validate($deep), 'document.blocks.0.children.0.children.0.children.0.children.0.children.0.children.0.children.0.children.0.children.0.children.0.children.0.children.0');
    }

    public function test_binding_cycle_detection_reports_the_source_path(): void
    {
        $registry = new BlockRegistry([$this->nodeDefinition(1)]);
        $validator = new PageDocumentValidator($registry, app());
        $document = PageDocumentSchema::emptyDocument();
        $document['blocks'] = [
            $this->node('node-a-0001', 'node-b-0002'),
            $this->node('node-b-0002', 'node-a-0001'),
        ];

        $this->expectValidationPath(fn () => $validator->validate($document), 'document.blocks.1.bindings.link.sourceBlockId');
    }

    public function test_block_versions_migrate_sequentially_before_validation(): void
    {
        $registry = new BlockRegistry([$this->nodeDefinition(2, [TestNodeV1ToV2::class])]);
        $validator = new PageDocumentValidator($registry, app());
        $migrator = new PageDocumentMigrator($registry, $validator, app());
        $document = PageDocumentSchema::emptyDocument();
        $block = $this->node('node-migrate-01', null);
        $block['version'] = 1;
        $block['props'] = [];
        $document['blocks'][] = $block;

        $migrated = $migrator->migrate($document);

        $this->assertSame(2, $migrated['blocks'][0]['version']);
        $this->assertSame('migrated', $migrated['blocks'][0]['props']['label']);
    }

    /** @param callable(): mixed $callback */
    private function expectValidationPath(callable $callback, string $path): void
    {
        try {
            $callback();
            $this->fail('Expected a PageDocument validation exception.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($path, $exception->errors());
        }
    }

    /** @return array<string, mixed> */
    private function placeholder(string $id): array
    {
        return [
            'id' => $id, 'type' => 'foundation.placeholder', 'version' => 1,
            'props' => ['label' => 'Placeholder'], 'style' => ['desktop' => [], 'tablet' => [], 'mobile' => []],
            'visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true], 'bindings' => [], 'children' => [],
        ];
    }

    /** @return array<string, mixed> */
    private function nestedPlaceholder(int $depth, int $index = 1): array
    {
        $block = $this->placeholder('deep-node-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT));
        if ($depth > 1) {
            $block['children'][] = $this->nestedPlaceholder($depth - 1, $index + 1);
        }

        return $block;
    }

    /** @param list<class-string<BlockVersionMigration>> $migrations */
    private function nodeDefinition(int $version, array $migrations = []): BlockDefinition
    {
        $empty = ['type' => 'object', 'properties' => [], 'additionalProperties' => false];

        return new BlockDefinition(
            type: 'test.node', version: $version, labels: ['vi' => 'Node', 'en' => 'Node', 'zh' => 'Node'], category: 'test', icon: 'node', thumbnail: null,
            propsSchema: ['type' => 'object', 'properties' => ['label' => ['type' => 'string', 'maxLength' => 160]], 'required' => ['label'], 'additionalProperties' => false],
            styleSchema: $empty, visibilitySchema: $empty,
            bindingsSchema: ['type' => 'object', 'properties' => ['link' => ['type' => 'object', 'properties' => ['sourceBlockId' => ['type' => 'string']], 'required' => ['sourceBlockId'], 'additionalProperties' => false]], 'additionalProperties' => false],
            defaults: [], allowRoot: true, allowedParents: ['test.node'], allowedChildren: ['test.node'], dataDependencies: [], permissions: [], cacheTags: [],
            renderer: FoundationPlaceholderRenderer::class, sanitizer: FoundationPlaceholderSanitizer::class, migrations: $migrations, testFixture: [],
        );
    }

    /** @return array<string, mixed> */
    private function node(string $id, ?string $source): array
    {
        return [
            'id' => $id, 'type' => 'test.node', 'version' => 1, 'props' => ['label' => 'Node'], 'style' => [], 'visibility' => [],
            'bindings' => $source === null ? [] : ['link' => ['sourceBlockId' => $source]], 'children' => [],
        ];
    }
}

final class TestNodeV1ToV2 implements BlockVersionMigration
{
    public function fromVersion(): int
    {
        return 1;
    }

    public function toVersion(): int
    {
        return 2;
    }

    public function migrate(array $block): array
    {
        $block['props']['label'] = 'migrated';

        return $block;
    }
}
