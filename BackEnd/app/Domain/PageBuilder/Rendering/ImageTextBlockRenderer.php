<?php

namespace App\Domain\PageBuilder\Rendering;

final class ImageTextBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.image-text';
    }
}
