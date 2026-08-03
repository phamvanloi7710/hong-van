<?php

namespace App\Domain\PageBuilder\Rendering;

final class SectionBlockRenderer extends AbstractLayoutBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.section';
    }
}
