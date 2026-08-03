<?php

namespace App\Domain\PageBuilder\Rendering;

final class SpacerBlockRenderer extends AbstractLayoutBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.spacer';
    }
}
