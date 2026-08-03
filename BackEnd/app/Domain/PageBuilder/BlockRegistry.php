<?php

namespace App\Domain\PageBuilder;

use App\Domain\PageBuilder\Rendering\FoundationPlaceholderRenderer;
use App\Domain\PageBuilder\Sanitization\FoundationPlaceholderSanitizer;
use Illuminate\Validation\ValidationException;

final class BlockRegistry
{
    /** @var array<string, BlockDefinition> */
    private array $definitions = [];

    /** @param list<BlockDefinition>|null $definitions */
    public function __construct(?array $definitions = null)
    {
        foreach ($definitions ?? [$this->foundationPlaceholder(), ...LayoutBlockDefinitions::definitions(), ...ContentMediaBlockDefinitions::definitions(), ...DynamicBusinessBlockDefinitions::definitions(), ...FormBlockDefinitions::definitions()] as $definition) {
            $this->register($definition);
        }
    }

    public function register(BlockDefinition $definition): void
    {
        if (isset($this->definitions[$definition->type])) {
            throw new \LogicException("Duplicate Page Builder block type [{$definition->type}].");
        }
        $this->definitions[$definition->type] = $definition;
    }

    public function get(string $type, string $path = 'document.blocks'): BlockDefinition
    {
        $definition = $this->definitions[$type] ?? null;
        if (! $definition instanceof BlockDefinition) {
            throw ValidationException::withMessages([$path => [__('page_builder.validation.unknown_block')]]);
        }

        return $definition;
    }

    public function find(string $type): ?BlockDefinition
    {
        return $this->definitions[$type] ?? null;
    }

    /** @return list<array<string, mixed>> */
    public function metadata(): array
    {
        return array_values(array_map(
            static fn (BlockDefinition $definition): array => $definition->metadata(),
            $this->definitions,
        ));
    }

    private function foundationPlaceholder(): BlockDefinition
    {
        $emptyObject = ['type' => 'object', 'properties' => [], 'additionalProperties' => false];

        return new BlockDefinition(
            type: 'foundation.placeholder',
            version: 1,
            labels: ['vi' => 'Vùng chờ nội dung', 'en' => 'Content placeholder', 'zh' => '内容占位区'],
            category: 'foundation',
            icon: 'fa-regular fa-square',
            thumbnail: null,
            propsSchema: [
                'type' => 'object',
                'properties' => ['label' => ['type' => 'string', 'maxLength' => 160]],
                'required' => ['label'],
                'additionalProperties' => false,
            ],
            styleSchema: [
                'type' => 'object',
                'properties' => [
                    'desktop' => $emptyObject,
                    'tablet' => $emptyObject,
                    'mobile' => $emptyObject,
                ],
                'required' => ['desktop', 'tablet', 'mobile'],
                'additionalProperties' => false,
            ],
            visibilitySchema: [
                'type' => 'object',
                'properties' => [
                    'desktop' => ['type' => 'boolean'],
                    'tablet' => ['type' => 'boolean'],
                    'mobile' => ['type' => 'boolean'],
                ],
                'required' => ['desktop', 'tablet', 'mobile'],
                'additionalProperties' => false,
            ],
            bindingsSchema: $emptyObject,
            defaults: [
                'props' => ['label' => ''],
                'style' => ['desktop' => [], 'tablet' => [], 'mobile' => []],
                'visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
                'bindings' => [],
                'children' => [],
            ],
            allowRoot: true,
            allowedParents: ['layout.section', 'layout.container', 'layout.stack', 'layout.grid', 'layout.columns'],
            allowedChildren: [],
            maxDepth: PageDocumentSchema::MAX_DEPTH,
            minChildren: 0,
            maxChildren: 0,
            dataDependencies: [],
            permissions: [],
            cacheTags: ['page-builder'],
            renderer: FoundationPlaceholderRenderer::class,
            sanitizer: FoundationPlaceholderSanitizer::class,
            migrations: [],
            testFixture: ['label' => ''],
        );
    }
}
