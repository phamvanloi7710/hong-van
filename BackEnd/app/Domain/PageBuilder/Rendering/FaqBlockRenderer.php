<?php

namespace App\Domain\PageBuilder\Rendering;

final class FaqBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.faq';
    }
}
