<?php

namespace App\Domain\PageBuilder\Rendering;

final class ListBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.list';
    }
}
