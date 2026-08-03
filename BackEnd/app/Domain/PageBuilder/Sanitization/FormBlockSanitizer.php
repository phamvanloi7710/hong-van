<?php

namespace App\Domain\PageBuilder\Sanitization;

use App\Domain\PageBuilder\Contracts\BlockSanitizer;

final class FormBlockSanitizer implements BlockSanitizer
{
    public function sanitize(array $block, string $path): array
    {
        $props = is_array($block['props'] ?? null) ? $block['props'] : [];
        foreach ($props as $key => $value) {
            $props[$key] = is_string($value) ? trim(str_replace("\0", '', $value)) : $value;
        }
        $block['props'] = $props;

        return $block;
    }
}
