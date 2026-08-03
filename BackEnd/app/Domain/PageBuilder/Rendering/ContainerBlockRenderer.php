<?php

namespace App\Domain\PageBuilder\Rendering;

final class ContainerBlockRenderer extends AbstractLayoutBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.container';
    }
}
