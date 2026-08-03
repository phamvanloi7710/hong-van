<?php

namespace App\Domain\PageBuilder\Rendering;

use App\Domain\PageBuilder\Contracts\BlockRenderer;
use Illuminate\Contracts\View\Factory;

final readonly class FormBlockRenderer implements BlockRenderer
{
    public function __construct(private Factory $views, private FormBlockViewData $data) {}

    public function view(): string
    {
        return 'components.page-builder.blocks.form';
    }

    public function render(array $block, string $childrenHtml): string
    {
        return $this->views->make($this->view(), $this->data->make($block))->render();
    }
}
