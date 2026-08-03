<?php

namespace App\Domain\PageBuilder\Rendering;

final class IconBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.icon';
    }
}
