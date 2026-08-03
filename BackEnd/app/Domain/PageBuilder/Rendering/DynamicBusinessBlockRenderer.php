<?php

namespace App\Domain\PageBuilder\Rendering;

use App\Domain\PageBuilder\Contracts\BlockRenderer;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Lang;

final readonly class DynamicBusinessBlockRenderer implements BlockRenderer
{
    public function __construct(private Factory $views, private LayoutClassResolver $classes) {}

    public function view(): string
    {
        return 'components.page-builder.blocks.dynamic-business';
    }

    public function render(array $block, string $childrenHtml): string
    {
        $data = is_array($block['_dynamicData'] ?? null) ? $block['_dynamicData'] : ['items' => [], 'empty' => true, 'sample' => false, 'preview' => false];
        $locale = is_string($data['locale'] ?? null) ? $data['locale'] : app()->getLocale();

        return $this->views->make($this->view(), [
            'blockId' => (string) ($block['id'] ?? ''), 'type' => (string) ($block['type'] ?? ''),
            'classes' => $this->classes->classes($block), 'props' => is_array($block['props'] ?? null) ? $block['props'] : [],
            'items' => is_array($data['items'] ?? null) ? $data['items'] : [], 'empty' => (bool) ($data['empty'] ?? true),
            'sample' => (bool) ($data['sample'] ?? false), 'preview' => (bool) ($data['preview'] ?? false),
            'emptyLabel' => Lang::get('page_builder.preview.empty', [], $locale),
            'statLabels' => Lang::get('page_builder.stats', [], $locale),
        ])->render();
    }
}
