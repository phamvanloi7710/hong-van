<?php

namespace App\Domain\PageBuilder\Rendering;

final class ColumnsBlockRenderer extends AbstractLayoutBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.columns';
    }
}
