<?php

namespace App\Domain\PageBuilder\Contracts;

interface BlockRenderer
{
    public function view(): string;

    /**
     * @param  array<string, mixed>  $block
     */
    public function render(array $block, string $childrenHtml): string;
}
