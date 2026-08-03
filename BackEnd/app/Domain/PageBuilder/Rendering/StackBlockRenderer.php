<?php

namespace App\Domain\PageBuilder\Rendering;

final class StackBlockRenderer extends AbstractLayoutBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.stack';
    }
}
