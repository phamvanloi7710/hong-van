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
        private DynamicBlockDataLoader $dynamicData,
        private Container $container,
    ) {}

    /** @param array<string, mixed> $document */
    public function render(array $document, ?PageRenderOptions $options = null): string
    {
        $options ??= PageRenderOptions::published();
        $validated = $this->validator->validate($document);
        $media = $this->mediaResolver->resolve($validated);
        $dynamicData = $this->dynamicData->load($validated, $options);
        $renderContext = ['locale' => $options->locale, 'preview' => $options->preview, 'type' => $options->contextType, 'publicId' => $options->contextPublicId];
        $blocks = is_array($validated['blocks'] ?? null) ? $validated['blocks'] : [];

        return implode('', array_map(fn (mixed $block): string => $this->renderBlock($block, $media, $dynamicData, $renderContext), $blocks));
    }

    /**
     * @param  array<string, array<string, mixed>>  $media
     * @param  array<string, array<string, mixed>>  $dynamicData
     * @param  array<string, mixed>  $renderContext
     */
    private function renderBlock(mixed $value, array $media, array $dynamicData, array $renderContext): string
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
        $childrenHtml = implode('', array_map(fn (mixed $child): string => $this->renderBlock($child, $media, $dynamicData, $renderContext), $children));
        $value['_mediaMap'] = $media;
        $value['_dynamicData'] = is_string($value['id'] ?? null) ? ($dynamicData[$value['id']] ?? null) : null;
        $value['_renderContext'] = $renderContext;

        return $renderer->render($value, $childrenHtml);
    }
}
