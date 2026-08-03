<?php

namespace App\Domain\PageBuilder\Rendering;

use App\Domain\PageBuilder\Contracts\BlockRenderer;

final class FoundationPlaceholderRenderer implements BlockRenderer
{
    public function view(): string
    {
        return 'page-builder.blocks.foundation-placeholder';
    }
}
