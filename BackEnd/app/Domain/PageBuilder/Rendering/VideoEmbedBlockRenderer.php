<?php

namespace App\Domain\PageBuilder\Rendering;

final class VideoEmbedBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.video-embed';
    }
}
