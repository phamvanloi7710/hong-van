<?php

namespace App\Domain\PageBuilder;

use App\Domain\PageBuilder\Contracts\BlockRenderer;
use Illuminate\Contracts\Container\Container;

final readonly class PageDocumentRenderer
{
    public function __construct(
        private BlockRegistry $registry,
        private PageDocumentValidator $validator,
        private PageBuilderMediaResolver $mediaResolver,
        private Container $container,
    ) {}

    /** @param array<string, mixed> $document */
    public function render(array $document): string
    {
        $validated = $this->validator->validate($document);
        $media = $this->mediaResolver->resolve($validated);
        $blocks = is_array($validated['blocks'] ?? null) ? $validated['blocks'] : [];

        return implode('', array_map(fn (mixed $block): string => $this->renderBlock($block, $media), $blocks));
    }

    /** @param array<string, array<string, mixed>> $media */
    private function renderBlock(mixed $value, array $media): string
    {
        if (! is_array($value) || ! is_string($value['type'] ?? null)) {
            return '';
        }
        $definition = $this->registry->get($value['type']);
        $renderer = $this->container->make($definition->renderer);
        if (! $renderer instanceof BlockRenderer) {
            throw new \LogicException("Invalid renderer for Page Builder block [{$definition->type}].");
        }
        $children = is_array($value['children'] ?? null) ? $value['children'] : [];
        $childrenHtml = implode('', array_map(fn (mixed $child): string => $this->renderBlock($child, $media), $children));
        $value['_mediaMap'] = $media;

        return $renderer->render($value, $childrenHtml);
    }
}
