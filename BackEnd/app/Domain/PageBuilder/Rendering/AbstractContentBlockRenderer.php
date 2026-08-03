<?php

namespace App\Domain\PageBuilder\Rendering;

use App\Domain\PageBuilder\Contracts\BlockRenderer;
use Illuminate\Contracts\View\Factory;

abstract class AbstractContentBlockRenderer implements BlockRenderer
{
    public function __construct(private readonly Factory $views, private readonly ContentBlockViewData $data) {}

    public function render(array $block, string $childrenHtml): string
    {
        return $this->views->make($this->view(), $this->data->make($block, $childrenHtml))->render();
    }
}
