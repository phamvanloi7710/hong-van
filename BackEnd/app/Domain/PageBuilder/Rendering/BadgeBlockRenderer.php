<?php

namespace App\Domain\PageBuilder\Rendering;

final class BadgeBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.badge';
    }
}
