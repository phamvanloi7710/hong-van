<?php

namespace App\Domain\PageBuilder\Sanitization;

use App\Domain\PageBuilder\Contracts\BlockSanitizer;
use Illuminate\Validation\ValidationException;

final class DynamicBusinessBlockSanitizer implements BlockSanitizer
{
    public function sanitize(array $block, string $path): array
    {
        $props = is_array($block['props'] ?? null) ? $block['props'] : [];
        foreach ($props as $key => $value) {
            if (is_string($value)) {
                $props[$key] = trim(str_replace("\0", '', $value));
            }
        }
        $url = (string) ($props['ctaUrl'] ?? '');
        if ($url !== '' && ! str_starts_with($url, '/') && ! str_starts_with($url, '#') && filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw ValidationException::withMessages(["{$path}.props.ctaUrl" => [__('page_builder.validation.unsafe_url')]]);
        }
        if (($url === '') !== ((string) ($props['ctaLabel'] ?? '') === '')) {
            throw ValidationException::withMessages(["{$path}.props.ctaLabel" => [__('page_builder.validation.link_pair')]]);
        }
        $block['props'] = $props;

        return $block;
    }
}
