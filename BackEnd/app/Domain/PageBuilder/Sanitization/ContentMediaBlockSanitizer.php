<?php

namespace App\Domain\PageBuilder\Sanitization;

use App\Domain\PageBuilder\Contracts\BlockSanitizer;
use App\Domain\Posts\RichTextSanitizer;
use Illuminate\Validation\ValidationException;

final readonly class ContentMediaBlockSanitizer implements BlockSanitizer
{
    public function __construct(private RichTextSanitizer $richText) {}

    public function sanitize(array $block, string $path): array
    {
        $props = is_array($block['props'] ?? null) ? $block['props'] : [];
        $props = $this->trimStrings($props);
        $type = (string) ($block['type'] ?? '');

        if ($type === 'content.rich-text') {
            $props['html'] = $this->richText->sanitize((string) ($props['html'] ?? ''));
            $this->requireText($props['html'], "{$path}.props.html");
        }
        if ($type === 'content.heading') {
            $this->requireText($props['text'] ?? '', "{$path}.props.text");
        }
        if ($type === 'content.button') {
            $this->requireText($props['label'] ?? '', "{$path}.props.label");
            $this->requireSafeUrl((string) ($props['url'] ?? ''), "{$path}.props.url", false);
        }
        if ($type === 'content.icon' && ($props['decorative'] ?? false) !== true) {
            $this->requireText($props['label'] ?? '', "{$path}.props.label", 'accessible_label_required');
        }
        if ($type === 'content.list') {
            foreach ((array) ($props['items'] ?? []) as $index => $item) {
                $this->requireText($item, "{$path}.props.items.{$index}");
            }
        }
        if ($type === 'content.quote') {
            $this->requireText($props['text'] ?? '', "{$path}.props.text");
            $this->requireSafeUrl((string) ($props['citeUrl'] ?? ''), "{$path}.props.citeUrl", true);
        }
        if ($type === 'content.table') {
            $this->validateTable($props, $path);
        }
        if ($type === 'content.badge') {
            $this->requireText($props['text'] ?? '', "{$path}.props.text");
        }
        if ($type === 'content.card') {
            $this->requireText($props['title'] ?? '', "{$path}.props.title");
            $this->requireSafeUrl((string) ($props['linkUrl'] ?? ''), "{$path}.props.linkUrl", true);
            $this->validateOptionalLinkLabel($props, $path);
        }
        if (in_array($type, ['media.image', 'media.image-text'], true)) {
            $this->validateAlternativeText($props, "{$path}.props");
        }
        if ($type === 'media.image-text') {
            $this->requireText($props['heading'] ?? '', "{$path}.props.heading");
            $this->requireSafeUrl((string) ($props['linkUrl'] ?? ''), "{$path}.props.linkUrl", true);
            $this->validateOptionalLinkLabel($props, $path);
        }
        if ($type === 'media.gallery') {
            $this->requireText($props['label'] ?? '', "{$path}.props.label", 'accessible_label_required');
            foreach ((array) ($props['items'] ?? []) as $index => $item) {
                if (is_array($item)) {
                    $this->validateAlternativeText($item, "{$path}.props.items.{$index}");
                }
            }
        }
        if ($type === 'media.video-embed') {
            $this->requireText($props['title'] ?? '', "{$path}.props.title", 'accessible_label_required');
        }
        if ($type === 'media.logo-cloud') {
            $this->requireText($props['label'] ?? '', "{$path}.props.label", 'accessible_label_required');
            foreach ((array) ($props['items'] ?? []) as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }
                $this->requireText($item['alt'] ?? '', "{$path}.props.items.{$index}.alt", 'alt_required');
                $this->requireSafeUrl((string) ($item['linkUrl'] ?? ''), "{$path}.props.items.{$index}.linkUrl", true);
            }
        }
        if ($type === 'content.faq') {
            foreach ((array) ($props['items'] ?? []) as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }
                $this->requireText($item['question'] ?? '', "{$path}.props.items.{$index}.question");
                $answer = $this->richText->sanitize((string) ($item['answer'] ?? ''));
                $this->requireText(strip_tags($answer), "{$path}.props.items.{$index}.answer");
                $props['items'][$index]['answer'] = $answer;
            }
        }

        $block['props'] = $props;

        return $block;
    }

    /** @param array<string, mixed> $props */
    private function validateTable(array $props, string $path): void
    {
        $this->requireText($props['caption'] ?? '', "{$path}.props.caption", 'accessible_label_required');
        $headers = (array) ($props['headers'] ?? []);
        foreach ($headers as $index => $header) {
            $this->requireText($header, "{$path}.props.headers.{$index}");
        }
        foreach ((array) ($props['rows'] ?? []) as $index => $row) {
            if (! is_array($row) || count($row) !== count($headers)) {
                $this->fail("{$path}.props.rows.{$index}", 'table_columns');
            }
        }
    }

    /** @param array<string, mixed> $props */
    private function validateAlternativeText(array $props, string $path): void
    {
        if (($props['decorative'] ?? false) !== true) {
            $this->requireText($props['alt'] ?? '', "{$path}.alt", 'alt_required');
        } else {
            $props['alt'] = '';
        }
    }

    /** @param array<string, mixed> $props */
    private function validateOptionalLinkLabel(array $props, string $path): void
    {
        $url = (string) ($props['linkUrl'] ?? '');
        $label = (string) ($props['linkLabel'] ?? '');
        if (($url === '') !== ($label === '')) {
            $this->fail("{$path}.props.linkLabel", 'link_pair');
        }
    }

    private function requireSafeUrl(string $url, string $path, bool $allowEmpty): void
    {
        if ($allowEmpty && $url === '') {
            return;
        }
        if ($url === '#' || str_starts_with($url, '#') || (str_starts_with($url, '/') && ! str_starts_with($url, '//'))) {
            return;
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) !== false) {
            return;
        }
        if ($scheme === 'mailto' && filter_var(substr($url, 7), FILTER_VALIDATE_EMAIL) !== false) {
            return;
        }
        if ($scheme === 'tel' && preg_match('/^tel:\+?[0-9(). -]{3,32}$/', $url) === 1) {
            return;
        }

        $this->fail($path, 'unsafe_url');
    }

    private function requireText(mixed $value, string $path, string $message = 'content_required'): void
    {
        if (! is_string($value) || trim(strip_tags($value)) === '') {
            $this->fail($path, $message);
        }
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function trimStrings(array $values): array
    {
        foreach ($values as $key => $value) {
            $values[$key] = is_array($value) ? $this->trimStrings($value) : (is_string($value) ? trim(str_replace("\0", '', $value)) : $value);
        }

        return $values;
    }

    private function fail(string $path, string $key): never
    {
        $message = __("page_builder.validation.{$key}");

        throw ValidationException::withMessages([$path => [is_string($message) ? $message : $key]]);
    }
}
