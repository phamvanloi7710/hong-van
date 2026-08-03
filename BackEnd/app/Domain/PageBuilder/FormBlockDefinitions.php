<?php

namespace App\Domain\PageBuilder;

use App\Domain\PageBuilder\Rendering\FormBlockRenderer;
use App\Domain\PageBuilder\Sanitization\FormBlockSanitizer;
use Illuminate\Support\Facades\Lang;

final class FormBlockDefinitions
{
    public const TYPES = ['form.contact', 'form.product-quote', 'form.transport-request', 'form.warehouse-request'];

    /** @return list<BlockDefinition> */
    public static function definitions(): array
    {
        return [
            self::definition('form.contact', 'contact', 'fa-regular fa-envelope'),
            self::definition('form.product-quote', 'product_quote', 'fa-solid fa-file-signature'),
            self::definition('form.transport-request', 'transport', 'fa-solid fa-truck-fast'),
            self::definition('form.warehouse-request', 'warehouse', 'fa-solid fa-warehouse'),
        ];
    }

    private static function definition(string $type, string $formType, string $icon): BlockDefinition
    {
        $props = [
            'title' => '',
            'description' => '',
            'submitLabel' => '',
            'successMessage' => '',
        ];
        $point = self::object([
            'spacing' => ['type' => 'string', 'enum' => ['none', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl']],
        ], ['spacing']);
        $defaults = [
            'props' => $props,
            'style' => ['desktop' => ['spacing' => 'none'], 'tablet' => ['spacing' => 'none'], 'mobile' => ['spacing' => 'none']],
            'visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
            'bindings' => [],
            'children' => [],
        ];

        return new BlockDefinition(
            type: $type, version: 1,
            labels: ['vi' => self::text($formType, 'label', 'vi'), 'en' => self::text($formType, 'label', 'en'), 'zh' => self::text($formType, 'label', 'zh')],
            category: 'form', icon: $icon, thumbnail: null,
            propsSchema: self::object([
                'title' => ['type' => 'string', 'maxLength' => 200],
                'description' => ['type' => 'string', 'maxLength' => 1000],
                'submitLabel' => ['type' => 'string', 'maxLength' => 120],
                'successMessage' => ['type' => 'string', 'maxLength' => 300],
            ], ['title', 'description', 'submitLabel', 'successMessage']),
            styleSchema: self::object(['desktop' => $point, 'tablet' => $point, 'mobile' => $point], ['desktop', 'tablet', 'mobile']),
            visibilitySchema: self::object(['desktop' => ['type' => 'boolean'], 'tablet' => ['type' => 'boolean'], 'mobile' => ['type' => 'boolean']], ['desktop', 'tablet', 'mobile']),
            bindingsSchema: self::object([], []), defaults: $defaults,
            allowRoot: false, allowedParents: ['layout.section', 'layout.container', 'layout.stack', 'layout.grid', 'layout.columns'], allowedChildren: [], maxDepth: 8, minChildren: 0, maxChildren: 0,
            dataDependencies: $type === 'form.product-quote' ? ['product-context'] : [], permissions: [], cacheTags: ['page-builder', 'forms'],
            renderer: FormBlockRenderer::class, sanitizer: FormBlockSanitizer::class, migrations: [],
            testFixture: ['id' => str_replace('.', '-', $type).'-fixture', 'type' => $type, 'version' => 1, ...$defaults],
        );
    }

    private static function text(string $formType, string $part, string $locale = 'vi'): string
    {
        return Lang::get("page_builder_forms.forms.{$formType}_{$part}", [], $locale);
    }

    /**
     * @param  array<string, mixed>  $properties
     * @param  list<string>  $required
     * @return array<string, mixed>
     */
    private static function object(array $properties, array $required): array
    {
        return ['type' => 'object', 'properties' => $properties, 'required' => $required, 'additionalProperties' => false];
    }
}
