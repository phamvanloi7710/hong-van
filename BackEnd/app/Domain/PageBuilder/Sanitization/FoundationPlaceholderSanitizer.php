<?php

namespace App\Domain\PageBuilder\Sanitization;

use App\Domain\PageBuilder\Contracts\BlockSanitizer;

final class FoundationPlaceholderSanitizer implements BlockSanitizer
{
    public function sanitize(array $block, string $path): array
    {
        $label = $block['props']['label'] ?? null;
        if (is_string($label)) {
            $block['props']['label'] = trim(str_replace("\0", '', $label));
        }

        return $block;
    }
}
