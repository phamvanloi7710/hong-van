<?php

namespace App\Domain\PageBuilder\Rendering;

final class DividerBlockRenderer extends AbstractLayoutBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.divider';
    }
}
