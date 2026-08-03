<?php

namespace App\Domain\PageBuilder\Rendering;

final class RichTextBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.rich-text';
    }
}
