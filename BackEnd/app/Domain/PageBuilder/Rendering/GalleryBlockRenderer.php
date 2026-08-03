<?php

namespace App\Domain\PageBuilder\Rendering;

final class GalleryBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.gallery';
    }
}
