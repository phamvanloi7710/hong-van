<?php

namespace App\Domain\PageBuilder\Rendering;

use App\Domain\PageBuilder\Contracts\BlockRenderer;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\HtmlString;

abstract class AbstractLayoutBlockRenderer implements BlockRenderer
{
    public function __construct(private readonly Factory $views, private readonly LayoutClassResolver $classes) {}

    public function render(array $block, string $childrenHtml): string
    {
        return $this->views->make($this->view(), [
            'blockId' => (string) $block['id'],
            'classes' => $this->classes->classes($block),
            'props' => (array) $block['props'],
            'children' => new HtmlString($childrenHtml),
            'backgroundImageUrl' => $this->classes->backgroundImageUrl($block),
        ])->render();
    }
}
