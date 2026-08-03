<?php

namespace App\Domain\PageBuilder;

final readonly class DynamicBlockDataLoader
{
    public function __construct(private DataSourceRegistry $sources) {}

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, array<string, mixed>>
     */
    public function load(array $document, PageRenderOptions $options): array
    {
        $resolved = [];
        $memo = [];
        $this->walk(is_array($document['blocks'] ?? null) ? $document['blocks'] : [], $options, $resolved, $memo);

        return $resolved;
    }

    /**
     * @param  list<mixed>  $blocks
     * @param  array<string, array<string, mixed>>  $resolved
     * @param  array<string, DataSourceResult>  $memo
     */
    private function walk(array $blocks, PageRenderOptions $options, array &$resolved, array &$memo): void
    {
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            $id = $block['id'] ?? null;
            $bindings = $block['bindings'] ?? null;
            if (is_string($id) && is_array($bindings) && is_string($bindings['source'] ?? null)) {
                $normalized = $this->sources->normalize($bindings);
                $key = hash('sha256', json_encode([$options->locale, $options->mayUseSampleData(), $normalized], JSON_THROW_ON_ERROR));
                $memo[$key] ??= $this->sources->resolve($normalized, $options);
                $resolved[$id] = [...$memo[$key]->toArray(), 'preview' => $options->preview, 'locale' => $options->locale];
            }
            $children = is_array($block['children'] ?? null) ? $block['children'] : [];
            $this->walk($children, $options, $resolved, $memo);
        }
    }
}
