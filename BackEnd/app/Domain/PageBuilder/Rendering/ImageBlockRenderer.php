<?php

namespace App\Domain\PageBuilder\Rendering;

final class ImageBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.image';
    }
}
