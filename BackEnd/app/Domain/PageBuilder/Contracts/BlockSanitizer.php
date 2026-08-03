<?php

namespace App\Domain\PageBuilder\Contracts;

interface BlockSanitizer
{
    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public function sanitize(array $block): array;
}
