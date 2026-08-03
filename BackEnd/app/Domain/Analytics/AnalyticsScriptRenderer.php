<?php

namespace App\Domain\Analytics;

final class AnalyticsScriptRenderer
{
    /** @param list<array{src: string, attributes: array<string, string|bool>}> $scripts */
    public function render(array $scripts, string $nonce): string
    {
        if ($nonce === '') {
            return '';
        }

        return implode("\n", array_map(function (array $script) use ($nonce): string {
            $attributes = ['nonce' => $nonce, 'src' => $script['src'], ...$script['attributes']];
            $html = [];

            foreach ($attributes as $name => $value) {
                if ($value === true) {
                    $html[] = htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8');
                } elseif (is_string($value)) {
                    $html[] = sprintf('%s="%s"', htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8'), htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
                }
            }

            return '<script '.implode(' ', $html).'></script>';
        }, $scripts));
    }
}
