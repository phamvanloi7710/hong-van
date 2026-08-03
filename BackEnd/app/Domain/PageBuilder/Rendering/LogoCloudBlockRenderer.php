<?php

namespace App\Domain\PageBuilder\Rendering;

final class LogoCloudBlockRenderer extends AbstractContentBlockRenderer
{
    public function view(): string
    {
        return 'components.page-builder.blocks.logo-cloud';
    }
}
