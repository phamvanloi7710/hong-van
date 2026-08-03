<?php

namespace App\Domain\PageBuilder\Rendering;

final class ButtonBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.button';
    }
}
