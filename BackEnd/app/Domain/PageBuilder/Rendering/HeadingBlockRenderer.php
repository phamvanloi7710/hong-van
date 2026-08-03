<?php

namespace App\Domain\PageBuilder\Rendering;

final class HeadingBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.heading';
    }
}
