<?php

namespace App\Domain\PageBuilder\Sanitization;

use App\Domain\PageBuilder\Contracts\BlockSanitizer;
use Illuminate\Validation\ValidationException;

final class LayoutBlockSanitizer implements BlockSanitizer
{
    public function sanitize(array $block, string $path): array
    {
        if ($block['type'] === 'layout.section') {
            $block = $this->sanitizeSection($block, $path);
        }
        if ($block['type'] === 'layout.columns') {
            $this->validateColumns($block, $path);
        }
        if ($block['type'] === 'layout.grid') {
            $mobileColumns = (string) data_get($block, 'style.mobile.columns', '1');
            if (! in_array($mobileColumns, ['1', '2'], true)) {
                throw ValidationException::withMessages(["{$path}.style.mobile.columns" => [__('page_builder.validation.mobile_grid_columns')]]);
            }
        }

        return $block;
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    private function sanitizeSection(array $block, string $path): array
    {
        $label = data_get($block, 'props.ariaLabel');
        if (is_string($label)) {
            $block['props']['ariaLabel'] = trim(str_replace("\0", '', $label));
        }

        if (data_get($block, 'props.background') === 'media') {
            $mediaId = data_get($block, 'props.backgroundMediaId');
            if (! is_string($mediaId) || preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $mediaId) !== 1) {
                throw ValidationException::withMessages(["{$path}.props.backgroundMediaId" => [__('page_builder.validation.background_media_required')]]);
            }
        } else {
            unset($block['props']['backgroundMediaId']);
        }

        return $block;
    }

    /** @param array<string, mixed> $block */
    private function validateColumns(array $block, string $path): void
    {
        $preset = (string) data_get($block, 'props.desktopPreset');
        $expected = match ($preset) {
            'equal-3' => 3,
            'equal-4' => 4,
            default => 2,
        };
        $children = is_array($block['children'] ?? null) ? $block['children'] : [];
        if (count($children) !== $expected) {
            throw ValidationException::withMessages(["{$path}.children" => [__('page_builder.validation.columns_children', ['count' => $expected])]]);
        }
    }
}
