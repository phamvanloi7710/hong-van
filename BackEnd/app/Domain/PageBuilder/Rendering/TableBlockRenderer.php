<?php

namespace App\Domain\PageBuilder\Rendering;

final class TableBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.table';
    }
}
