<?php

namespace App\Domain\PageBuilder;

use App\Domain\PageBuilder\Contracts\BlockVersionMigration;
use Illuminate\Contracts\Container\Container;
use Illuminate\Validation\ValidationException;

final readonly class PageDocumentMigrator
{
    public function __construct(
        private BlockRegistry $registry,
        private PageDocumentValidator $validator,
        private Container $container,
    ) {}

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    public function migrate(array $document): array
    {
        $schemaVersion = $document['schemaVersion'] ?? null;
        if (! is_int($schemaVersion) || $schemaVersion < 1 || $schemaVersion > PageDocumentSchema::VERSION) {
            throw ValidationException::withMessages(['document.schemaVersion' => [__('page_builder.validation.schema_version')]]);
        }

        $blocks = $document['blocks'] ?? [];
        if (is_array($blocks) && array_is_list($blocks)) {
            $document['blocks'] = array_map(fn (mixed $block): mixed => $this->migrateBlock($block), $blocks);
        }
        $document['schemaVersion'] = PageDocumentSchema::VERSION;

        return $this->validator->validate($document);
    }

    /** @return array<string, mixed>|mixed */
    private function migrateBlock(mixed $value): mixed
    {
        if (! is_array($value) || array_is_list($value) || ! is_string($value['type'] ?? null)) {
            return $value;
        }
        $definition = $this->registry->get($value['type'], 'document.blocks.type');
        $version = $value['version'] ?? null;
        if (! is_int($version) || $version < 1 || $version > $definition->version) {
            throw ValidationException::withMessages(['document.blocks.version' => [__('page_builder.validation.block_version')]]);
        }

        while ($version < $definition->version) {
            $migration = $this->migrationFrom($definition, $version);
            $value = $migration->migrate($value);
            $version = $migration->toVersion();
            $value['version'] = $version;
        }
        $children = $value['children'] ?? [];
        if (is_array($children) && array_is_list($children)) {
            $value['children'] = array_map(fn (mixed $child): mixed => $this->migrateBlock($child), $children);
        }

        return $value;
    }

    private function migrationFrom(BlockDefinition $definition, int $version): BlockVersionMigration
    {
        foreach ($definition->migrations as $migrationClass) {
            $migration = $this->container->make($migrationClass);
            if ($migration instanceof BlockVersionMigration && $migration->fromVersion() === $version && $migration->toVersion() === $version + 1) {
                return $migration;
            }
        }

        throw ValidationException::withMessages(['document.blocks.version' => [__('page_builder.validation.migration_missing')]]);
    }
}
