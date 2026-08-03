<?php

namespace App\Domain\PageBuilder;

use App\Domain\PageBuilder\Contracts\BlockRenderer;
use App\Domain\PageBuilder\Contracts\BlockSanitizer;
use App\Domain\PageBuilder\Contracts\BlockVersionMigration;

final readonly class BlockDefinition
{
    /**
     * @param  array<string, string>  $labels
     * @param  array<string, mixed>  $propsSchema
     * @param  array<string, mixed>  $styleSchema
     * @param  array<string, mixed>  $visibilitySchema
     * @param  array<string, mixed>  $bindingsSchema
     * @param  array<string, mixed>  $defaults
     * @param  list<string>  $allowedParents
     * @param  list<string>  $allowedChildren
     * @param  list<string>  $dataDependencies
     * @param  list<string>  $permissions
     * @param  list<string>  $cacheTags
     * @param  class-string<BlockRenderer>  $renderer
     * @param  class-string<BlockSanitizer>  $sanitizer
     * @param  list<class-string<BlockVersionMigration>>  $migrations
     * @param  array<string, mixed>  $testFixture
     */
    public function __construct(
        public string $type,
        public int $version,
        public array $labels,
        public string $category,
        public string $icon,
        public ?string $thumbnail,
        public array $propsSchema,
        public array $styleSchema,
        public array $visibilitySchema,
        public array $bindingsSchema,
        public array $defaults,
        public bool $allowRoot,
        public array $allowedParents,
        public array $allowedChildren,
        public array $dataDependencies,
        public array $permissions,
        public array $cacheTags,
        public string $renderer,
        public string $sanitizer,
        public array $migrations,
        public array $testFixture,
    ) {}

    /** @return array<string, mixed> */
    public function metadata(): array
    {
        return [
            'type' => $this->type,
            'version' => $this->version,
            'labels' => $this->labels,
            'category' => $this->category,
            'icon' => $this->icon,
            'thumbnail' => $this->thumbnail,
            'schema' => [
                'props' => $this->propsSchema,
                'style' => $this->styleSchema,
                'visibility' => $this->visibilitySchema,
                'bindings' => $this->bindingsSchema,
            ],
            'defaults' => $this->defaults,
            'allowRoot' => $this->allowRoot,
            'allowedParents' => $this->allowedParents,
            'allowedChildren' => $this->allowedChildren,
            'dataDependencies' => $this->dataDependencies,
            'permissions' => $this->permissions,
        ];
    }
}
