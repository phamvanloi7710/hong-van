<?php

namespace App\Domain\PageBuilder\Rendering;

final class QuoteBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.quote';
    }
}
