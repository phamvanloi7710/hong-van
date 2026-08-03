<?php

namespace App\Domain\Themes;

use Illuminate\Validation\ValidationException;

final class ThemeTokenSchema
{
    /** @return array<string, mixed> */
    public function defaults(): array
    {
        return (array) config('public_theme.defaults', []);
    }

    /** @param array<string, mixed> $tokens */
    public function validate(array $tokens): void
    {
        $errors = [];
        $defaults = $this->defaults();
        $expected = $this->flatten($defaults);
        $actual = $this->flatten($tokens);

        foreach ($tokens as $group => $values) {
            if (! array_key_exists($group, $defaults)) {
                $errors["tokens.{$group}"][] = 'Nhóm token này không nằm trong allowlist.';

                continue;
            }
            if (! is_array($values)) {
                $errors["tokens.{$group}"][] = 'Nhóm token phải là object.';

                continue;
            }
            foreach (array_keys($values) as $key) {
                if (! array_key_exists($key, (array) $defaults[$group])) {
                    $errors["tokens.{$group}.{$key}"][] = 'Token này không nằm trong allowlist.';
                }
            }
        }

        foreach (array_diff(array_keys($actual), array_keys($expected)) as $path) {
            $errors["tokens.{$path}"][] = 'Token này không nằm trong allowlist.';
        }
        foreach (array_diff(array_keys($expected), array_keys($actual)) as $path) {
            $errors["tokens.{$path}"][] = 'Token này là bắt buộc.';
        }

        foreach ($actual as $path => $value) {
            if (! array_key_exists($path, $expected)) {
                continue;
            }

            if (str_starts_with($path, 'colors.')) {
                $this->requireMatch($errors, $path, $value, '/^#[0-9a-fA-F]{6}$/', 'Màu phải dùng định dạng #RRGGBB.');
            } elseif (str_starts_with($path, 'fonts.')) {
                $this->requireOption($errors, $path, $value, array_keys((array) config('public_theme.fonts', [])));
            } elseif ($path === 'shadows.preset') {
                $this->requireOption($errors, $path, $value, array_keys((array) config('public_theme.shadow_presets', [])));
            } elseif ($path === 'animation.preset') {
                $this->requireOption($errors, $path, $value, array_keys((array) config('public_theme.animation_presets', [])));
            } elseif ($path === 'buttons.radius') {
                $this->requireOption($errors, $path, $value, ['small', 'medium', 'large', 'pill']);
            } elseif ($path === 'headings.line_height') {
                $this->requireNumber($errors, $path, $value, 1, 2);
            } elseif (str_starts_with($path, 'radii.')) {
                $this->requireNumber($errors, $path, $value, 0, 999);
            } elseif (str_starts_with($path, 'sizes.')) {
                $this->requireNumber($errors, $path, $value, 10, 120);
            } elseif (str_starts_with($path, 'spacing.') || $path === 'sections.gap') {
                $this->requireNumber($errors, $path, $value, 0, 240);
            } elseif (str_starts_with($path, 'containers.')) {
                $this->requireNumber($errors, $path, $value, 8, 1920);
            } elseif ($path === 'buttons.min_height' || $path === 'buttons.horizontal_padding') {
                $this->requireNumber($errors, $path, $value, 0, 120);
            } elseif ($path === 'buttons.font_weight' || $path === 'headings.font_weight') {
                $this->requireNumber($errors, $path, $value, 300, 900);
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /** @param array<string, mixed> $tokens */
    public function checksum(array $tokens): string
    {
        $sorted = $this->sortRecursively($tokens);

        return hash('sha256', json_encode($sorted, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function flatten(array $values, string $prefix = ''): array
    {
        $flat = [];
        foreach ($values as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            if (is_array($value)) {
                $flat += $this->flatten($value, $path);
            } else {
                $flat[$path] = $value;
            }
        }

        return $flat;
    }

    /** @param array<string, list<string>> $errors */
    private function requireMatch(array &$errors, string $path, mixed $value, string $pattern, string $message): void
    {
        if (! is_string($value) || preg_match($pattern, $value) !== 1) {
            $errors["tokens.{$path}"][] = $message;
        }
    }

    /**
     * @param  array<string, list<string>>  $errors
     * @param  list<string>  $options
     */
    private function requireOption(array &$errors, string $path, mixed $value, array $options): void
    {
        if (! is_string($value) || ! in_array($value, $options, true)) {
            $errors["tokens.{$path}"][] = 'Giá trị token không hợp lệ.';
        }
    }

    /** @param array<string, list<string>> $errors */
    private function requireNumber(array &$errors, string $path, mixed $value, float $min, float $max): void
    {
        if (! is_int($value) && ! is_float($value)) {
            $errors["tokens.{$path}"][] = 'Token phải là số.';
        } elseif ($value < $min || $value > $max) {
            $errors["tokens.{$path}"][] = "Token phải nằm trong khoảng {$min}–{$max}.";
        }
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function sortRecursively(array $values): array
    {
        ksort($values);
        foreach ($values as &$value) {
            if (is_array($value)) {
                $value = $this->sortRecursively($value);
            }
        }

        return $values;
    }
}
