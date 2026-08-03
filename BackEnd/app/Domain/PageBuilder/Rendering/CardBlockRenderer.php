<?php

namespace App\Domain\PageBuilder\Rendering;

final class CardBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.card';
    }
}
