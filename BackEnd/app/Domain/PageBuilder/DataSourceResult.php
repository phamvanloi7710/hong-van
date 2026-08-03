<?php

namespace App\Domain\PageBuilder;

final readonly class DataSourceResult
{
    /**
     * @param  list<array<string, mixed>>  $items
     * @param  list<string>  $cacheTags
     */
    public function __construct(
        public array $items,
        public array $cacheTags,
        public bool $sample = false,
    ) {}

    /** @return array{items:list<array<string,mixed>>,cacheTags:list<string>,empty:bool,sample:bool} */
    public function toArray(): array
    {
        return ['items' => $this->items, 'cacheTags' => $this->cacheTags, 'empty' => $this->items === [], 'sample' => $this->sample];
    }
}
